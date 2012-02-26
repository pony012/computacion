<?php
	require_once 'session_maestro.php';
	
	/* Variables que recibimos por $POST:
	 # user -> nombre de usuario
	 # md5 -> la contraseña
	 */
	
	header ("Location: login.php");
	
	if (!isset ($_SESSION['intentos_fallidos'])) $_SESSION['intentos_fallidos'] = 0;
	
	/* Verificar que haya datos POST */
	if (!isset ($_POST['user']) || !isset ($_POST['md5'])) {
		$_SESSION['intentos_fallidos']++;
		exit;
	}
	
	require_once 'mensajes.php';
	
	if ($_SESSION['intentos_fallidos'] > 4) {
		/* Hay que verificar el recaptcha primero */
		/* "recaptcha_challenge_field" or "recaptcha_response_field" */
		if (!isset ($_POST['recaptcha_challenge_field']) || !isset ($_POST['recaptcha_response_field'])) {
			agrega_mensaje (3, "Recaptcha vacio");
			exit;
		}
		
		require_once '../scripts/recaptchalib.php';
		
		$private_key = "";
		
		$resp = recaptcha_check_answer ($private_key, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
		
		if (!$resp->is_valid) {
			agrega_mensaje (3, "Recaptcha incorrecto");
			exit;
		}
	}
	
	/* Sanitizado de variables */
	$id_usuario = strval (intval ($_POST['user']));
	$contrasena = $_POST['md5'];
	
	if ($id_usuario <= 0 || !preg_match ("/^([A-fa-f0-9]){32}$/", $contrasena)) { /* Datos inválidos */
		agrega_mensaje (3, "Error desconocido");
		$_SESSION['intentos_fallidos']++;
		exit;
	}
	
	database_connect ();
	
	$query = sprintf ("SELECT Codigo, Permisos, Activo FROM Sesiones_Maestros WHERE Codigo = '%s' AND Pass = '%s'", $id_usuario, $contrasena);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		mysql_free_result ($result);
		agrega_mensaje (3, "Usuario o contraseña inválidos");
		$_SESSION['intentos_fallidos']++;
		exit;
	} else {
		$usuario = mysql_fetch_object ($result);
		mysql_free_result ($result);
		
		if ($usuario->Activo == 0) {
			agrega_mensaje (1, "Su cuenta está desactivada, contacte al administrador del sistema");
			$_SESSION['intentos_fallidos']++;
			exit;
		}
		
		/* Crear una nueva sessión */
		new_session ($usuario);
		
		header ("Location: vistas.php");
	}
?>
