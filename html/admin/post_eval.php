<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	if (!isset ($_SESSION['permisos']['admin_evaluaciones']) || $_SESSION['permisos']['admin_evaluaciones'] != 1) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
	
	header ("Location: evaluaciones.php");
	
	if (!isset ($_POST['modo']) || ($_POST['modo'] != 'nuevo' && $_POST['modo'] != 'editar')) exit;
	
	/* Validaciones varias sobre los campos */
	if (!isset ($_POST['descripcion']) || $_POST['descripcion'] == "") {
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	
	settype ($_POST['inicio'], 'integer');
	settype ($_POST['fin'], 'integer');
	
	if ($_POST['inicio'] >= $_POST['fin'] || $_POST['inicio'] < 0 || $_POST['fin'] < 0) {
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	
	if (isset ($_POST['exclusiva']) && $_POST['exclusiva'] == 1) {
		$exclu = 1;
	} else {
		$exclu = 0;
	}
	
	require_once "../mysql-con.php";
	
	if ($_POST['modo'] == 'nuevo') {
		$query = sprintf ("INSERT INTO Evaluaciones (Descripcion, Exclusiva, Apertura, Cierre) VALUES ('%s', '%s', FROM_UNIXTIME(%s), FROM_UNIXTIME(%s));", mysql_real_escape_string ($_POST['descripcion']), $exclu, $_POST['inicio'], $_POST['fin']);
		
		$result = mysql_query ($query);
		
		mysql_close ($mysql_con);
		if (!$result) {
			agrega_mensaje (3, "Error desconocido");
		}
		
		agrega_mensaje (0, "La forma de evaluación ha sido creada");
		exit;
	} else if ($_POST['modo'] == 'editar') {
		if (!isset ($_POST['id']) || $_POST['id'] < 1) {
			agrega_mensaje (3, "Error desconocido");
			exit;
		}
		/* UPDATE `computacion`.`Evaluaciones` SET `Exclusiva` = '1', `Apertura` = '2012-02-02 01:40:00', `Cierre` = '2012-02-05 23:59:00' WHERE `Evaluaciones`.`Id` = 4; */
		$query = sprintf ("UPDATE Evaluaciones SET Descripcion = '%s', Exclusiva = '%s', Apertura = FROM_UNIXTIME (%s), Cierre = FROM_UNIXTIME (%s) WHERE Id = '%s'", mysql_real_escape_string ($_POST['descripcion']), $exclu, $_POST['inicio'], $_POST['fin'], $_POST['id']);
		
		$result = mysql_query ($query);
		
		mysql_close ($mysql_con);
		if (!$result) {
			agrega_mensaje (3, "Error desconocido");
		}
		
		agrega_mensaje (0, "La forma de evaluación ha sido actualizada");
		exit;
	}
?>
