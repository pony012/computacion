<?php
	session_start ();
	
	/* Variables que recibimos por $POST:
	 # user -> nombre de usuario
	 # md5 -> la contraseña
	 */
	
	header ("Location: login.php");
	
	/* Verificar que haya datos POST */
	if (!isset ($_POST['user']) || !isset ($_POST['md5'])) {
		exit;
	}
	
	require_once 'mensajes.php';
	
	/* Sanitizado de variables */
	$id_usuario = strval (intval ($_POST['user']));
	$contrasena = $_POST['md5'];
	
	if ($id_usuario <= 0 || !preg_match ("/^([A-fa-f0-9]){32}$/", $contrasena)) { /* Datos inválidos */
		agrega_mensaje (3, "Error desconocido");
		/* TODO: Sumar los intentos fallidos */
		exit;
	}
	
	require_once "../mysql-con.php";
	
	$query = sprintf ("SELECT Codigo, Permisos, Activo FROM Sesiones_Maestros WHERE Codigo = '%s' AND Pass = '%s'", $id_usuario, $contrasena);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		mysql_free_result ($result);
		agrega_mensaje (3, "Usuario o contraseña inválidos");
		exit;
	} else {
		$usuario = mysql_fetch_object ($result);
		mysql_free_result ($result);
		
		if ($usuario->Activo == 0) {
			agrega_mensaje (1, "Su cuenta está desactivada, contacte al administrador del sistema");
			/* TODO: Sumar los intentos fallidos */
			exit;
		}
		
		/* Rellenar los datos de la sesion */
		$_SESSION['auth'] = 1;
		$_SESSION['codigo'] = $usuario->Codigo;
		
		/* TODO: Enviar el nombre y el correo a COOKIES */
		
		/* Ahora recuperar la tabla de permisos */
		$query = sprintf ("SELECT * FROM Permisos WHERE Id = '%s'", $usuario->Permisos);
		
		$result = mysql_query ($query, $mysql_con);
		$permisos = mysql_fetch_assoc ($result);
		
		$_SESSION['permisos'] = $permisos;
		
		mysql_free_result ($result);
		
		header ("Location: vistas.php");
	}
	
	mysql_close ($mysql_con);
?>
