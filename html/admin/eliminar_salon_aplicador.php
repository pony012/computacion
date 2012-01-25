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
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
	
	header ("Location: aplicadores_general.php");
	if (!isset ($_GET['confirmado_js']) || $_GET['confirmado_js'] != 1) {
		agrega_mensaje (1, "Su solicitud no puede ser procesada, por favor intente de nuevo");
		exit;
	}
	
	if (!isset ($_GET['id'])) {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	settype ($_GET['id'], 'integer');
	
	require_once '../mysql-con.php';
	
	/* Verificar que el id del salon exista */
	
	$query = sprintf ("SELECT Id FROM Salones_Aplicadores WHERE Id = '%s'", $_GET['id']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		/* TODO: argumento next para regresar a la página anterior */
		agrega_mensaje (3, "El salon especificado no existe");
		mysql_close ($mysql_con);
		exit;
	}
	
	mysql_free_result ($result);
	
	$query = sprintf ("DELETE FROM Alumnos_Aplicadores WHERE Id = '%s'", $_GET['id']);
	mysql_query ($query);
	
	$query = sprintf ("DELETE FROM Salones_Aplicadores WHERE Id = '%s'", $_GET['id']);
	mysql_query ($query);
	
	/* TODO: argumento next para regresar a la página anterior */
	agrega_mensaje (0, "El salón fué eliminado");
?>
