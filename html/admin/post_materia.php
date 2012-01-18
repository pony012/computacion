<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['crear_materias']) || $_SESSION['permisos']['crear_materias'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	header ("Location: materias.php");
	
	if (!isset ($_POST['modo']) || ($_POST['modo'] != 'nuevo' && $_POST['modo'] != 'editar')) {
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 3;
		$_SESSION['m_klass'] = 'unknown';
		exit;
	}
	
	if (count ($_POST['evals']) != count ($_POST['porcentajes']) || count ($_POST['evals']) <= 0) {
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 3;
		$_SESSION['m_klass'] = 'm_wrong';
		exit;
	}
	
	require_once '../mysql-con.php';
	
	/* Recuperar las formas de evaluación */
	$result = mysql_query ("SELECT Id FROM Evaluaciones", $mysql_con);
	while (($row = mysql_fetch_row ($result)) != FALSE) $todas[$row[0]] = 1;
	mysql_free_result ($result);
	
	$nuevas = array ();
	for ($g = 0; $g < count ($_POST['evals']); $g++) {
		$nuevas [((int) $_POST['evals'][$g])] = (int) $_POST['porcentajes'][$g];
	}
	
	$suma = 0;
	foreach ($nuevas as $key => $porcentaje) {
		if ($porcentaje <= 0 || !isset ($todas [$key])) {
			$_SESSION['mensaje'] = 1;
			$_SESSION['m_tipo'] = 3;
			$_SESSION['m_klass'] = 'unknown';
			exit;
		}
		
		$suma += $porcentaje;
	}
	
	if ($suma != 100) {
		/* Suma incorrecta */
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 3;
		$_SESSION['m_klass'] = 'm_wrong';
		exit;
	}
	
	if (isset ($_POST['tiene_extra']) && $_POST['tiene_extra'] == 1) $nuevas[0] = 100;
	
	/* Validar la clave la materia */
	if (!preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_POST['clave'])) {
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 3;
		$_SESSION['m_klass'] = 'm_clave';
		exit;
	}
	
	$_POST['clave'] = strtoupper ($_POST['clave']);
	
	if ($_POST['modo'] == 'nuevo') {
		/* INSERT INTO `computacion`.`Materias` (`Clave`, `Descripcion`) VALUES ('as123', 'sadfgh'); */
		$query = sprintf ("INSERT INTO Materias (Clave, Descripcion) VALUES ('%s', '%s');", $_POST['clave'], mysql_real_escape_string ($_POST['descripcion']));
		
		$result = mysql_query ($query, $mysql_con);
	
		if (!$result) {
			$_SESSION['mensaje'] = 1;
			$_SESSION['m_tipo'] = 3;
			$_SESSION['m_klass'] = 'unknown';
			exit;
		}
		
		/* Insertar las formas de evaluación */
		$query = "INSERT INTO Porcentajes (Clave, Tipo, Ponderacion) VALUES ";
		
		foreach ($nuevas as $key => $porcentaje) {
			$query = $query . sprintf ("('%s', '%s', '%s'),", $_POST['clave'], $key, $porcentaje);
		}
		
		$query = substr_replace ($query, ";", -1);
		mysql_query ($query, $mysql_con);
		
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 0;
		$_SESSION['m_klass'] = 'm_n_ok';
	} else if ($_POST['modo'] == 'editar') {
		$query = sprintf ("UPDATE Materias SET Descripcion='%s' WHERE Clave='%s'", mysql_real_escape_string ($_POST['descripcion']), $_POST['clave']);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (!$result) {
			$_SESSION['mensaje'] = 1;
			$_SESSION['m_tipo'] = 3;
			$_SESSION['m_klass'] = 'unknown';
			exit;
		}
		
		/* Antes de limpiarlos, sacar las actuales forma de evaluación */
		$query = sprintf ("SELECT Tipo FROM Porcentajes WHERE Clave='%s'", $_POST['clave']);
		
		$result = mysql_query ($query, $mysql_con);
		$old = array ();
		while (($object = mysql_fetch_object ($result))) $old [$object->Tipo] = 1;
		mysql_free_result ($result);
		
		/* Limpiar los porcentajes anteriores */
		$query = sprintf ("DELETE FROM Porcentajes WHERE Clave='%s'", $_POST['clave']);
		mysql_query ($query, $mysql_con);
		
		/* Insertar las nuevas formas de evaluación */
		$query = "INSERT INTO Porcentajes (Clave, Tipo, Ponderacion) VALUES ";
		
		foreach ($nuevas as $key => $porcentaje) {
			$query = $query . sprintf ("('%s', '%s', '%s'),", $_POST['clave'], $key, $porcentaje);
		}
		
		$query = substr_replace ($query, ";", -1);
		mysql_query ($query, $mysql_con);
		
		/* Luego, corregir la tabla de calificaciones */
		foreach ($old as $key => $value) {
			if (!isset ($nuevas[$key])) {
				/* Eliminar este de la tabla de calificaciones
				 * DELETE FROM C USING Calificaciones AS C INNER JOIN Secciones AS S ON C.Nrc = S.Nrc WHERE S.Materia='CC204' AND C.Tipo='7' */
				$query = sprintf ("DELETE FROM C USING Calificaciones AS C INNER JOIN Secciones AS S ON C.Nrc = S.Nrc WHERE S.Materia='%s' AND C.Tipo='%s'", $_POST['clave'], $key);
				mysql_query ($query, $mysql_con);
			} else {
				unset ($nuevas [$key]);
			}
		}
		
		/* Las nuevas calificaciones */
		foreach ($nuevas as $key => $value) {
			$query = sprintf ("SELECT G.* FROM Grupos AS G INNER JOIN Secciones AS Sec ON G.Nrc = Sec.Nrc WHERE Sec.Materia = '%s'", $_POST['clave']);
			$result = mysql_query ($query, $mysql_con);
			
			$query_cal = "INSERT INTO Calificaciones (Alumno, Nrc, Tipo, Valor) VALUES ";
			while (($object = mysql_fetch_object ($result))) {
				$query_cal = $query_cal . sprintf ("('%s', '%s', '%s', NULL),", $object->Alumno, $object->Nrc, $key);
			}
			mysql_free_result ($result);
			
			$query_cal = substr_replace ($query_cal, ";", -1);
			
			mysql_query ($query_cal, $mysql_con);
		}
		
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 0;
		$_SESSION['m_klass'] = 'm_a_ok';
	}
	
	mysql_close ($mysql_con);
	exit;
?>
