<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	if (!has_permiso ('admin_carreras')) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
	
	header ("Location: carreras.php");
	
	if (!isset ($_POST['modo']) || ($_POST['modo'] != 'nuevo' && $_POST['modo'] != 'editar')) {
		agrega_mensaje (1, "Su solicitud no puede ser procesada. Por favor intente de nuevo");
		exit;
	}
	
	/* Validar la clave la carrera */
	if (!preg_match ("/^([A-Za-z]){3}$/", $_POST['clave'])) {
		agrega_mensaje (3, "Clave de carrera incorrecta");
		exit;
	}
	
	$clave_carrera = strtoupper ($_POST['clave']);
	
	database_connect ();
	
	if ($_POST['modo'] == 'nuevo') {
		/* INSERT INTO `computacion`.`Carreras` (`Clave`, `Descripcion`) VALUES ('COM', 'Ingeniería en computación'); */
		$query = sprintf ("INSERT INTO Carreras (Clave, Descripcion) VALUES ('%s', '%s');", $clave_carrera, mysql_real_escape_string ($_POST['descripcion']));
		
		$result = mysql_query ($query, $mysql_con);
	
		if (!$result) {
			agrega_mensaje (3, "Error desconocido");
			exit;
		}
		
		agrega_mensaje (0, sprintf ("La carrera %s fué creada", htmlentities ($_POST['descripcion'])));
	} else if ($_POST['modo'] == 'editar') {
		$query = sprintf ("UPDATE Carreras SET Descripcion='%s' WHERE Clave='%s'", mysql_real_escape_string ($_POST['descripcion']), $clave_carrera);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (!$result) {
			agrega_mensaje (3, "Error desconocido");
			exit;
		}
		
		agrega_mensaje (0, sprintf ("La carrera %s ha sido actualizada", htmlentities ($_POST['descripcion'])));
	}
?>
