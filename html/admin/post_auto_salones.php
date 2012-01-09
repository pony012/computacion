<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['asignar_aplicadores']) || $_SESSION['permisos']['asignar_aplicadores'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	
	if (!preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_POST['materia'])) {
		header ("Location: aplicadores.php?e=clave");
		exit;
	}
	
	if (!isset ($_POST['select_order'])) {
		header ("Location: aplicadores.php?e=unknown");
		exit;
	}
	
	settype ($_POST['fecha'], 'integer');
	settype ($_POST['evaluacion'], 'integer');
	
	require_once '../mysql-con.php';
	
	$tiempo_fecha = $_POST['fecha'] - ($_POST['fecha'] % 900);
	
	/* Validar que exista la forma de evaluacion seleccionada con la materia seleccionada */
	/* SELECT * FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id INNER JOIN Materias AS M ON P.Clave = M.Clave */
	$query = sprintf ("SELECT P.Tipo FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Tipo='%s' AND P.Clave='%s' AND E.Exclusiva = 0", $_POST['evaluacion'], $_POST['materia']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: aplicadores.php?e=noexiste");
		exit;
	}
	
	mysql_free_result ($result);
	
	if ($_POST['select_order'] == 'order' || $_POST['select_order'] == 'random') {
		settype ($_POST['no_alumnos'], 'integer');
		if ($_POST['no_alumnos'] < 10) {
			header ("Location: aplicadores.php?e=unknown");
			mysql_close ($mysql_con);
			exit;
		}
		/* SELECT G.Alumno FROM Grupos AS G INNER JOIN Secciones AS S ON G.Nrc = S.Nrc INNER JOIN Alumnos AS A ON G.Alumno = A.Codigo WHERE S.Materia = 'CC100' ORDER BY A.Apellido, A.Nombre */
		$query = sprintf ("SELECT G.Alumno FROM Grupos AS G INNER JOIN Secciones AS S ON G.Nrc = S.Nrc INNER JOIN Alumnos AS A ON G.Alumno = A.Codigo WHERE S.Materia = '%s' ", $_POST['materia']);
		
		if ($_POST['select_order'] == 'order') {
			$query = $query . "ORDER BY A.Apellido, A.Nombre";
		} else {
			$query = $query . "ORDER BY RAND()";
		}
		
		$result = mysql_query ($query, $mysql_con);
		
		$g = 0;
		$salon = 1;
		$query_aplicadores = "INSERT INTO Aplicadores (Alumno, Materia, Tipo, Salon, FechaHora, Maestro) VALUES ";
		
		while (($object = mysql_fetch_object ($result))) {
			$g++;
			$query_aplicadores = $query_aplicadores . sprintf ("('%s', '%s', '%s', 'Salón %s', FROM_UNIXTIME ('%s'), '%s'),", $object->Alumno, $_POST['materia'], $_POST['evaluacion'], $salon, $tiempo_fecha, $_SESSION['codigo']);
			
			if ($g == $_POST['no_alumnos']) {
				/* Ejecutar la query, resetar la consulta y aumentar el salón */
				$query_aplicadores = substr_replace ($query_aplicadores, ";", -1);
				mysql_query ($query_aplicadores, $mysql_con);
				$query_aplicadores = "INSERT INTO Aplicadores (Alumno, Materia, Tipo, Salon, FechaHora, Maestro) VALUES ";
				$salon++;
				$g = 0;
			}
		}
		
		if ($g != 0) {
			$query_aplicadores = substr_replace ($query_aplicadores, ";", -1);
			mysql_query ($query_aplicadores, $mysql_con);
		}
		
		header ("Location: aplicadores.php?auto=ok");
		exit;
	}
?>
