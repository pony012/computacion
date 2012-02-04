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
	if (!isset ($_POST['modo']) || ($_POST['modo'] != 'nuevo' && $_POST['modo'] != 'editar')) {
		exit;
	}
	
	/* Verificar un nombre válido */
	if (!isset ($_POST['nombre']) ||  $_POST['nombre'] == "") {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	/* Validar al maestro */
	if (!isset ($_POST['maestro']) || !preg_match ("/^([0-9]){1,7}$/", $_POST['maestro'])) {
		agrega_mensaje (3, "Error al procesar los datos");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	/* Validar el maestro contra mysql */
	
	$query = sprintf ("SELECT Codigo FROM Maestros WHERE Codigo = '%s'", $_POST['maestro']);
	
	$result = mysql_query ($query, $mysql_con);
	if (mysql_num_rows ($result) == 0) {
		agrega_mensaje (3, "Error al procesar los datos");
		exit;
	}
	
	if ($_POST['modo'] == 'editar') {
		/* Validar el ID */
		if (!isset ($_POST['id']) || $_POST['id'] < 0) exit;
		
		settype ($_POST['id'], 'integer');
		
		$query = sprintf ("SELECT Id FROM Academias WHERE Id = '%s'", $_POST['id']);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (mysql_num_rows ($result) == 0) {
			agrega_mensaje (3, "Error al procesar los datos");
			exit;
		}
		
		mysql_free_result ($result);
		
		$query = sprintf ("UPDATE Academias SET Nombre = '%s', Maestro = '%s' WHERE Id = '%s'", mysql_real_escape_string ($_POST['nombre']), $_POST['maestro'], $_POST['id']);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (!$result) {
			agrega_mensaje (3, "Error desconocido");
		} else {
			agrega_mensaje (0, "Academia actualizada");
		}
		
		header ("Location: ver_academia.php?id=" . $_POST['id']);
		exit;
	} else if ($_POST['modo'] == 'nuevo') {
		/* Insertar una nueva Academia */
		$query = sprintf ("INSERT INTO Academias (Nombre, Maestro) VALUES ('%s', '%s')", mysql_real_escape_string ($_POST['nombre']), $_POST['maestro']);
		
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
