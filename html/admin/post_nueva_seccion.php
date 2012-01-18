<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	/* Luego verificar si tiene el permiso de crear grupos */
	if (!isset ($_SESSION['permisos']['crear_grupos']) || $_SESSION['permisos']['crear_grupos'] != 1) {
		/* Privilegios insuficientes */
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
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 3;
		$_SESSION['m_klass'] = 's_wrong';
		exit;
	}
	
	/* La seccion debe ser introducida en mayúsculas */
	$_POST['seccion'] = strtoupper ($_POST['seccion']);
	
	/* Validar la seccion */
	if (!isset ($_POST['seccion']) || !preg_match ("/^([Dd])([0-9]){2}$/", $_POST['seccion'])) {
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 3;
		$_SESSION['m_klass'] = 's_wrong';
		exit;
	}
	
	if (!isset ($_POST['materia']) || !preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_POST['materia'])) {
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 3;
		$_SESSION['m_klass'] = 's_materia';
		exit;
	}
	
	/* Validar el maestro */
	if (!isset ($_POST['maestro']) || !preg_match ("/^([0-9]){1,7}$/", $_POST['maestro'])) {
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 3;
		$_SESSION['m_klass'] = 's_maestro';
		exit;
	}
	
	require_once "../mysql-con.php";
	
	/* Verificar que existe la materia */
	$query = sprintf ("SELECT Clave FROM Materias WHERE Clave='%s'", $_POST['materia']);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		/* La materia no existe */
		mysql_free_result ($result);
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 3;
		$_SESSION['m_klass'] = 's_materia';
		exit;
	}
	mysql_free_result ($result);
	
	/* Validar el maestro */
	$query = sprintf ("SELECT Codigo FROM Maestros WHERE Codigo='%s'", $_POST['maestro']);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		/* El maestro no existe */
		mysql_free_result ($result);
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 3;
		$_SESSION['m_klass'] = 's_maestro';
		exit;
	}
	
	mysql_free_result ($result);
	
	/* Ahora sí, hacer la inserción en la tabla */
	$query = sprintf ("INSERT INTO Secciones (Nrc, Materia, Maestro, Seccion) VALUES ('%s', '%s', '%s', '%s')", $_POST['nrc'], $_POST['materia'], $_POST['maestro'], $_POST['seccion']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		/* Error al insertar la materia */
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 1;
		$_SESSION['m_klass'] = 'unknown';
	} else {
		$_SESSION['mensaje'] = 1;
		$_SESSION['m_tipo'] = 0;
		$_SESSION['m_klass'] = 's_ok';
	}
	
	mysql_close ($mysql_con);
	exit;
?>
