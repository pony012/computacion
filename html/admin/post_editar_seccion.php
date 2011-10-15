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
	
	/* Validar primero el NRC */
	if (!preg_match ("/^([0-9]){5}$/", $_POST['nrc'])) {
		header ("Location: secciones.php?e=nrc");
		exit;
	}
	
	require_once "../mysql-con.php";
	
	/* Validar el maestro */
	$query = sprintf ("SELECT Codigo FROM Maestros WHERE Codigo='%s'", $_POST['maestro']);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		/* El maestro no existe */
		header ("Location: secciones.php?e=maestro");
		mysql_close ($mysql_con);
		exit;
	}
	
	mysql_free_result ($result);
	
	$query = sprintf ("UPDATE Secciones SET Maestro='%s' WHERE NRC='%s'", mysql_real_escape_string ($_POST['maestro']), $_POST['nrc']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		header ("Location: secciones.php?a=desconocido");
	} else {
		header ("Location: secciones.php?a=ok");
	}
	
	mysql_close ($mysql_con);
	exit;
?>
