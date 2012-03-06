<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	if (!has_permiso ('crear_materias')) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
	header ("Location: materias.php");
	
	if (!isset ($_GET['clave']) || !preg_match ("/^([A-Za-z])([A-Za-z0-9]){2}([0-9]){2}$/", $_GET['clave'])) {
		agrega_mensaje (3, "Clave de materia incorrecta");
		exit;
	}
	
	$clave_materia = strtoupper ($_GET['clave']);
	
	if (!isset ($_GET['confirmado_js']) || $_GET['confirmado_js'] != 1) {
		agrega_mensaje (1, "Su solicitud no puede ser procesada. Por favor intente de nuevo");
		exit;
	}
	
	database_connect ();
	
	/* Impedir que eliminen la materia si tiene secciones que dependan de ella */
	$query = sprintf ("SELECT * FROM Secciones WHERE Materia='%s' LIMIT 1", $clave_materia);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) > 0) {
		agrega_mensaje (1, "La materia no puede ser eliminada porque tiene secciones abiertas");
		exit;
	}
	
	mysql_free_result ($result);
	
	/* Si no tiene alguna dependencia, entonces eliminarla */
	$query = sprintf ("DELETE FROM Materias WHERE Clave='%s'", $clave_materia);
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		agrega_mensaje (3, "Ha ocurrido un error desconocido");
		exit;
	}
	
	/* Limpiar los porcentajes asociados con esta materia */
	$query = sprintf ("DELETE FROM Porcentajes WHERE Clave='%s'", $clave_materia);
	mysql_query ($query, $mysql_con);
	
	agrega_mensaje (0, sprintf ("La materia %s ha sido eliminada", $clave_materia));
?>
