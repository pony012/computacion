<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	header ("Location: academias.php");
	
	if (!isset ($_SESSION['permisos']['admin_academias']) || $_SESSION['permisos']['admin_academias'] != 1) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		exit;
	}
	
	if (!isset ($_GET['confirmado_js']) || $_GET['confirmado_js'] != 1) {
		agrega_mensaje (1, "Su solicitud no puede ser procesada. Por favor intente de nuevo");
		exit;
	}
	
	/* Verificar que haya datos POST */
	if (!isset ($_GET['id']) || $_GET['id'] < 0 || !isset ($_GET['materia']) || $_GET['materia'] === "NULL") exit;
	
	settype ($_GET['id'], 'integer');
	
	/* Sanear la entrada "materia" */
	if (!preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_GET['materia'])) {
		agrega_mensaje (3, "Materia incorrecta");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	header ("Location: ver_academia.php?id=" . $_GET['id']);
	
	/* Verificar que la materia existe, y que pertenece a la academia especificada */
	$query = sprintf ("SELECT Academia FROM Materias WHERE Clave = '%s'", $_GET['materia']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		agrega_mensaje (3, "Materia desconocida");
		exit;
	}
	
	$m = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	if (is_null ($m->Academia) || $m->Academia != $_GET['id']) {
		/* Esta materia no pertenece a ninguna academia o
		 * no pertence a esa academia */
		agrega_mensaje (1, "La materia no pertenece a la academia especificada");
		exit;
	}
	
	/* Ahora sí, sacar esta materia de la academia especificada */
	$query = sprintf ("UPDATE Materias SET Academia = NULL WHERE Clave = '%s'", $_GET['materia']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	agrega_mensaje (0, sprintf ("La materia %s fué eliminada de la academia", $_GET['materia']));
?>
