<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	header ("Location: seleccionar_subida.php");
	
	require_once 'mensajes.php';
	
	/* Validar los datos $_GET */
	if (!isset ($_POST['id'])) {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	$id_salon = strval (intval ($_POST['id']));
	
	if (count ($_POST['alumno']) == 0 || count ($_POST['alumno']) != count ($_POST['valor'])) {
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	/* Verificar que el id del salón exista, y guardar la clave de la materia */
	$query = sprintf ("SELECT * FROM Salones_Aplicadores WHERE Id = '%s'", $id_salon);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	$object = mysql_fetch_object ($result);
	mysql_free_result ($result);
	$materia = $object->Materia;
	$evaluacion = $object->Tipo;
	
	/* Verificar que la materia exista, pertenece a una academia y este maestro es dueño de la academia */
	/* SELECT * FROM Academias AS A INNER JOIN Materias AS M ON M.Academia = A.Id WHERE A.Maestro = '2066907' AND M.Clave = 'ET200' */
	$query = sprintf ("SELECT A.Maestro, A.Subida FROM Academias AS A INNER JOIN Materias AS M ON M.Academia = A.Id WHERE M.Clave = '%s'", $materia);
	
	$result = mysql_query ($query, $mysql_con);
	if (mysql_num_rows ($result) == 0) {
		mysql_free_result ($result);
		agrega_mensaje (1, "La materia no pertenece a una academia");
		exit;
	}
	
	$object = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	if ($object->Maestro != $_SESSION['codigo']) { /* Gracias pero no eres el presidente de la academia */
		agrega_mensaje (1, "Usted no es el presidente de la academia");
		exit;
	}
	
	if ($object->Subida != 1) { /* Tampoco está autorizado para subir calificaciones */
		agrega_mensaje (3, "No tiene permiso para subir calificaciones en esta academia");
		exit;
	}
	
	/* Verificar que la forma de evaluacion exista con la materia especificada
	 * Adicionalmente, recoger si la forma de evaluación está abierta */
	/* SELECT * FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave = 'ET200' AND P.Tipo = 0 */
	
	$query = sprintf ("SELECT *, UNIX_TIMESTAMP (E.Apertura) AS Apertura, UNIX_TIMESTAMP (E.Cierre) AS Cierre FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave = '%s' AND P.Tipo = %s", $materia, $evaluacion);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) { /* La forma de evaluacion no existe para esa materia */
		mysql_free_result ($result);
		agrega_mensaje (3, "La forma de evaluación no existe para esta materia");
		exit;
	}
	
	$object = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	if ($object->Exclusiva != 0) { /* Esta forma de evaluación es para subida del maestro */
		agrega_mensaje (3, "Esta forma de evaluación es para subida del maestro");
		exit;
	}
	
	if ($object->Estado == 'closed') { /* Esta forma de evaluación está deshabilitada */
		agrega_mensaje (1, "La forma de evaluación no permite subida de calificaciones");
		exit;
	} else if ($object->Estado == 'time') {
		$now = time ();
		if ($now < $object->Apertura || $now >= $object->Cierre) {
			setlocale (LC_ALL, "es_MX.UTF-8");
			agrega_mensaje (1, sprintf ("Fuera de tiempo para subida de calificaciones<br>%s se abre el %s", $object->Descripcion, strftime ("%A %e de %B de %Y a las %H:%M", $object->Apertura)));
			exit;
		}
	}
	
	/* Ahora verificar las calificaciones */
	$calificaciones = array ();
	
	foreach ($_POST['alumno'] as $index => $valor) {
		if ($_POST['valor'][$index] == "") {
			$calificaciones [$valor] = "NULL";
		} else {
			$calificaciones [$valor] = intval ($_POST['valor'][$index]);
		
			if ($calificaciones [$valor] < 0 || $calificaciones [$valor] > $object->Ponderacion) {
				agrega_mensaje (3, "Datos incorrectos");
				exit;
			}
		}
	}
	
	/* Ahora sí, después de todas las validaciones, seleccionar cada alumno */
	/* SELECT * FROM Alumnos_Aplicadores AS AA INNER JOIN Calificaciones AS C ON C.Alumno = AA.Alumno INNER JOIN Secciones AS S ON C.Nrc = S.Nrc WHERE AA.Id = 4 AND C.Tipo = '1' AND S.Materia = 'ET209' */
	$lista_nrc = array ();
	
	$query = sprintf ("SELECT C.Alumno, C.Nrc FROM Alumnos_Aplicadores AS AA INNER JOIN Calificaciones AS C ON C.Alumno = AA.Alumno INNER JOIN Secciones AS S ON C.Nrc = S.Nrc WHERE AA.Id = '%s' AND C.Tipo = '%s' AND S.Materia = '%s'", $id_salon, $evaluacion, $materia);
	
	$result = mysql_query ($query, $mysql_con);
	
	while (($object = mysql_fetch_object ($result))) {
		if (isset ($calificaciones [$object->Alumno])) {
			$query_cals = sprintf ("UPDATE Calificaciones SET Valor = %s WHERE Alumno = '%s' AND Nrc = '%s' AND Tipo = '%s'; ", $calificaciones[$object->Alumno], $object->Alumno, $object->Nrc, $evaluacion);
			
			/* Agregar a la lista de nrc a actualizar */
			$lista_nrc[] = $object->Nrc;
			
			mysql_query ($query_cals, $mysql_con);
		}
	}
	
	/* Actualizar todos los promedios */
	$lista_nrc = array_unique ($lista_nrc);
	
	foreach ($lista_nrc as $nrc) {
		$query = sprintf ("SELECT AVG (Valor) AS AVG FROM Calificaciones WHERE Nrc = '%s' AND Tipo = '%s' AND Valor IS NOT NULL", $nrc, $evaluacion);
		
		$result = mysql_query ($query, $mysql_con);
		$promedio = mysql_fetch_object ($result);
		mysql_free_result ($result);
	
		if (is_null ($promedio->AVG)) {
			$query = sprintf ("DELETE FROM Promedios WHERE Tipo = '%s' AND Nrc = '%s'", $evaluacion, $nrc);
		} else {
			$query = sprintf ("INSERT INTO Promedios (Nrc, Tipo, Promedio) VALUES ('%s', '%s', %s) ON DUPLICATE KEY UPDATE Promedio = Values (Promedio)", $nrc, $evaluacion, $promedio->AVG);
		}
	
		mysql_query ($query, $mysql_con);
	}
	
	agrega_mensaje (0, "Calificaciones subidas exitosamente");
	
	mysql_close ($mysql_con);
?>
