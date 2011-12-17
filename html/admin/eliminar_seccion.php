<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	/* Luego verificar si tiene el permiso de crear grupos */
	if (!isset ($_SESSION['permisos']['crear_grupos']) || $_SESSION['permisos']['crear_grupos'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	
	if (!isset ($_GET['nrc'])) {
		header ("Location: secciones.php?r=null");
		exit;
	}
	
	if (!isset ($_GET['confirmado_js']) || $_GET['confirmado_js'] != 1) {
		header ("Location: secciones.php?r=err");
		exit;
	}
	
	/* Validar primero el NRC */
	if (!preg_match ("/^([0-9]){1,5}$/", $_GET['nrc'])) {
		header ("Location: secciones.php?e=nrc");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	/* Impedir que eliminen la sección si tiene alumnos */
	$query = sprintf ("SELECT * FROM Grupos WHERE Nrc='%s'", $_GET['nrc']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows($result) == 0) {
		header ("Location: secciones.php?r=uso");
		exit;
	}
	
	mysql_free_result ($result);
	
	$query = sprintf ("DELETE FROM Secciones WHERE Nrc='%s'", $_GET['nrc']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if ($result) {
		header ("Location: secciones.php?r=ok");
	} else {
		header ("Location: secciones.php?r=no");
	}
	
	mysql_close ($mysql_con);
?>
