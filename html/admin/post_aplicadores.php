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
	
	if (!isset ($_POST['id'])) {
		header ("Location: aplicadores_general.php?e=unknown");
		exit;
	}
	
	settype ($_POST['id'], 'integer');
	settype ($_POST['fecha'], 'integer');
	$_POST['fecha'] = $_POST['fecha'] - ($_POST['fecha'] % 900);
	
	require_once '../mysql-con.php';
	
	$_POST['salon'] = mysql_real_escape_string ($_POST['salon']);
	
	/* Validar el maestro */
	if ($_POST['maestro'] != "NULL") {
		settype ($_POST['maestro'], 'integer');
		/* SELECT * FROM Maestros WHERE Codigo = '%s' */
		$query = sprintf ("SELECT Codigo FROM Maestros WHERE Codigo = '%s'", $_POST['maestro']);
		$result = mysql_query ($query, $mysql_con);
	
		if (mysql_num_rows ($result) == 0) {
			header ("Location: aplicadores_general.php?e=maestro");
			exit;
		}
	
		mysql_free_result ($result);
	}
	
	/* SELECT * FROM Salones_Aplicadores AS SA INNER JOIN Evaluaciones as E ON SA.Tipo = E.Id INNER JOIN Materias AS M ON SA.Materia = M.Clave WHERE Id = 1 */
	$query = sprintf ("SELECT Materia, Tipo FROM Salones_Aplicadores WHERE Id = '%s'", $_POST['id']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: aplicadores_general.php?e=noexiste");
		exit;
	}
	$datos_id = mysql_fetch_object ($result);
	
	mysql_free_result ($result);
	
	/* Actualizar el maestro, el salón y la hora */
	/* UPDATE `computacion`.`Salones_Aplicadores` SET `Salon` = 'Beta 4', `FechaHora` = '2012-02-13 12:00:00', `Maestro` = '2066907' WHERE `Salones_Aplicadores`.`Id` =1; */
	$query = sprintf ("UPDATE Salones_Aplicadores SET Salon = '%s', FechaHora = FROM_UNIXTIME ('%s'), Maestro = %s WHERE Id = '%s';", $_POST['salon'], $_POST['fecha'], $_POST['maestro'], $_POST['id']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		header ("Location: aplicadores_general.php?e=unknown");
		exit;
	}
	
	/* DELETE FROM Alumnos_Aplicadores WHERE Id=39 */
	$query = sprintf ("DELETE FROM Alumnos_Aplicadores WHERE Id = '%s'", $_POST['id']);
	mysql_query ($query, $mysql_con);
	
	/* Empezar el query extendido */
	$query_aplicadores = "INSERT INTO Alumnos_Aplicadores (Id, Alumno) VALUES ";
	
	foreach ($_POST['alumno'] as $value) {
		$query_alumno = sprintf ("SELECT Codigo FROM Alumnos WHERE Codigo = '%s'", $value);
		$result = mysql_query ($query_alumno);
		if (mysql_num_rows ($result) == 0) {
			mysql_free_result ($result);
			continue;
		}
		
		mysql_free_result ($result);
		
		/* Verificar que cada alumno que se inserte, esté presente en este tipo de evaluación sólo una vez */
		/* DELETE FROM Aa USING Alumnos_Aplicadores AS Aa INNER JOIN Salones_Aplicadores AS Sa ON Aa.Id = Sa.Id WHERE Aa.Alumno = '208566275' AND Sa.Materia = 'CC100' AND Sa.Tipo = '1' */
		
		$query = sprintf ("DELETE FROM Aa USING Alumnos_Aplicadores AS Aa INNER JOIN Salones_Aplicadores AS Sa ON Aa.Id = Sa.Id WHERE Aa.Alumno = '%s' AND Sa.Materia = '%s' AND Sa.Tipo = '%s'", $value, $datos_id->Materia, $datos_id->Tipo);
		mysql_query ($query, $mysql_con);
		
		$query_aplicadores = $query_aplicadores . sprintf ("('%s', '%s'),", $_POST['id'], $value);
	}
	
	$query_aplicadores = substr_replace ($query_aplicadores, ";", -1);
	
	$result = mysql_query ($query_aplicadores, $mysql_con);
	
	if (!$result) {
		header ("Location: aplicadores_general.php?e=unknown");
	} else {
		header ("Location: aplicadores_general.php?a=ok");
	}
	mysql_close ($mysql_con);
	exit;
?>
