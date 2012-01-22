<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['asignar_aplicadores']) || $_SESSION['permisos']['asignar_aplicadores'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	
	if (!isset ($_GET['confirmado_js']) || $_GET['confirmado_js'] != 1) {
		header ("Location: materias.php?r=err");
		exit;
	}
	
	if (!isset ($_GET['id'])) {
		header ("Location: aplicadores_general.php?e=unknown");
		exit;
	}
	
	settype ($_GET['id'], 'integer');
	
	require_once '../mysql-con.php';
	
	/* Verificar que el id del salon exista */
	
	$query = sprintf ("SELECT Id FROM Salones_Aplicadores WHERE Id = '%s'", $_GET['id']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		/* TODO: argumento next para regresar a la página anterior */
		header ("Location: aplicadores_general.php?e=noexiste");
		mysql_close ($mysql_con);
		exit;
	}
	
	mysql_free_result ($result);
	
	$query = sprintf ("DELETE FROM Alumnos_Aplicadores WHERE Id = '%s'", $_GET['id']);
	mysql_query ($query);
	
	$query = sprintf ("DELETE FROM Salones_Aplicadores WHERE Id = '%s'", $_GET['id']);
	mysql_query ($query);
	
	/* TODO: argumento next para regresar a la página anterior */
	header ("Location: aplicadores_general.php?r=ok");
?>
