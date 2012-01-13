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
	
	if (!preg_match ("/^([0-9]){1,7}$/", $_POST['maestro'])) {
		header ("Location: aplicadores_general.php?e=maestro");
		exit;
	}
	
	settype ($_POST['fechahora'], 'integer');
	settype ($_POST['evaluacion'], 'integer');
	
	require_once '../mysql-con.php';
	
	$lugar = mysql_real_escape_string ($_POST['salon']);
	$tiempo_fecha = $_POST['fechahora'] - ($_POST['fechahora'] % 900);
	
	/* SELECT * FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id INNER JOIN Materias AS M ON P.Clave = M.Clave */
	$query = sprintf ("SELECT P.Tipo FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Tipo='%s' AND P.Clave='%s' AND E.Exclusiva = 0", $_POST['evaluacion'], $_POST['materia']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: aplicadores_general.php?e=noexiste");
		exit;
	}
	
	mysql_free_result ($result);
	
	/* Validar el maestro */
	$query = sprintf ("SELECT Codigo FROM Maestros WHERE Codigo = '%s'", $_POST['maestro']);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: aplicadores_general.php?e=maestro");
		exit;
	}
	
	mysql_free_result ($result);
	
	/* Empezar el query extendido */
	$query_aplicadores = "INSERT INTO Aplicadores (Alumno, Materia, Tipo, Salon, FechaHora, Maestro) VALUES ";
	
	foreach ($_POST['alumno'] as $value) {
		$query_alumno = sprintf ("SELECT Codigo FROM Alumnos WHERE Codigo = '%s'", $value);
		$result = mysql_query ($query_alumno);
		if (mysql_num_rows ($result) == 0) {
			mysql_free_result ($result);
			continue;
		}
		
		mysql_free_result ($result);
		
		$query_aplicadores = $query_aplicadores . sprintf ("('%s', '%s', '%s', '%s', FROM_UNIXTIME ('%s'), '%s'),", $value, $_POST['materia'], $_POST['evaluacion'], $lugar, $tiempo_fecha, $_POST['maestro']);
	}
	
	$query_aplicadores = substr_replace ($query_aplicadores, " ", -1) . " ON DUPLICATE KEY UPDATE Maestro=VALUES(Maestro), Salon=VALUES(Salon), FechaHora=VALUES(FechaHora);";
	
	$result = mysql_query ($query_aplicadores, $mysql_con);
	
	if (!$result) {
		header ("Location: aplicadores_general.php?e=unknown");
	} else {
		header ("Location: aplicadores_general.php?a=ok");
	}
	mysql_close ($mysql_con);
	exit;
?>
