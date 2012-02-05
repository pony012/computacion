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
	
	/* Verificar que haya datos POST */
	if (!isset ($_POST['id']) || $_POST['id'] < 0 || !isset ($_POST['materia']) || $_POST['materia'] === "NULL") {
		exit;
	}
	
	settype ($_POST['id'], 'integer');
	
	if (!preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_POST['materia'])) {
		agrega_mensaje (3, "Materia incorrecta");
		exit;
	}
		
	require_once '../mysql-con.php';
	
	/* Verificar que la academia exista */
	$query = sprintf ("SELECT * FROM Academias WHERE Id = '%s'", $_POST['id']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		agrega_mensaje (3, "Academia desconocida");
		exit;
	}
	
	mysql_free_result ($result);
	header ("Location: ver_academia.php?id=" . $_POST['id']);
	
	/* Verificar que la materia exista */
	$query = sprintf ("SELECT Academia FROM Materias WHERE Clave = '%s'", $_POST['materia']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		agrega_mensaje (3, "Materia desconocida");
		exit;
	}
	
	$m = mysql_fetch_object ($result);
	
	if (!is_null ($m->Academia)) {
		agrega_mensaje (1, "La materia ya pertenece a una academia");
		exit;
	}
	
	mysql_free_result ($result);
	
	/* Ahora sí, agregar la materia a esta academia */
	$query = sprintf ("UPDATE Materias SET Academia = '%s' WHERE Clave = '%s'", $_POST['id'], $_POST['materia']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		agrega_mensaje (3, "Error desconocido");
	} else {
		agrega_mensaje (0, "Materia agregada");
	}
	
	mysql_close ($mysql_con);
?>