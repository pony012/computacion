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
	
	/* El campo select del estado */
	if (!isset ($_POST['estado']) || ($_POST['estado'] != 'open' && $_POST['estado'] != 'closed' && $_POST['estado'] != 'time')) {
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	
	settype ($_POST['grupo'], 'integer');
	settype ($_POST['inicio'], 'integer');
	settype ($_POST['fin'], 'integer');
	
	if ($_POST['estado'] == 'time') {
		if ($_POST['inicio'] >= $_POST['fin'] || $_POST['inicio'] < 0 || $_POST['fin'] < 0) {
			agrega_mensaje (3, "Datos incorrectos");
			exit;
		}
	}
	
	if (isset ($_POST['exclusiva']) && $_POST['exclusiva'] == 1) {
		$exclu = 1;
	} else {
		$exclu = 0;
	}
	
	require_once "../mysql-con.php";
	
	if ($_POST['modo'] == 'nuevo') {
		/* Verificar que el grupo de evaluaciones exista */
		if (!isset ($_POST['grupo']) || $_POST['grupo'] < 0) {
			agrega_mensaje (3, "Grupo de evaluacion noexistente");
			exit;
		}
	
		$query = sprintf ("SELECT Id FROM Grupos_Evaluaciones WHERE Id = '%s'", $_POST['grupo']);
		$result = mysql_query ($query, $mysql_con);
	
		if (mysql_num_rows ($result) == 0) {
			agrega_mensaje (3, "Grupo de evaluacion no existente");
			exit;
		}
	
		mysql_free_result ($result);
		
		$query = sprintf ("INSERT INTO Evaluaciones (Grupo, Descripcion, Estado, Exclusiva, Apertura, Cierre) VALUES ('%s', '%s', '%s', FROM_UNIXTIME(%s), FROM_UNIXTIME(%s));", $_POST['grupo'], mysql_real_escape_string ($_POST['descripcion']), $_POST['estado'], $exclu, $_POST['inicio'], $_POST['fin']);
		
		$result = mysql_query ($query);
		
		mysql_close ($mysql_con);
		if (!$result) {
			agrega_mensaje (3, "Error desconocido");
			exit;
		}
		
		agrega_mensaje (0, "La forma de evaluación ha sido creada");
		exit;
	} else if ($_POST['modo'] == 'editar') {
		if (!isset ($_POST['id']) || $_POST['id'] < 1) {
			agrega_mensaje (3, "Error desconocido");
			exit;
		}
		/* UPDATE `computacion`.`Evaluaciones` SET `Exclusiva` = '1', `Apertura` = '2012-02-02 01:40:00', `Cierre` = '2012-02-05 23:59:00' WHERE `Evaluaciones`.`Id` = 4; */
		$query = sprintf ("UPDATE Evaluaciones SET Descripcion = '%s', Exclusiva = '%s', Estado = '%s', Apertura = FROM_UNIXTIME (%s), Cierre = FROM_UNIXTIME (%s) WHERE Id = '%s'", mysql_real_escape_string ($_POST['descripcion']), $exclu, $_POST['estado'], $_POST['inicio'], $_POST['fin'], $_POST['id']);
		
		$result = mysql_query ($query);
		
		mysql_close ($mysql_con);
		if (!$result) {
			agrega_mensaje (3, "Error desconocido");
			exit;
		}
		
		agrega_mensaje (0, "La forma de evaluación ha sido actualizada");
		exit;
	}
?>
