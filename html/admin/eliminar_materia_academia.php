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
	if (!isset ($_GET['id']) || !isset ($_GET['materia']) || $_GET['materia'] === "NULL") exit;
	
	$id_academia = strval (intval ($_GET['id']));
	
	/* Sanear la entrada "materia" */
	if (!preg_match ("/^([A-Za-z])([A-Za-z0-9]){2}([0-9]){2}$/", $_GET['materia'])) {
		agrega_mensaje (3, "Materia incorrecta");
		exit;
	}
	
	$clave_materia = strtoupper ($_GET['materia']);
	
	database_connect ();
	
	header ("Location: ver_academia.php?id=" . $id_academia);
	
	/* Verificar que la materia existe, y que pertenece a la academia especificada */
	$query = sprintf ("SELECT Academia FROM Materias WHERE Clave = '%s'", $clave_materia);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		agrega_mensaje (3, "Materia desconocida");
		exit;
	}
	
	$m = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	if (is_null ($m->Academia) || $m->Academia != $id_academia) {
		/* Esta materia no pertenece a ninguna academia o
		 * no pertence a esa academia */
		agrega_mensaje (1, "La materia no pertenece a la academia especificada");
		exit;
	}
	
	/* Ahora sí, sacar esta materia de la academia especificada */
	$query = sprintf ("UPDATE Materias SET Academia = NULL WHERE Clave = '%s'", $clave_materia);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	agrega_mensaje (0, sprintf ("La materia %s fué eliminada de la academia", $_GET['materia']));
?>
