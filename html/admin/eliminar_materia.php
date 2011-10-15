<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['crear_materias']) || $_SESSION['permisos']['crear_materias'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	
	if (!isset ($_GET['clave'])) {
		header ("Location: materias.php?r=null");
		exit;
	}
	
	if (!isset ($_GET['confirmado_js']) || $_GET['confirmado_js'] != 1) {
		header ("Location: materias.php?r=err");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	/* Impedir que eliminen la materia si tiene secciones
	 * que dependan de ella */
	$query = sprintf ("SELECT * FROM Secciones WHERE Materia='%s' LIMIT 1", mysql_real_escape_string ($_GET['clave']));
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) > 0) {
		header ("Location: materias.php?r=uso");
		exit;
	}
	
	mysql_free_result ($result);
	
	/* Si no tiene alguna dependencia, entonces eliminarla */
	$query = sprintf ("DELETE FROM Materias WHERE Clave='%s'", mysql_real_escape_string ($_GET['clave']));
	
	$result = mysql_query ($query, $mysql_con);
	
	if ($result) {
		header ("Location: materias.php?r=ok");
		exit;
	} else {
		header ("Location: materias.php?r=no");
	}
?>