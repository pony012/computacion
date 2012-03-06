<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	/* Luego verificar si tiene el permiso de crear grupos */
	if (!has_permiso ('crear_grupos')) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
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
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	
	if (!isset ($_POST['maestro']) || !preg_match ("/^([0-9]){1,7}$/", $_POST['maestro'])) {
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	
	$nrc = strval (intval ($_POST['nrc']));
	$maestro = strval (intval ($_POST['maestro']));
	
	database_connect ();
	
	/* Validar el nrc */
	$query = sprintf ("SELECT Nrc FROM Secciones WHERE Nrc = '%s'", $nrc);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		/* El Nrc no existe */
		agrega_mensaje (3, "Nrc desconocido");
		exit;
	}
	
	mysql_free_result ($result);
	
	/* Validar el maestro */
	$query = sprintf ("SELECT Codigo FROM Maestros WHERE Codigo='%s'", $maestro);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		/* El maestro no existe */
		agrega_mensaje (3, "Maestro desconocido");
		exit;
	}
	
	mysql_free_result ($result);
	
	/* Ahora sí, actualizar la tabla */
	$query = sprintf ("UPDATE Secciones SET Maestro = '%s' WHERE Nrc = '%s'", $maestro, $nrc);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		agrega_mensaje (3, "Error desconocido");
	} else {
		agrega_mensaje (0, "El nrc fué actualizado");
	}
?>
