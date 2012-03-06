<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	if (!has_permiso ('admin_evaluaciones')) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
	
	header ("Location: evaluaciones.php");
	
	if (!isset ($_GET['id'])) exit;
	
	$id_eval = strval (intval ($_GET['id']));
	
	if (!isset ($_GET['confirmado_js']) || $_GET['confirmado_js'] != 1) {
		agrega_mensaje (1, "Su solicitud no puede ser procesada, por favor intente otra vez");
		exit;
	}
	
	database_connect ();
	
	$query = sprintf ("SELECT Tipo FROM Porcentajes WHERE Tipo = '%s' LIMIT 1", $id_eval);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) > 0) {
		mysql_free_result ($result);
		agrega_mensaje (1, "La forma de evaluación está siendo usada por alguna materia");
		exit;
	}
	
	mysql_free_result ($result);
	
	$query = sprintf ("DELETE FROM Evaluaciones WHERE Id = '%s'", $id_eval);
	$result = mysql_query ($query);
	
	if (!$result) {
		agrega_mensaje (3, "Error desconocido");
	} else {
		agrega_mensaje (0, "La forma de evaluación fué eliminada");
	}
?>
