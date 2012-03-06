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
	
	header ("Location: carreras.php");
	
	if (!isset ($_GET['confirmado_js']) || $_GET['confirmado_js'] != 1) {
		agrega_mensaje (1, "Su solicitud no puede ser procesada. Por favor intente de nuevo");
		exit;
	}
	
	if (!isset ($_GET['clave']) || !preg_match ("/^([A-Za-z]){3}$/", $_GET['clave'])) {
		agrega_mensaje (3, "Clave incorrecta");
		exit;
	}
	
	$clave_carrera = $_GET['clave'];
	
	database_connect ();
	
	/* Impedir que eliminen la materia si tiene alumnos de esa carrera */
	/* SELECT * FROM Alumnos WHERE Carrera=COM limit 1 */
	$query = sprintf ("SELECT * FROM Alumnos WHERE Carrera='%s' LIMIT 1", $clave_carrera);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) > 0) {
		mysql_free_result ($result);
		agrega_mensaje (1, "La carrera no puede ser borrada porque tiene alumnos matriculados");
		exit;
	}
	
	mysql_free_result ($result);
	
	/* Si no tiene alguna dependencia, entonces eliminar la carrera */
	$query = sprintf ("DELETE FROM Carreras WHERE Clave='%s'", $clave_carrera);
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		agrega_mensaje (3, "Error desconocido");
	} else {
		agrega_mensaje (0, "La carrera fué eliminada");
	}
?>
