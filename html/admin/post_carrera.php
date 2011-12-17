<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['admin_carreras']) || $_SESSION['permisos']['admin_carreras'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	
	if (!isset ($_POST['modo']) || ($_POST['modo'] != 'nuevo' && $_POST['modo'] != 'editar')) {
		header ("Location: carreras.php");
		exit;
	}
	
	/* Validar la clave la carrera */
	if (!preg_match ("/^([A-Za-z]){3}$/", $_POST['clave'])) {
		header ("Location: carreras.php?e=clave");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	$_POST['clave'] = strtoupper ($_POST['clave']);
	
	if ($_POST['modo'] == 'nuevo') {
		/* INSERT INTO `computacion`.`Carreras` (`Clave`, `Descripcion`) VALUES ('COM', 'Ingeniería en computación'); */
		$query = sprintf ("INSERT INTO Carreras (Clave, Descripcion) VALUES ('%s', '%s');", $_POST['clave'], mysql_real_escape_string ($_POST['descripcion']));
		
		$result = mysql_query ($query, $mysql_con);
	
		if (!$result) {
			header ("Location: carreras.php?e=desconocido");
			exit;
		}
		
		header ("Location: carreras.php?a=ok");
	} else if ($_POST['modo'] == 'editar') {
		$query = sprintf ("UPDATE Carreras SET Descripcion='%s' WHERE Clave='%s'", mysql_real_escape_string ($_POST['descripcion']), $_POST['clave']);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (!$result) {
			header ("Location: materias.php?a=desconocido");
			exit;
		}
		
		header ("Location: carreras.php?a=ok");
	}
	
	mysql_close ($mysql_con);
	exit;
?>
