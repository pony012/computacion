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
	
	if (!isset ($_POST['id'])) {
		header ("Location: aplicadores_general.php?e=unknown");
		exit;
	}
	
	settype ($_POST['id'], 'integer');
	
	require_once '../mysql-con.php';
	
	/* SELECT * FROM Salones_Aplicadores AS SA INNER JOIN Evaluaciones as E ON SA.Tipo = E.Id INNER JOIN Materias AS M ON SA.Materia = M.Clave WHERE Id = 1 */
	$query = sprintf ("SELECT Id FROM Salones_Aplicadores WHERE Id = '%s'", $_POST['id']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: aplicadores_general.php?e=noexiste");
		exit;
	}
	
	mysql_free_result ($result);
	
	/* Empezar el query extendido */
	$query_aplicadores = "INSERT INTO Alumnos_Aplicadores (Id, Alumno) VALUES ";
	
	foreach ($_POST['alumno'] as $value) {
		$query_alumno = sprintf ("SELECT Codigo FROM Alumnos WHERE Codigo = '%s'", $value);
		$result = mysql_query ($query_alumno);
		if (mysql_num_rows ($result) == 0) {
			mysql_free_result ($result);
			continue;
		}
		
		mysql_free_result ($result);
		
		$query_aplicadores = $query_aplicadores . sprintf ("('%s', '%s'),", $_POST['id'], $value);
	}
	
	$query_aplicadores = substr_replace ($query_aplicadores, ";", -1);
	
	$result = mysql_query ($query_aplicadores, $mysql_con);
	
	if (!$result) {
		header ("Location: aplicadores_general.php?e=unknown");
	} else {
		header ("Location: aplicadores_general.php?a=ok");
	}
	mysql_close ($mysql_con);
	exit;
?>
