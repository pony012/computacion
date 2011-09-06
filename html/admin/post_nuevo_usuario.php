<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	/* Luego verificar si tiene el permiso de gestionar usuarios */
	if (!isset ($_SESSION['permisos']['aed_usuarios']) || $_SESSION['permisos']['aed_usuarios'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	
	/* Ahora, tomar todos los campos y validarlos
	 * Campos recibidos por POST:
	 * codigo: El código del maestro/usuario
	 * nombre: El nombre
	 * correo: Correo electronico
	 * md5: La contraseña cifrada
	 */
	
	/* Sanitizado de variables */
	filter_input (INPUT_POST, 'user', FILTER_SANITIZE_NUMBER_INT);
	filter_input (INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
	
	require_once '../mysql-con.php';
	
	/* Voy a checar que el codigo no existe */
	$query = sprintf ("SELECT m.Codigo FROM Maestros AS m WHERE m.Codigo = '%s'", mysql_real_escape_string ($_POST['codigo']));
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) > 1) {
		/* TODO: Error usuario ya existente:
		 * Debería redirigir a usuarios.php
		 * junto con un mensaje de error en usuarios.php
		 */
		 mysql_free_result ($result);
		 mysql_close ($mysql_con);
		 exit ();
	}
	
	$query = sprintf ("INSERT INTO Maestros (Codigo, Nombre, Correo, Flag) VALUES ('%s', '%s', '%s', '0')", mysql_real_escape_string ($_POST['codigo']), mysql_real_escape_string ($_POST['nombre']), mysql_real_escape_string ($_POST['correo']));
	
	$result = mysql_query ($query, $mysql_con);
	mysql_free_result ($result);
	
	/* Falta insertar los permisos */
	
?>
