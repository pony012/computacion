<?php
	function exit_and_close ($get) {
		mysql_free_result ($result);
		header ("Location: nuevo_grupo.php". $get);
		exit;
	}
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
	
	/* Campos que recibo por POST
	 * nrc: El nrc de la materia, no debe estar duplicado.
	 * materia: La clave de la materia
	 * seccion:
	 * maestro:
	 * n_puntos:
	 * puntos_depa1:
	 * puntos_depa2:
	 * tiene_puntos:
	 * tiene_depa1:
	 * tiene_depa2:
	 */
	
	require_once "../mysql-con.php";
	
	/* Saneado rápido */
	foreach ($_POST as $key => $valor) {
		$_POST[ $key ] = mysql_real_escape_string ($valor);
	}
	
	/* Validar primero el NRC */
	if (!preg_match ("/^([0-9])+$/", $_POST['nrc']) || strlen ($_POST['nrc'])) {
		header ("Location: nuevo_grupo.php?e=nrc");
		exit;
	}
	
	/* Validar la seccion */
	if (!preg_match ("/^([Dd])([0-9]){2}$/", $_POST['seccion'])) {
		header ("Location: nuevo_grupo.php?e=seccion");
		exit;
	}
	
	if ($_POST['tiene_puntos'] == "off" && $_POST['tiene_depa1'] == "off" && $_POST['tiene_depa2'] == "off") {
		/* Error, debe haber alguna forma de evaluar en la materia */
		header ("Location: nuevo_grupo.php?e=eval");
		exit;
	}
	
	/* Validar la materia */
	$query = sprintf ("SELECT Clave FROM Materias WHERE Clave='%s'", $_POST['materia']);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		/* La materia no existe */
		exit_and_close ("?e=materia");
	}
	mysql_free_result ($result);
	
	/* Validar el maestro */
	$query = sprintf ("SELECT Codigo FROM Maestros WHERE Codigo='%s'", $_POST['maestro']);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		/* El maestro no existe */
		exit_and_close ("?e=maestro");
	}
	
	
?>
