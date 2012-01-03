<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['admin_evaluaciones']) || $_SESSION['permisos']['admin_evaluaciones'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	
	if (!isset ($_POST['modo']) || ($_POST['modo'] != 'nuevo' && $_POST['modo'] != 'editar')) {
		header ("Location: evaluaciones.php");
		exit;
	}
	/* Validaciones varias sobre los campos */
	if (!isset ($_POST['descripcion']) || $_POST['descripcion'] == "") {
		header ("Location: evaluaciones.php");
		exit;
	}
	
	settype ($_POST['inicio'], 'integer');
	settype ($_POST['fin'], 'integer');
	
	if ($_POST['inicio'] >= $_POST['fin'] || $_POST['inicio'] < 0 || $_POST['fin'] < 0) {
		header ("Location: evaluaciones.php?err=fechas");
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
			header ("Location: evaluaciones.php?e=unknown");
		} else {
			header ("Location: evaluaciones.php?n=ok");
		}
		exit;
	} else if ($_POST['modo'] == 'editar') {
		if (!isset ($_POST['id']) || $_POST['id'] < 1) {
			header ("Location: evaluaciones.php?e=err");
			exit;
		}
		/* UPDATE `computacion`.`Evaluaciones` SET `Exclusiva` = '1', `Apertura` = '2012-02-02 01:40:00', `Cierre` = '2012-02-05 23:59:00' WHERE `Evaluaciones`.`Id` = 4; */
		$query = sprintf ("UPDATE Evaluaciones SET Descripcion = '%s', Exclusiva = '%s', Apertura = FROM_UNIXTIME (%s), Cierre = FROM_UNIXTIME (%s) WHERE Id = '%s'", mysql_real_escape_string ($_POST['descripcion']), $exclu, $_POST['inicio'], $_POST['fin'], $_POST['id']);
		
		$result = mysql_query ($query);
		
		mysql_close ($mysql_con);
		if (!$result) {
			header ("Location: evaluaciones.php?e=unknown");
		} else {
			header ("Location: evaluaciones.php?a=ok");
		}
		exit;
	}
?>
