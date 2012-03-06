<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	if (!has_permiso ('admin_evaluaciones')) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
	
	header ("Location: evaluaciones.php");
	
	if (!isset ($_POST['modo']) || ($_POST['modo'] != 'nuevo' && $_POST['modo'] != 'editar')) exit;
	
	/* Validaciones varias sobre los campos */
	if (!isset ($_POST['descripcion']) || trim ($_POST['descripcion']) == "") {
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	
	/* El campo select del estado */
	if (!isset ($_POST['estado']) || ($_POST['estado'] != 'open' && $_POST['estado'] != 'closed' && $_POST['estado'] != 'time')) {
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	
	$estado = $_POST['estado'];
	$grupo_eval = strval (intval ($_POST['grupo']));
	$hora_inicio = strval (intval ($_POST['inicio']));
	$hora_fin = strval (intval ($_POST['fin']));
	
	if ($_POST['estado'] == 'time') {
		if ($hora_inicio >= $hora_fin || $hora_inicio < 0 || $hora_fin < 0) {
			agrega_mensaje (3, "Datos incorrectos");
			exit;
		}
	}
	
	if (isset ($_POST['exclusiva']) && $_POST['exclusiva'] == 1) {
		$exclu = 1;
	} else {
		$exclu = 0;
	}
	
	database_connect ();
	
	if ($_POST['modo'] == 'nuevo') {
		/* Verificar que el grupo de evaluaciones exista */
		if (!isset ($grupo_eval) || $grupo_eval < 0) {
			agrega_mensaje (3, "Grupo de evaluacion noexistente");
			exit;
		}
		
		$query = sprintf ("SELECT Id FROM Grupos_Evaluaciones WHERE Id = '%s'", $grupo_eval);
		$result = mysql_query ($query, $mysql_con);
	
		if (mysql_num_rows ($result) == 0) {
			agrega_mensaje (3, "Grupo de evaluacion no existente");
			exit;
		}
		
		mysql_free_result ($result);
		
		$query = sprintf ("INSERT INTO Evaluaciones (Grupo, Descripcion, Estado, Exclusiva, Apertura, Cierre) VALUES ('%s', '%s', '%s', '%s', FROM_UNIXTIME(%s), FROM_UNIXTIME(%s));", $grupo_eval, mysql_real_escape_string (trim ($_POST['descripcion'])), $estado, $exclu, $hora_inicio, $hora_fin);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (!$result) {
			agrega_mensaje (3, "Error desconocido");
		} else {
			agrega_mensaje (0, "La forma de evaluación ha sido creada");
		}
		exit;
	} else if ($_POST['modo'] == 'editar') {
		if (!isset ($_POST['id']) || $_POST['id'] < 1) {
			agrega_mensaje (3, "Error desconocido");
			exit;
		}
		$id_eval = strval (intval ($_POST['id']));
		
		/* UPDATE `computacion`.`Evaluaciones` SET `Exclusiva` = '1', `Apertura` = '2012-02-02 01:40:00', `Cierre` = '2012-02-05 23:59:00' WHERE `Evaluaciones`.`Id` = 4; */
		$query = sprintf ("UPDATE Evaluaciones SET Descripcion = '%s', Exclusiva = '%s', Estado = '%s', Apertura = FROM_UNIXTIME (%s), Cierre = FROM_UNIXTIME (%s) WHERE Id = '%s'", mysql_real_escape_string (trim ($_POST['descripcion'])), $exclu, $estado, $hora_inicio, $hora_fin, $id_eval);
		
		$result = mysql_query ($query);
		
		if (!$result) {
			agrega_mensaje (3, "Error desconocido");
		} else {
			agrega_mensaje (0, "La forma de evaluación ha sido actualizada");
		}
		exit;
	}
?>
