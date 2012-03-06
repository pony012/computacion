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
	
	/* Verificar que haya datos POST */
	if (!isset ($_POST['id']) || $_POST['id'] < 0 || !isset ($_POST['materia']) || $_POST['materia'] === "NULL") exit;
	
	$id_academia = strval (intval ($_POST['id']));
	
	if (!preg_match ("/^([A-Za-z])([A-Za-z0-9]){2}([0-9]){2}$/", $_POST['materia'])) {
		agrega_mensaje (3, "Materia incorrecta");
		exit;
	}
	$clave_materia = $_POST['materia'];
	
	database_connect ();
	
	/* Verificar que la academia exista */
	$query = sprintf ("SELECT * FROM Academias WHERE Id = '%s'", $id_academia);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		agrega_mensaje (3, "Academia desconocida");
		exit;
	}
	
	mysql_free_result ($result);
	header ("Location: ver_academia.php?id=" . $id_academia);
	
	/* Verificar que la materia exista */
	$query = sprintf ("SELECT Academia FROM Materias WHERE Clave = '%s'", $clave_materia);
	
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
	$query = sprintf ("UPDATE Materias SET Academia = '%s' WHERE Clave = '%s'", $id_academia, $clave_materia);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		agrega_mensaje (3, "Error desconocido");
	} else {
		agrega_mensaje (0, "Materia agregada");
	}
?>
