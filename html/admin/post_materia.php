<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	if (!isset ($_SESSION['permisos']['crear_materias']) || $_SESSION['permisos']['crear_materias'] != 1) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
	
	header ("Location: materias.php");
	
	if (!isset ($_POST['modo']) || ($_POST['modo'] != 'nuevo' && $_POST['modo'] != 'editar')) {
		agrega_mensaje (3, "Ha ocurrido un error desconocido");
		exit;
	}
	
	if (count ($_POST['evals']) != count ($_POST['porcentajes']) || count ($_POST['evals']) <= 0) {
		agrega_mensaje (3, "Ha ocurrido un error procesando los datos");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	/* Recuperar las formas de evaluación */
	$result = mysql_query ("SELECT Id FROM Evaluaciones", $mysql_con);
	while (($row = mysql_fetch_row ($result)) != FALSE) $todas[$row[0]] = 1;
	mysql_free_result ($result);
	
	$nuevas = array ();
	for ($g = 0; $g < count ($_POST['evals']); $g++) {
		$nuevas [((int) $_POST['evals'][$g])] = (int) $_POST['porcentajes'][$g];
	}
	
	$suma = 0;
	foreach ($nuevas as $key => $porcentaje) {
		if ($porcentaje <= 0 || !isset ($todas [$key])) {
			agrega_mensaje (3, "Ha ocurrido un error procesando los datos");
			exit;
		}
		
		$suma += $porcentaje;
	}
	
	if ($suma != 100) {
		/* Suma incorrecta */
		agrega_mensaje (3, "Ha ocurrido un error procesando los datos");
		exit;
	}
	
	if (isset ($_POST['tiene_extra']) && $_POST['tiene_extra'] == 1) $nuevas[0] = 100;
	
	/* Validar la clave la materia */
	if (!preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_POST['clave'])) {
		agrega_mensaje (3, "Materia incorrecta");
		exit;
	}
	
	$_POST['clave'] = strtoupper ($_POST['clave']);
	
	if ($_POST['modo'] == 'nuevo') {
		/* INSERT INTO `computacion`.`Materias` (`Clave`, `Descripcion`) VALUES ('as123', 'sadfgh'); */
		$query = sprintf ("INSERT INTO Materias (Clave, Descripcion) VALUES ('%s', '%s');", $_POST['clave'], mysql_real_escape_string ($_POST['descripcion']));
		
		$result = mysql_query ($query, $mysql_con);
	
		if (!$result) {
			agrega_mensaje (3, "Error desconocido");
			exit;
		}
		
		/* Insertar las formas de evaluación */
		$query = "INSERT INTO Porcentajes (Clave, Tipo, Ponderacion) VALUES ";
		
		foreach ($nuevas as $key => $porcentaje) {
			$query = $query . sprintf ("('%s', '%s', '%s'),", $_POST['clave'], $key, $porcentaje);
		}
		
		$query = substr_replace ($query, ";", -1);
		mysql_query ($query, $mysql_con);
		
		agrega_mensaje (0, sprintf ("La materia %s fué creada", $_POST['clave']));
	} else if ($_POST['modo'] == 'editar') {
		$query = sprintf ("UPDATE Materias SET Descripcion='%s' WHERE Clave='%s'", mysql_real_escape_string ($_POST['descripcion']), $_POST['clave']);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (!$result) {
			agrega_mensaje (3, "Error desconocido");
			exit;
		}
		
		/* Limpiar los porcentajes anteriores */
		$query = sprintf ("DELETE FROM Porcentajes WHERE Clave='%s'", $_POST['clave']);
		mysql_query ($query, $mysql_con);
		
		/* Insertar las nuevas formas de evaluación */
		$query = "INSERT INTO Porcentajes (Clave, Tipo, Ponderacion) VALUES ";
		
		foreach ($nuevas as $key => $porcentaje) {
			$query = $query . sprintf ("('%s', '%s', '%s'),", $_POST['clave'], $key, $porcentaje);
		}
		
		$query = substr_replace ($query, ";", -1);
		mysql_query ($query, $mysql_con);
		
		/* Borrar todas las calificaciones de esa materia */
		$query = sprintf ("DELETE FROM C USING Calificaciones AS C INNER JOIN Secciones AS S ON C.Nrc = S.Nrc WHERE S.Materia='%s'", $_POST['clave']);
		
		mysql_query ($query);
		
		/* Re-ingresar las calificaciones */
		$query = sprintf ("SELECT Alumno, Nrc FROM Grupos AS G INNER JOIN Secciones AS S ON G.Nrc = S.Nrc WHERE S.Materia = '%s'", $_POST['clave']);
		
		$result = mysql_query ($query);
		
		while (($object = mysql_fetch_object ($result))) {
			$query_cal = "INSERT DELAYED INTO Calificaciones VALUES (Alumno, Nrc, Tipo, Valor)";
			
			foreach ($nuevas as $key => $porcentaje) {
				$query_cal = $query_cal . sprintf (" ('%s', '%s', '%s', NULL),", $object->Alumno, $object->Nrc, $key);
			}
			
			$query_cal = substr_replace ($query_cal, ";", -1);
			mysql_query ($query_cal, $mysql_con);
		}
		
		agrega_mensaje (0, sprintf ("La materia %s fué actualizada", $_POST['clave']));
	}
	
	mysql_close ($mysql_con);
	exit;
?>
