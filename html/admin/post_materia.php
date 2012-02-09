<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	if (!isset ($_SESSION['permisos']['crear_materias']) || $_SESSION['permisos']['crear_materias'] != 1) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
	
	header ("Location: materias.php");
	
	if (!isset ($_POST['modo']) || ($_POST['modo'] != 'nuevo' && $_POST['modo'] != 'editar')) {
		agrega_mensaje (3, "Ha ocurrido un error desconocido");
		exit;
	}
	
	/* Validar la clave la materia */
	if (!preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_POST['clave'])) {
		agrega_mensaje (3, "Materia incorrecta");
		exit;
	}
	
	$_POST['clave'] = strtoupper ($_POST['clave']);
	
	if (!isset ($_POST['grupo'])) {
		agrega_mensaje (3, "Ha ocurrido un error procesando los datos");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	/* Recuperar las formas de evaluación */
	$todas = array ();
	$result = mysql_query ("SELECT Id, Grupo FROM Evaluaciones", $mysql_con);
	while (($object = mysql_fetch_object ($result))) $todas[$object->Id] = $object->Grupo;
	mysql_free_result ($result);
	
	$nuevas = array ();
	/* Recorrer cada grupo; y dentro de cada grupo, recorrer su formas de evaluación
	 * Si no existen o no están el grupo correcto salir
	 * Si la suma por cada grupo es diferente de 100 salir */
	foreach ($_POST['grupo'] as $id_grupo) {
		if (!isset ($_POST['eval_' . $id_grupo]) || !isset ($_POST['p_' . $id_grupo]) || count ($_POST['eval_' . $id_grupo]) != count ($_POST['p_' . $id_grupo])) {
			agrega_mensaje (3, "Error desconocido");
			exit;
		}
		
		$suma = 0;
		
		foreach ($_POST['eval_' . $id_grupo] as $index => $eval) {
			if (!isset ($todas[$eval]) || $todas[$eval] != $id_grupo) {
				/* No existe la evaluación, o no pertenece a este grupo */
				agrega_mensaje (3, "Error desconocido");
				exit;
			}
			$porcentaje = $_POST['p_' . $id_grupo][$index];
			settype ($porcentaje, 'integer');
			
			if ($porcentaje <= 0) {
				agrega_mensaje (3, "Error desconocido");
				exit;
			}
			$suma = $suma + $porcentaje;
			$nuevas[$eval] = $porcentaje;
			
			unset ($todas[$eval]); /* La quito de "todas" para que si se repite marque error */
		}
		
		if ($suma != 100) {
			agrega_mensaje (3, "Error al procesador los datos");
			exit;
		}
	}
	
	if ($_POST['modo'] == 'nuevo') {
		/* INSERT INTO `computacion`.`Materias` (`Clave`, `Descripcion`) VALUES ('as123', 'sadfgh'); */
		$query = sprintf ("INSERT INTO Materias (Clave, Descripcion) VALUES ('%s', '%s');", $_POST['clave'], mysql_real_escape_string ($_POST['descripcion']));
		
		$result = mysql_query ($query, $mysql_con);
	
		if (!$result) {
			agrega_mensaje (3, "Error desconocido");
			exit;
		}
		
		/* Insertar las formas de evaluación */
		$query = "INSERT INTO Porcentajes (Clave, Tipo, Ponderacion) VALUES ";
		
		foreach ($nuevas as $key => $porcentaje) {
			$query = $query . sprintf ("('%s', '%s', '%s'),", $_POST['clave'], $key, $porcentaje);
		}
		
		$query = substr_replace ($query, ";", -1);
		mysql_query ($query, $mysql_con);
		
		agrega_mensaje (0, sprintf ("La materia %s fué creada", $_POST['clave']));
	} else if ($_POST['modo'] == 'editar') {
		$query = sprintf ("UPDATE Materias SET Descripcion='%s' WHERE Clave='%s'", mysql_real_escape_string ($_POST['descripcion']), $_POST['clave']);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (!$result) {
			agrega_mensaje (3, "Error desconocido");
			exit;
		}
		
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
		
		/* Borrar todas las calificaciones de esa materia */
		$query = sprintf ("DELETE FROM C USING Calificaciones AS C INNER JOIN Secciones AS S ON C.Nrc = S.Nrc WHERE S.Materia='%s'", $_POST['clave']);
		
		mysql_query ($query);
		
		/* Re-ingresar las calificaciones */
		$query = sprintf ("SELECT Alumno, Nrc FROM Grupos AS G INNER JOIN Secciones AS S ON G.Nrc = S.Nrc WHERE S.Materia = '%s'", $_POST['clave']);
		
		$result = mysql_query ($query);
		
		while (($object = mysql_fetch_object ($result))) {
			$query_cal = "INSERT DELAYED INTO Calificaciones (Alumno, Nrc, Tipo, Valor) VALUES ";
			
			foreach ($nuevas as $key => $porcentaje) {
				$query_cal = $query_cal . sprintf (" ('%s', '%s', '%s', NULL),", $object->Alumno, $object->Nrc, $key);
			}
			
			$query_cal = substr_replace ($query_cal, ";", -1);
			mysql_query ($query_cal, $mysql_con);
		}
		
		agrega_mensaje (0, sprintf ("La materia %s fué actualizada", $_POST['clave']));
	}
	
	mysql_close ($mysql_con);
	exit;
?>
