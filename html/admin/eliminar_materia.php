<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	if (!isset ($_SESSION['permisos']['crear_materias']) || $_SESSION['permisos']['crear_materias'] != 1) {
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
	
	if (!isset ($_GET['confirmado_js']) || $_GET['confirmado_js'] != 1) {
		agrega_mensaje (1, "Su solicitud no puede ser procesada. Por favor intente de nuevo");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	/* Impedir que eliminen la materia si tiene secciones
	 * que dependan de ella */
	$query = sprintf ("SELECT * FROM Secciones WHERE Materia='%s' LIMIT 1", mysql_real_escape_string ($_GET['clave']));
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) > 0) {
		agrega_mensaje (1, "La materia no puede ser eliminada porque tiene secciones abiertas");
		exit;
	}
	
	mysql_free_result ($result);
	
	/* Si no tiene alguna dependencia, entonces eliminarla */
	$query = sprintf ("DELETE FROM Materias WHERE Clave='%s'", mysql_real_escape_string ($_GET['clave']));
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		agrega_mensaje (3, "Ha ocurrido un error desconocido");
		exit;
	}
	
	/* Limpiar los porcentajes asociados con esta materia */
	$query = sprintf ("DELETE FROM Porcentajes WHERE Clave='%s'", $_GET['clave']);
	mysql_query ($query, $mysql_con);
	
	agrega_mensaje (0, sprintf ("La materia %s ha sido eliminada", $_GET['clave']));
	mysql_close ($mysql_con);
?>
