<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	if (!isset ($_SESSION['permisos']['admin_carreras']) || $_SESSION['permisos']['admin_carreras'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		agrega_mensaje (3, "Privilegios insuficientes");
		exit;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<link rel="stylesheet" type="text/css" href="../css/theme.css" />
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
	<script language="javascript" type="text/javascript">
		function validar () {
			var clave = document.getElementById ("clave").value;
			
			/* Validaciones sobre la clave */
			if (!/^([A-Za-z]){3}$/.test(clave)) {
				/* Clave incorrecta */
				alert ("Clave incorrecta");
				return false;
			}
			
			return true;
		}
	</script>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes (); ?>
	<h1>Nueva carrera</h1>
	<form action="post_carrera.php" method="POST" onsubmit="return validar()">
	<input type="hidden" name="modo" value="nuevo" />
	<p>Clave de la carrera: <input type="text" name="clave" id="clave" maxlength="3" /></p>
	<p>Descripción de la carrera: <input type="text" name="descripcion" id="descripcion" length="100" /></p>
	<input type="submit" value="Agregar carrera" />
	</form>
</body>
</html>
