<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	/* Luego verificar si tiene el permiso de gestionar usuarios */
	if (!has_permiso ('aed_usuarios')) {
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
	
	/* Sanitizado de variables */
	/* Validar el maestro */
	if (!isset ($_POST['codigo']) || !preg_match ("/^([0-9]){1,7}$/", $_POST['codigo'])) {
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	
	if (!isset ($_POST['nombre']) || trim ($_POST['nombre']) == "" || !isset ($_POST['apellido'])) {
		agrega_mensaje (3, "Nombre incorrecto");
		exit;
	}
	
	if (!isset ($_POST['md5']) || !preg_match ("/^([A-Za-z0-9]){32}$/", $_POST['md5'])) {
		agrega_mensaje (3, "Datos incorrectos");
		exit;
	}
	
	$codigo = strval (intval ($_POST['codigo']));
	$contra = $_POST['md5'];
	$correo = filter_input (INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
	
	database_connect ();
	
	$correo = mysql_real_escape_string ($correo);
	$nombre = mysql_real_escape_string ($_POST['nombre']);
	$apellido = mysql_real_escape_string ($_POST['apellido']);
	
	/* Voy a checar que el codigo no existe */
	$query = sprintf ("SELECT m.Codigo FROM Maestros AS m WHERE m.Codigo = '%s'", $codigo);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) > 0) {
		/* TODO: Error usuario ya existente:
		 * Debería redirigir a usuarios.php
		 * junto con un mensaje de error en usuarios.php
		 */
		 mysql_free_result ($result);
		 agrega_mensaje (3, "El maestro ya existe");
		 exit;
	}
	
	mysql_free_result ($result);
	
	$query = sprintf ("INSERT INTO Maestros (Codigo, Nombre, Apellido, Correo, Flag) VALUES ('%s', '%s', '%s', '%s', 0)", $codigo, $nombre, $apellido, $correo);
	$result = mysql_query ($query, $mysql_con);
	
	if (!$result) {
		/* No se ha insertado ningún registro, esto es preocupante */
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	/* Falta insertar los permisos */
	/* TODO: Elegir los permisos acordes al tipo */
	$query = "INSERT INTO Permisos (aed_usuarios, crear_grupos, asignar_aplicadores) VALUES (0, 0, 0)";
	mysql_query ($query, $mysql_con);
	
	$ultimo_permiso = mysql_insert_id ($mysql_con);
	
	/* Crear la sesión */
	$query = sprintf ("INSERT INTO Sesiones_Maestros (Codigo, Pass, Permisos, Activo) VALUES ('%s', '%s', %s, 1)", $codigo, $contra, $ultimo_permiso);
	mysql_query ($query, $mysql_con);
	
	agrega_mensaje (0, "El maestro/usuario fué creado");
?>
