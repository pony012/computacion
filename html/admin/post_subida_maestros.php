<html></html>
<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	if (!isset ($_POST['nrc']) || !isset ($_POST['eval'])) {
		header ("Location: ver_maestro.php?codigo=" . $_SESSION['codigo']);
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	settype ($_POST['eval'], 'integer');
	
	/* Validar primero el NRC */
	if (!preg_match ("/^([0-9]){1,5}$/", $_POST['nrc'])) {
		header ("Location: ver_maestro.php?codigo=" . $_SESSION['codigo']);
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	header (sprintf ("Location: ver_grupo.php?nrc=%s", $_POST['nrc']));
	
	if (count ($_POST['alumno']) == 0 || count ($_POST['alumno']) != count ($_POST['valor'])) {
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	/* Primero verificar que esté abierta la materia para subida de calificaciones */
	/* SELECT * FROM Secciones AS S INNER JOIN Porcentajes AS P ON S.Materia = P.Clave INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE S.Nrc = '1758' AND P.Tipo = '1' AND EXCLUSIVA = 1 */
	/* Esta query descarta nrc inexistente, tipo de evaluacion inexistente para esa materia, y que la forma de evaluación no sea exclusiva del maestro */
	$query = sprintf ("SELECT S.Maestro, E.Estado, UNIX_TIMESTAMP(E.Apertura) AS Apertura, UNIX_TIMESTAMP(E.Cierre) AS Cierre, P.Ponderacion FROM Secciones AS S INNER JOIN Porcentajes AS P ON S.Materia = P.Clave INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE S.Nrc = '%s' AND P.Tipo = '%s' AND E.Exclusiva = 1", $_POST['nrc'], $_POST['eval']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	$datos_eval = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	if ($datos_eval->Maestro != $_SESSION['codigo']) {
		/* Lo siento, pero tu no eres el maestro de este grupo */
		agrega_mensaje (3, "Privilegios insuficientes");
		exit;
	}
	
	/* Verificar que los tiempos estén abiertos */
	if ($datos_eval->Estado == 'closed') { /* Esta forma de evaluación está deshabilitada */
		agrega_mensaje (1, "La forma de evaluación está cerrada");
		exit;
	}
	
	if ($datos_eval->Estado == 'time') {
		$now = time ();
		if ($now < $object->Apertura || $now >= $object->Cierre) {
			agrega_mensaje (1, "Fuera de tiempo para subida de calificaciones");
			exit;
		}
	}
	
	/* Ahora verificar las calificaciones */
	$calificaciones = array ();
	
	foreach ($_POST['alumno'] as $index => $valor) {
		if ($_POST['valor'][$index] == "") {
			$calificaciones [$valor] = "NULL";
		} else {
			$calificaciones [$valor] = (int) $_POST['valor'][$index];
		
			if ($calificaciones [$valor] < 0 || $calificaciones [$valor] > $datos_eval->Ponderacion) {
				agrega_mensaje (3, "Datos incorrectos");
				exit;
			}
		}
	}
	
	/* SELECT C.Alumno, C.Valor, A.Apellido, A.Nombre FROM Calificaciones AS C INNER JOIN Alumnos AS A ON C.Alumno = A.Codigo WHERE C.Nrc ='%s' AND C.Tipo = '%s' ORDER BY A.Apellido, A.Nombre */
	$query = sprintf ("SELECT Alumno, Valor FROM Calificaciones WHERE Nrc = '%s' AND Tipo = '%s'", $_POST['nrc'], $_POST['eval']);
	
	$result = mysql_query ($query, $mysql_con);
	
	while (($object = mysql_fetch_object ($result))) {
		if (isset ($calificaciones [$object->Alumno])) {
			$query_cals = sprintf ("UPDATE Calificaciones SET Valor = %s WHERE Alumno = '%s' AND Nrc = '%s' AND Tipo = '%s'; ", $calificaciones[$object->Alumno], $object->Alumno, $_POST['nrc'], $_POST['eval']);
			mysql_query ($query_cals, $mysql_con);
		}
	}
	
	mysql_free_result ($result);
	
	/* Actualizar los promedios */
	$query = sprintf ("SELECT AVG (Valor) AS AVG FROM Calificaciones WHERE Nrc = '%s' AND Tipo = '%s' AND Valor IS NOT NULL", $_POST['nrc'], $_POST['eval']);
	$result = mysql_query ($query, $mysql_con);
	$promedio = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	if (is_null ($promedio->AVG)) {
		$query = sprintf ("DELETE FROM Promedios WHERE Tipo = '%s' AND Nrc = '%s'", $_POST['eval'], $_POST['nrc']);
	} else {
		$query = sprintf ("INSERT INTO Promedios (Nrc, Tipo, Promedio) VALUES ('%s', '%s', %s) ON DUPLICATE KEY UPDATE Promedio = Values (Promedio)", $_POST['nrc'], $_POST['eval'], $promedio->AVG);
	}
	
	mysql_query ($query, $mysql_con);
	
	agrega_mensaje (0, "Calificaciones subidas exitosamente");
	
	mysql_close ($mysql_con);
?>
