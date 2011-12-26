<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['admin_evaluaciones']) || $_SESSION['permisos']['admin_evaluaciones'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	
	if (!isset ($_POST['modo']) || ($_POST['modo'] != 'nuevo' && $_POST['modo'] != 'editar')) {
		header ("Location: evaluaciones.php");
		exit;
	}
	
	require_once "../mysql-con.php";
	
	if ($_POST['modo'] == 'nuevo') {
		$query = sprintf ("INSERT INTO Evaluaciones (Descripcion) VALUES ('%s');", mysql_real_escape_string ($_POST['descripcion']));
		
		$result = mysql_query ($query);
		
		mysql_close ($mysql_con);
		if (!$result) {
			header ("Location: evaluaciones.php?e=unknown");
			exit;
		} else {
			header ("Location: evaluaciones.php?n=ok");
			exit;
		}
	} else if ($_POST['modo'] == 'editar') {
		if (!isset ($_POST['id']) || $_POST['id'] < 1) {
			header ("Location: evaluaciones.php?e=err");
			exit;
		}
		
		$query = sprintf ("UPDATE Evaluaciones SET Descripcion = '%s' WHERE Id = '%s'", $_POST['descripcion'], $_POST['id']);
		
		$result = mysql_query ($query);
		
		mysql_close ($mysql_con);
		if (!$result) {
			header ("Location: evaluaciones.php?e=unknown");
			exit;
		} else {
			header ("Location: evaluaciones.php?a=ok");
			exit;
		}
	}
?>
