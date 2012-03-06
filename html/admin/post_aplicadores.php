<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	if (!has_permiso ('asignar_aplicadores')) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		agrega_mensaje (3, "Privilegios insuficientes");
		exit;
	}
	
	header ("Location: aplicadores_general.php");
	
	if (!isset ($_POST['alumno']) || !is_array ($_POST['alumno'])) {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	if (!isset ($_POST['id']) || !isset ($_POST['maestro'])) {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	/* Validar al maestro */
	if (!isset ($_POST['maestro']) || (!preg_match ("/^([0-9]){1,7}$/", $_POST['maestro']) && $_POST['maestro'] !== "NULL")) {
		agrega_mensaje (3, "Error al procesar los datos");
		exit;
	}
	
	$maestro_aplicador = $_POST['maestro'];
	$id_salon = strval (intval ($_POST['id']));
	$nueva_fecha = strval (intval ($_POST['fecha']));
	$nueva_fecha = $nueva_fecha - ($nueva_fecha % 900);
	
	database_connect ();
	
	$nombre_salon = mysql_real_escape_string ($_POST['salon']);
	
	/* Validar el maestro */
	if ($maestro_aplicador != "NULL") {
		/* SELECT * FROM Maestros WHERE Codigo = '%s' */
		$query = sprintf ("SELECT Codigo FROM Maestros WHERE Codigo = '%s'", $maestro_aplicador);
		$result = mysql_query ($query, $mysql_con);
	
		if (mysql_num_rows ($result) == 0) {
			agrega_mensaje (3, "El maestro especificado no existe");
			exit;
		}
	
		mysql_free_result ($result);
	}
	
	/* SELECT * FROM Salones_Aplicadores AS SA INNER JOIN Evaluaciones as E ON SA.Tipo = E.Id INNER JOIN Materias AS M ON SA.Materia = M.Clave WHERE Id = 1 */
	$query = sprintf ("SELECT Materia, Tipo FROM Salones_Aplicadores WHERE Id = '%s'", $id_salon);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	$datos_id = mysql_fetch_object ($result);
	
	mysql_free_result ($result);
	
	/* Actualizar el maestro, el salón y la hora */
	/* UPDATE `computacion`.`Salones_Aplicadores` SET `Salon` = 'Beta 4', `FechaHora` = '2012-02-13 12:00:00', `Maestro` = '2066907' WHERE `Salones_Aplicadores`.`Id` =1; */
	$query = sprintf ("UPDATE Salones_Aplicadores SET Salon = '%s', FechaHora = FROM_UNIXTIME ('%s'), Maestro = %s WHERE Id = '%s';", $nombre_salon, $nueva_fecha, $maestro_aplicador, $id_salon);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	/* DELETE FROM Alumnos_Aplicadores WHERE Id=39 */
	$query = sprintf ("DELETE FROM Alumnos_Aplicadores WHERE Id = '%s'", $id_salon);
	mysql_query ($query, $mysql_con);
	
	/* Empezar el query extendido */
	$query_aplicadores = "INSERT INTO Alumnos_Aplicadores (Id, Alumno) VALUES ";
	
	foreach ($_POST['alumno'] as $value) {
		$un_alumno = strval (intval ($value))
		$query_alumno = sprintf ("SELECT Codigo FROM Alumnos WHERE Codigo = '%s'", $un_alumno);
		$result = mysql_query ($query_alumno);
		if (mysql_num_rows ($result) == 0) {
			mysql_free_result ($result);
			continue;
		}
		
		mysql_free_result ($result);
		
		/* Verificar que cada alumno que se inserte, esté presente en este tipo de evaluación sólo una vez */
		/* DELETE FROM Aa USING Alumnos_Aplicadores AS Aa INNER JOIN Salones_Aplicadores AS Sa ON Aa.Id = Sa.Id WHERE Aa.Alumno = '208566275' AND Sa.Materia = 'CC100' AND Sa.Tipo = '1' */
		
		$query = sprintf ("DELETE FROM Aa USING Alumnos_Aplicadores AS Aa INNER JOIN Salones_Aplicadores AS Sa ON Aa.Id = Sa.Id WHERE Aa.Alumno = '%s' AND Sa.Materia = '%s' AND Sa.Tipo = '%s'", $un_alumno, $datos_id->Materia, $datos_id->Tipo);
		mysql_query ($query, $mysql_con);
		
		$query_aplicadores = $query_aplicadores . sprintf ("('%s', '%s'),", $id_salon, $un_alumno);
	}
	
	$query_aplicadores = substr_replace ($query_aplicadores, ";", -1);
	
	$result = mysql_query ($query_aplicadores, $mysql_con);
	
	if (!$result) {
		agrega_mensaje (3, "Error desconocido");
	}
	
	header ("Location: ver_salon_aplicador.php?id=" . $id_salon);
	agrega_mensaje (0, "Salon actualizado correctamente");
	agrega_mensaje (0, "Alumnos asignados correctamente");
	exit;
?>
