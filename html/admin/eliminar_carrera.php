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
	
	if (!isset ($_GET['clave']) || !preg_match ("/^([A-Za-z]){3}$/", $_GET['clave'])) {
		header ("Location: carreras.php?r=null");
		exit;
	}
	
	if (!isset ($_GET['confirmado_js']) || $_GET['confirmado_js'] != 1) {
		header ("Location: carreras.php?r=err");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	/* Impedir que eliminen la materia si tiene secciones
	 * que dependan de ella */
	/* SELECT * FROM Alumnos WHERE Carrera=COM limit 1 */
	$query = sprintf ("SELECT * FROM Alumnos WHERE Carrera='%s' LIMIT 1", $_GET['clave']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) > 0) {
		mysql_free_result ($result);
		header ("Location: carreras.php?r=uso");
		exit;
	}
	
	mysql_free_result ($result);
	
	/* Si no tiene alguna dependencia, entonces eliminar la carrera */
	$query = sprintf ("DELETE FROM Carreras WHERE Clave='%s'", $_GET['clave']);
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		header ("Location: carreras.php?r=no");
		exit;
	}
	
	header ("Location: carreras.php?r=ok");
	mysql_close ($mysql_con);
?>
