<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	if (!isset ($_SESSION['permisos']['asignar_aplicadores']) || $_SESSION['permisos']['asignar_aplicadores'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		agrega_mensaje (3, "Privilegios insuficientes");
		exit;
	}
	
	header ("Location: aplicadores_general.php");
	
	if (!preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_POST['materia'])) {
		agrega_mensaje (3, "Clave de materia incorrecta");
		exit;
	}
	
	if (!isset ($_POST['select_order'])) {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	settype ($_POST['fecha'], 'integer');
	$tiempo_fecha = $_POST['fecha'] - ($_POST['fecha'] % 900);
	settype ($_POST['evaluacion'], 'integer');
	settype ($_POST['no_alumnos'], 'integer');
	
	if (($_POST['select_order'] == 'order' || $_POST['select_order'] == 'random') && $_POST['no_alumnos'] < 10) {
		agrega_mensaje (3, "El mínimo de alumnos por salón aplicador es de 10");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	/* Validar que exista la forma de evaluacion seleccionada con la materia seleccionada */
	/* SELECT * FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id INNER JOIN Materias AS M ON P.Clave = M.Clave */
	$query = sprintf ("SELECT P.Tipo FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Tipo='%s' AND P.Clave='%s' AND E.Exclusiva = 0", $_POST['evaluacion'], $_POST['materia']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	mysql_free_result ($result);
	
	/* Verificar los maestros */
	foreach  ($_POST['maestro'] as $key => $m) {
		if (!preg_match ("/^([0-9]){1,7}$/", $m)) {
			unset ($_POST['maestro'][$key]);
			continue;
		}
		
		$query = sprintf ("SELECT Codigo FROM Maestros WHERE Codigo = '%s'", $m);
		$result = mysql_query ($query, $mysql_con);
		
		if (mysql_num_rows ($result) == 0) {
			unset ($_POST['maestro'][$key]);
		}
		mysql_free_result ($result);
	}
	
	/* Re-indexar el arreglo */
	$_POST['maestro'] = array_values ($_POST['maestro']);
	
	/* Borrar todos los alumnos de esta materia y evaluación */
	/* DELETE FROM AA USING Alumnos_Aplicadores AS AA INNER JOIN Salones_Aplicadores AS SA ON AA.Id = SA.Id WHERE SA.Materia = 'CC100' AND SA.Tipo = 1 */
	$query = sprintf ("DELETE FROM AA USING Alumnos_Aplicadores AS AA INNER JOIN Salones_Aplicadores AS SA ON AA.Id = SA.Id WHERE SA.Materia = '%s' AND SA.Tipo = '%s'", $_POST['materia'], $_POST['evaluacion']);
	
	mysql_query ($query);
	
	/* DELETE FROM Salones_Aplicadores WHERE Materia = 'CC100' AND Tipo =1 */
	
	$query = sprintf ("DELETE FROM Salones_Aplicadores WHERE Materia = '%s' AND Tipo = '%s'", $_POST['materia'], $_POST['evaluacion']);
	
	mysql_query ($query);
	
	if ($_POST['select_order'] == 'order' || $_POST['select_order'] == 'random') {
		/* SELECT G.Alumno FROM Grupos AS G INNER JOIN Secciones AS S ON G.Nrc = S.Nrc INNER JOIN Alumnos AS A ON G.Alumno = A.Codigo WHERE S.Materia = 'CC100' ORDER BY A.Apellido, A.Nombre */
		$query = sprintf ("SELECT G.Alumno FROM Grupos AS G INNER JOIN Secciones AS S ON G.Nrc = S.Nrc INNER JOIN Alumnos AS A ON G.Alumno = A.Codigo WHERE S.Materia = '%s'", $_POST['materia']);
		
		if ($_POST['select_order'] == 'order') {
			$query = $query . " ORDER BY A.Apellido, A.Nombre";
		} else {
			$query = $query . " ORDER BY RAND()";
		}
		
		$result = mysql_query ($query, $mysql_con);
		
		$g = 0;
		$salon = 1;
		$id_salon = -1;
		$query_aplicadores = "INSERT INTO Alumnos_Aplicadores (Id, Alumno) VALUES";
		
		while (($object = mysql_fetch_object ($result))) {
			$g++;
			
			if ($id_salon == -1) {
				/* Tomar un maestro de la lista */
				if (isset ($_POST['maestro'][$salon - 1])) {
					$un_maestro = $_POST['maestro'][$salon - 1];
				} else {
					$un_maestro = "NULL";
				}
				
				$query = sprintf ("INSERT INTO Salones_Aplicadores (Materia, Tipo, Salon, FechaHora, Maestro) VALUES ('%s', '%s', 'Salon %s', FROM_UNIXTIME ('%s'), %s);", $_POST['materia'], $_POST['evaluacion'], $salon, $tiempo_fecha, $un_maestro);
				
				mysql_query ($query, $mysql_con);
				
				$id_salon = mysql_insert_id ($mysql_con);
			}
			
			$query_aplicadores = $query_aplicadores . sprintf (" ('%s', '%s'),", $id_salon, $object->Alumno);
			
			if ($g == $_POST['no_alumnos']) {
				/* Ejecutar la query, resetar la consulta y aumentar el salón */
				$query_aplicadores = substr_replace ($query_aplicadores, ";", -1);
				mysql_query ($query_aplicadores, $mysql_con);
				$query_aplicadores = "INSERT INTO Alumnos_Aplicadores (Id, Alumno) VALUES";
				$salon++;
				$id_salon = -1;
				$g = 0;
			}
		}
		
		mysql_free_result ($result);
		
		if ($g != 0) {
			$query_aplicadores = substr_replace ($query_aplicadores, ";", -1);
			mysql_query ($query_aplicadores, $mysql_con);
		}
		
		agrega_mensaje (0, sprintf ("Se han generado %s salones", $salon - 1));
		exit;
	} else if ($_POST['select_order'] == 'grupos') {
		$query = sprintf ("SELECT Nrc, Maestro FROM Secciones WHERE Materia='%s'", $_POST['materia']);
		
		$result = mysql_query ($query, $mysql_con);
		
		$salon = 1;
		while (($nrc = mysql_fetch_object ($result))) {
			/* Por cada Nrc, generar un query extendido */
			$query = sprintf ("INSERT INTO Salones_Aplicadores (Materia, Tipo, Salon, FechaHora, Maestro) VALUES ('%s', '%s', 'Salon %s', FROM_UNIXTIME ('%s'), '%s');", $_POST['materia'], $_POST['evaluacion'], $salon, $tiempo_fecha, $nrc->Maestro);
			
			mysql_query ($query, $mysql_con);
			$id_salon = mysql_insert_id ($mysql_con);
			
			$query_aplicadores = "INSERT INTO Alumnos_Aplicadores (Id, Alumno) VALUES";
			$query = sprintf ("SELECT Alumno FROM Grupos WHERE Nrc = '%s'", $nrc->Nrc);
			
			$res_al = mysql_query ($query);
			
			while (($object = mysql_fetch_object ($res_al))) {
				$query_aplicadores = $query_aplicadores . sprintf (" ('%s', '%s'),", $id_salon, $object->Alumno);
			}
			
			mysql_free_result ($res_al);
			
			$query_aplicadores = substr_replace ($query_aplicadores, ";", -1);
			mysql_query ($query_aplicadores, $mysql_con);
			$salon++;
		}
		
		mysql_free_result ($result);
		
		agrega_mensaje (0, "Se ha convertido cada salón de clases en un salon aplicador");
		exit;
	}
?>
