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
	
	header ("Location: secciones.php");
	
	/* Validar primero el NRC */
	if (!isset ($_GET['nrc']) || !preg_match ("/^([0-9]){1,5}$/", $_GET['nrc'])) {
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 3;
		$_SESSION['m_klass'] = 's_nrc';
		exit;
	}
	
	if (!isset ($_GET['confirmado_js']) || $_GET['confirmado_js'] != 1) {
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 1;
		$_SESSION['m_klass'] = 'n_js';
		exit;
	}
	
	require_once '../mysql-con.php';
	
	/* Impedir que eliminen la sección si tiene alumnos */
	$query = sprintf ("SELECT * FROM Grupos WHERE Nrc='%s'", $_GET['nrc']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows($result) == 0) {
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 1;
		$_SESSION['m_klass'] = 's_r_uso';
		exit;
	}
	
	mysql_free_result ($result);
	
	$query = sprintf ("DELETE FROM Secciones WHERE Nrc='%s'", $_GET['nrc']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if ($result) {
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 0;
		$_SESSION['m_klass'] = 's_r_ok';
	} else {
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 3;
		$_SESSION['m_klass'] = 's_r_no';
	}
	
	mysql_close ($mysql_con);
?>
