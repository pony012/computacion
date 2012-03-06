<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	header ("Location: academias.php");
	
	if (!has_permiso ('admin_academias')) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		exit;
	}
	
	if (!isset ($_GET['confirmado_js']) || $_GET['confirmado_js'] != 1) {
		agrega_mensaje (1, "Su solicitud no puede ser procesada. Por favor intente de nuevo");
		exit;
	}
	
	/* Verificar que haya datos POST */
	if (!isset ($_GET['id'])) exit;
	$id_academia = strval (intval ($_GET['id']));
	
	database_connect ();
	
	$query = sprintf ("UPDATE Materias SET Academia = NULL WHERE Academia = '%s'", $id_academia);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	$query = sprintf ("DELETE FROM Academias WHERE Id = '%s'", $id_academia);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		agrega_mensaje (3, "Error desconocido");
	} else {
		agrega_mensaje (0, "Academia eliminada");
	}
?>
