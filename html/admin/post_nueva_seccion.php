<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	/* Luego verificar si tiene el permiso de crear grupos */
	if (!has_permiso ('crear_grupos')) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
	
	/* Campos que recibo por POST
	 * nrc: El nrc de la materia, no debe estar duplicado.
	 * materia: La clave de la materia
	 * seccion: La sección de este grupo
	 * maestro: El maestro
	 */
	
	/* Saneado rápido
	foreach ($_POST as $key => $valor) {
		$_POST[ $key ] = mysql_real_escape_string ($valor);
	}*/
	
	header ("Location: secciones.php");
	
	/* Validar primero el NRC */
	if (!isset ($_POST['nrc']) || !preg_match ("/^([0-9]){1,5}$/", $_POST['nrc'])) {
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	
	/* Validar la seccion */
	if (!isset ($_POST['seccion']) || !preg_match ("/^([Dd])([0-9]){2}$/", $_POST['seccion'])) {
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	
	if (!isset ($_POST['materia']) || !preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_POST['materia'])) {
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	
	/* Validar el maestro */
	if (!isset ($_POST['maestro']) || !preg_match ("/^([0-9]){1,7}$/", $_POST['maestro'])) {
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	
	/* La seccion debe ser introducida en mayúsculas */
	$seccion = strtoupper ($_POST['seccion']);
	$nrc = $_POST['nrc'];
	$clave_materia = strtoupper ($_POST['materia']);
	$maestro = $_POST['maestro'];
	
	database_connect ();
	
	/* Verificar que existe la materia */
	$query = sprintf ("SELECT Clave FROM Materias WHERE Clave = '%s'", $clave_materia);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		/* La materia no existe */
		mysql_free_result ($result);
		agrega_mensaje (3, "Materia inexistente");
		exit;
	}
	mysql_free_result ($result);
	
	/* Validar el maestro */
	$query = sprintf ("SELECT Codigo FROM Maestros WHERE Codigo = '%s'", $maestro);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		/* El maestro no existe */
		mysql_free_result ($result);
		agrega_mensaje (3, "Maestro desconocido");
		exit;
	}
	
	mysql_free_result ($result);
	
	/* Ver si existe una sección igual o nrc igual */
	$query = sprintf ("SELECT Seccion, Materia FROM Secciones WHERE Nrc = '%s'", $nrc);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) != 0) {
		/* Nrc duplicado */
		$object = mysql_fetch_object ($result);
		mysql_free_result ($result);
		agrega_mensaje (3, sprintf ("El nrc %s está duplicado.<br />Pertenece a la materia %s y sección", $nrc, $object->Materia, $object->Seccion));
		exit;
	}
	mysql_free_result ($result);
	
	$query = sprintf ("SELECT Nrc FROM Secciones WHERE Materia = '%s' AND Seccion = '%s'", $clave_materia, $seccion);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) != 0) {
		/* Materia y Sección duplicadas */
		$object = mysql_fetch_object ($result);
		mysql_free_result ($result);
		agrega_mensaje (3, sprintf ("Sección duplicada<br />La materia %s ya existe en la sección %s, bajo el nrc %s", $clave_materia, $seccion, $object->Nrc));
		exit;
	}
	mysql_free_result ($result);
	
	
	/* Ahora sí, hacer la inserción en la tabla */
	$query = sprintf ("INSERT INTO Secciones (Nrc, Materia, Maestro, Seccion) VALUES ('%s', '%s', '%s', '%s')", $nrc, $clave_materia, $maestro, $seccion);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		/* Error al insertar la materia */
		agrega_mensaje (3, "Error desconocido");
	} else {
		agrega_mensaje (0, "Materia creada correctamente");
	}
?>
