<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	/* Luego verificar si tiene el permiso de gestionar usuarios */
	if (!isset ($_SESSION['permisos']['aed_usuarios']) || $_SESSION['permisos']['aed_usuarios'] != 1) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
	
	/* Ahora, tomar todos los campos y validarlos
	 * Campos recibidos por POST:
	 * codigo: El código del maestro/usuario
	 * nombre: El nombre
	 * apellido: El apellido
	 * correo: Correo electronico
	 * md5: La contraseña cifrada
	 */
	 
	header ("Location: usuarios.php");
	
	/* Verificar que haya datos POST */
	if (!isset ($_POST['codigo']) ||
	    !isset ($_POST['nombre']) ||
	    !isset ($_POST['md5'])) {
		exit;
	}
	
	/* Sanitizado de variables */
	/* Validar el maestro */
	if (!isset ($_POST['codigo']) || !preg_match ("/^([0-9]){1,7}$/", $_POST['codigo'])) {
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	
	filter_input (INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
	
	require_once '../mysql-con.php';
	
	$_POST['correo'] = mysql_real_escape_string ($_POST['correo']);
	
	/* Voy a checar que el codigo no existe */
	$query = sprintf ("SELECT m.Codigo FROM Maestros AS m WHERE m.Codigo = '%s'", $_POST['codigo']);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) > 0) {
		/* TODO: Error usuario ya existente:
		 * Debería redirigir a usuarios.php
		 * junto con un mensaje de error en usuarios.php
		 */
		 mysql_free_result ($result);
		 mysql_close ($mysql_con);
		 agrega_mensaje (3, "El maestro ya existe");
		 exit;
	}
	
	mysql_free_result ($result);
	
	$query = sprintf ("INSERT INTO Maestros (Codigo, Nombre, Apellido, Correo, Flag) VALUES ('%s', '%s', '%s', '%s', 0)", $_POST['codigo'], mysql_real_escape_string ($_POST['nombre']), mysql_real_escape_string ($_POST['apellido']), mysql_real_escape_string ($_POST['correo']));
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		/* No se ha insertado ningún registro, esto es preocupante */
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	/* Falta insertar los permisos */
	/* TODO: Elegir los permisos acordes al tipo */
	$query = "INSERT INTO Permisos (aed_usuarios, crear_grupos, asignar_aplicadores) VALUES (0, 0, 0)";
	$result = mysql_query ($query, $mysql_con);
	
	$ultimo_permiso = mysql_insert_id ($mysql_con);
	
	/* Crear la sesión */
	$query = sprintf ("INSERT INTO Sesiones_Maestros (Codigo, Pass, Permisos, Activo) VALUES ('%s', '%s', %s, 1)", $_POST['codigo'], mysql_real_escape_string ($_POST['md5']), $ultimo_permiso);
	$result = mysql_query ($query, $mysql_con);
	
	mysql_close ($mysql_con);
	
	/* TODO: Enviar un mensaje de correcto */
	agrega_mensaje (0, "El maestro/usuario fué creado");
	exit;
?>
