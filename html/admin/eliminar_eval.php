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
	
	if (!isset ($_GET['id'])) {
		header ("Location: evaluaciones.php");
		exit;
	}
	
	settype ($_GET['id'], 'integer');
	
	if ($_GET['id'] < 1) {
		header ("Location: evaluaciones.php?r=extra");
		exit;
	}
	
	if (!isset ($_GET['confirmado_js']) || $_GET['confirmado_js'] != 1) {
		header ("Location: evaluaciones.php?r=err");
		exit;
	}
	
	require_once "../mysql-con.php";
	
	$query = sprintf ("SELECT Tipo FROM Porcentajes WHERE Tipo = '%s' LIMIT 1", $_GET['id']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) > 0) {
		mysql_free_result ($result);
		header ("Location: evaluaciones.php?r=uso");
		exit;
	}
	
	mysql_free_result ($result);
	
	$query = sprintf ("DELETE FROM Evaluaciones WHERE Id = '%s'", $_GET['id']);
	$result = mysql_query ($query);
	
	if (!$result) {
		header ("Location: evaluaciones.php?r=no");
		exit;
	}
	
	header ("Location: evaluaciones.php?r=ok");
	mysql_close ($mysql_con);
?>
