<?php
	require_once 'session_maestro.php';
	
	/* TODO: ¿Permitir autenticación por $_GET? */
	if (!isset ($_SESSION['auth_m']) || $_SESSION['auth_m'] != true) exit;
	if (!isset ($_GET['modo'])) exit;
	
	if ($_GET['modo'] == 'evals') {
		if (!isset ($_GET['materia']) || !preg_match ("/^([A-Za-z])([A-Za-z0-9]){2}([0-9]){2}$/", $_GET['materia'])) exit;
		if (isset ($_GET['exclusiva']) && $_GET['exclusiva'] == 1) $exclu = 1;
		else $exclu = 0;
		
		$mate = strtoupper ($_GET['materia']);
		
		database_connect ();
		$query = sprintf ("SELECT P.Tipo, E.Descripcion FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave='%s' AND E.Exclusiva=%s", $mate, $exclu);
		$result = mysql_query ($query, $mysql_con);
		
		$json = array ();
		
		while (($object = mysql_fetch_object ($result))) {
			$json[] = $object;
		}
		
		$json_string = json_encode ($json);
		
		printf ($json_string);
		exit;
	}
	
	if ($_GET['modo'] == 'grupos') {
		if (!isset ($_GET['materia']) || !isset ($_GET['bus']) || $_GET['bus'] == "") exit;
		if (!preg_match ("/^([A-Za-z])([A-Za-z0-9]){2}([0-9]){2}$/", $_GET['materia'])) exit;
		if (!has_permiso ('asignar_aplicadores')) exit;
		
		$mate = strtoupper ($_GET['materia']);
		
		database_connect ();
		$busqueda = mysql_real_escape_string ($_GET['bus']);
		/* SELECT G.Alumno, A.Nombre, A.Apellido FROM Grupos AS G INNER JOIN Secciones AS Sec ON G.Nrc = Sec.Nrc INNER JOIN Alumnos AS A ON G.Alumno = A.Codigo WHERE Sec.Materia = 'CC100' ORDER BY A.Apellido, A.Nombre */
		$query = sprintf ("SELECT G.Alumno, A.Nombre, A.Apellido FROM Grupos AS G INNER JOIN Secciones AS Sec ON G.Nrc = Sec.Nrc INNER JOIN Alumnos AS A ON G.Alumno = A.Codigo WHERE Sec.Materia = '%s' AND (A.Nombre LIKE '%%%s%%' OR G.Alumno LIKE '%%%s%%' OR A.Apellido LIKE '%%%s%%') ORDER BY A.Apellido, A.Nombre", $_GET['materia'], $busqueda, $busqueda, $busqueda);
		
		$result = mysql_query ($query, $mysql_con);
		
		$json = array ();
		
		while (($object = mysql_fetch_object ($result))) {
			$json[] = $object;
		}
		
		$json_string = json_encode ($json);
		
		printf ($json_string);
		exit;
	}
	
	if ($_GET['modo'] == 'count') {
		if (!isset ($_GET['tipo'])) exit;
		if (!has_permiso ('asignar_aplicadores')) exit;
		
		if ($_GET['tipo'] == 'alumnos') {
			if (!isset ($_GET['materia']) || !preg_match ("/^([A-Za-z])([A-Za-z0-9]){2}([0-9]){2}$/", $_GET['materia'])) exit;
			
			$mate = strtoupper ($_GET['materia']);
			
			database_connect ();
			
			/* SELECT COUNT (*) AS TOTAL FROM Grupos AS G INNER JOIN Secciones AS S ON G.Nrc = S.Nrc WHERE S.Materia = 'CC204' */
			$query = sprintf ("SELECT COUNT(*) AS TOTAL FROM Grupos AS G INNER JOIN Secciones AS S ON G.Nrc = S.Nrc WHERE S.Materia = '%s'", $mate);
			$result = mysql_query ($query, $mysql_con);
			
			$json_object = mysql_fetch_object ($result);
			
			mysql_free_result ($result);
			$json_string = json_encode ($json_object);
			
			printf ($json_string);
			exit;
		}
	}
?>
