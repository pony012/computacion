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
	
	database_connect ();
	
	/* Validar el maestro */
	$query = sprintf ("SELECT Codigo FROM Maestros WHERE Codigo='%s'", $_POST['maestro']);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		/* El maestro no existe */
		agrega_mensaje (3, "Maestro desconocido");
		exit;
	}
	
	mysql_free_result ($result);
	
	$query = sprintf ("UPDATE Secciones SET Maestro='%s' WHERE NRC='%s'", mysql_real_escape_string ($_POST['maestro']), $_POST['nrc']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		agrega_mensaje (3, "Error desconocido");
	} else {
		agrega_mensaje (0, "El nrc fué actualizado");
	}
?>
