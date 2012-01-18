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
	/* Campos que recibo por POST:
	 * Nrc: El nrc a actualizar
	 * maestro: El nuevo maestro
	 */
	
	header ("Location: secciones.php");
	
	/* Validar primero el NRC */
	if (!isset ($_POST['nrc']) || !preg_match ("/^([0-9]){1,5}$/", $_POST['nrc'])) {
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 3;
		$_SESSION['m_klass'] = 's_nrc';
		exit;
	}
	
	require_once "../mysql-con.php";
	
	/* Validar el maestro */
	$query = sprintf ("SELECT Codigo FROM Maestros WHERE Codigo='%s'", $_POST['maestro']);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		/* El maestro no existe */
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 3;
		$_SESSION['m_klass'] = 's_maestro';
		mysql_close ($mysql_con);
		exit;
	}
	
	mysql_free_result ($result);
	
	$query = sprintf ("UPDATE Secciones SET Maestro='%s' WHERE NRC='%s'", mysql_real_escape_string ($_POST['maestro']), $_POST['nrc']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 1;
		$_SESSION['m_klass'] = 'unknown';
	} else {
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 1;
		$_SESSION['m_klass'] = 's_a_ok';
	}
	
	mysql_close ($mysql_con);
	exit;
?>
