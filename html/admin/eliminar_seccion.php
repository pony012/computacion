<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	/* Luego verificar si tiene el permiso de crear grupos */
	if (!isset ($_SESSION['permisos']['crear_grupos']) || $_SESSION['permisos']['crear_grupos'] != 1) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
	
	header ("Location: secciones.php");
	
	/* Validar primero el NRC */
	if (!isset ($_GET['nrc']) || !preg_match ("/^([0-9]){1,5}$/", $_GET['nrc'])) {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	if (!isset ($_GET['confirmado_js']) || $_GET['confirmado_js'] != 1) {
		agrega_mensaje (1, "Su solicitud no puede ser procesada. Por favor intente de nuevo");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	/* Impedir que eliminen la sección si tiene alumnos */
	$query = sprintf ("SELECT * FROM Grupos WHERE Nrc='%s' LIMIT 1", $_GET['nrc']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows($result) > 0) {
		agrega_mensaje (1, "La seccion no puede ser eliminada porque tiene alumnos matriculados");
		exit;
	}
	
	mysql_free_result ($result);
	
	$query = sprintf ("DELETE FROM Secciones WHERE Nrc='%s'", $_GET['nrc']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		agrega_mensaje (3, "Error Desconocido");
	}
	
	agrega_mensaje (1, "La sección fué eliminada");
	
	mysql_close ($mysql_con);
?>
