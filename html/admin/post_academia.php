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
	if (!isset ($_POST['modo']) || ($_POST['modo'] != 'nuevo' && $_POST['modo'] != 'editar')) {
		exit;
	}
	
	/* Verificar un nombre válido */
	if (!isset ($_POST['nombre']) ||  $_POST['nombre'] == "") {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	/* Validar al maestro */
	if (!isset ($_POST['maestro']) || (!preg_match ("/^([0-9]){1,7}$/", $_POST['maestro']) && $_POST['maestro'] !== "NULL")) {
		agrega_mensaje (3, "Error al procesar los datos");
		exit;
	}
	
	database_connect ();
	
	$nombre_academia = mysql_real_escape_string ($_POST['nombre']);
	$presidente = $_POST['maestro'];
	
	/* Validar el maestro contra mysql */
	if ($presidente != "NULL") {
		$query = sprintf ("SELECT Codigo FROM Maestros WHERE Codigo = '%s'", $presidente);
	
		$result = mysql_query ($query, $mysql_con);
		if (mysql_num_rows ($result) == 0) {
			agrega_mensaje (3, "Error al procesar los datos");
			exit;
		}
		
		mysql_free_result ($result);
	}
	
	if ($_POST['modo'] == 'editar') {
		/* Validar el ID */
		if (!isset ($_POST['id']) || $_POST['id'] < 0) exit;
		
		$id_academia = strval (intval ($_POST['id']));
		
		$query = sprintf ("SELECT Id FROM Academias WHERE Id = '%s'", $id_academia);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (mysql_num_rows ($result) == 0) {
			agrega_mensaje (3, "Error al procesar los datos");
			exit;
		}
		
		mysql_free_result ($result);
		
		$query = sprintf ("UPDATE Academias SET Nombre = '%s', Maestro = %s WHERE Id = '%s'", $nombre_academia, $presidente, $id_academia);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (!$result) {
			agrega_mensaje (3, "Error desconocido");
		} else {
			agrega_mensaje (0, "Academia actualizada");
		}
		
		header ("Location: ver_academia.php?id=" . $id_academia);
		exit;
	} else if ($_POST['modo'] == 'nuevo') {
		/* Insertar una nueva Academia */
		$query = sprintf ("INSERT INTO Academias (Nombre, Maestro) VALUES ('%s', %s)", $nombre_academia, $presidente);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (!$result) {
			agrega_mensaje (3, "Error desconocido");
		} else {
			$id = mysql_insert_id ($mysql_con);
			
			header ("Location: ver_academia.php?id=" . $id);
			agrega_mensaje (0, "Academia creada");
		}
		exit;
	}
?>
