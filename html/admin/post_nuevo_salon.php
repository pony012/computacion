<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['asignar_aplicadores']) || $_SESSION['permisos']['asignar_aplicadores'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	
	if (!preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_POST['materia'])) {
		header ("Location: aplicadores_general.php?e=clave");
		exit;
	}
	
	if (!preg_match ("/^([0-9]){1,7}$/", $_POST['maestro']) && $_POST['maestro'] != "NULL") {
		header ("Location: aplicadores_general.php?e=maestro");
		exit;
	}
	
	settype ($_POST['fecha'], 'integer');
	$_POST['fecha'] = $_POST['fecha'] - ($_POST['fecha'] % 900);
	settype ($_POST['evaluacion'], 'integer');
	
	require_once '../mysql-con.php';
	
	/* SELECT * FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id INNER JOIN Materias AS M ON P.Clave = M.Clave */
	$query = sprintf ("SELECT E.Descripcion AS Evaluacion, M.Descripcion, P.Clave, P.Tipo FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id INNER JOIN Materias AS M ON P.Clave = M.Clave WHERE P.Tipo='%s' AND P.Clave='%s' AND E.Exclusiva = 0", $_POST['evaluacion'], $_POST['materia']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: aplicadores_general.php?e=noexiste");
		exit;
	}
	
	$datos = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	if ($_POST['maestro'] != "NULL") {
		$query = sprintf ("SELECT Codigo, Nombre, Apellido FROM Maestros WHERE Codigo = '%s'", $_POST['maestro']);
		$result = mysql_query ($query, $mysql_con);
		
		if (mysql_num_rows ($result) == 0) {
			header ("Location: aplicadores_general.php?e=maestro");
			exit;
		}
		
		mysql_free_result ($result);
	}
	
	$_POST['salon'] = mysql_real_escape_string ($_POST['salon']);
	
	$query = sprintf ("INSERT INTO Salones_Aplicadores (Materia, Tipo, Salon, FechaHora, Maestro) VALUES ('%s', '%s', '%s', FROM_UNIXTIME ('%s'), %s);", $_POST['materia'], $_POST['evaluacion'], $_POST['salon'], $_POST['fecha'], $_POST['maestro']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		header ("Location: aplicadores_general.php?e=unknown");
	} else {
		$num = mysql_insert_id ($mysql_con);
		
		header ("Location: asignar_alumnos_aplicadores.php?salon=" . $num);
	}
	
	mysql_close ($mysql_con);
?>
