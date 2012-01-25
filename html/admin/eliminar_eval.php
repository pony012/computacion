<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	if (!isset ($_SESSION['permisos']['admin_evaluaciones']) || $_SESSION['permisos']['admin_evaluaciones'] != 1) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
	
	header ("Location: evaluaciones.php");
	
	if (!isset ($_GET['id'])) exit;
	
	settype ($_GET['id'], 'integer');
	
	if ($_GET['id'] < 1) {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	if (!isset ($_GET['confirmado_js']) || $_GET['confirmado_js'] != 1) {
		agrega_mensaje (1, "Su solicitud no puede ser procesada, por favor intente otra vez");
		exit;
	}
	
	require_once "../mysql-con.php";
	
	$query = sprintf ("SELECT Tipo FROM Porcentajes WHERE Tipo = '%s' LIMIT 1", $_GET['id']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) > 0) {
		mysql_free_result ($result);
		agrega_mensaje (1, "La forma de evaluación está actualmente en uso");
		exit;
	}
	
	mysql_free_result ($result);
	
	$query = sprintf ("DELETE FROM Evaluaciones WHERE Id = '%s'", $_GET['id']);
	$result = mysql_query ($query);
	
	if (!$result) {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	agrega_mensaje (0, "La forma de evaluación fué eliminada");
	mysql_close ($mysql_con);
?>
