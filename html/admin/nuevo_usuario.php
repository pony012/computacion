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
	
	$tipo = $_GET['t'];
	if ($tipo != 'u' && $tipo != 'm') {
		header ("Location: vistas.php");
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
	<script language="javascript" src="../scripts/md5.js" type="text/javascript"></script>
	<script language="javascript" type="text/javascript">
		function valida_form () {
			var cod = document.getElementById("codigo").value;
			var pass1 = document.getElementById("pass1").value;
			var pass2 = document.getElementById("pass2").value;
			var nombre = document.getElementById("nombre").value;
			
			/* TODO: Verificar el código no sea repetido
			 * Posiblemente con jquery */
			if (cod == null || cod == "") return false;
			
			/* TODO: Informar que las contraseñas no coinciden */
			if (pass1 != pass2) return false;
			
			/* TODO: Informar que el usuario no puede estar vacio */
			if (nombre == null || nombre == "") return false;
			
			/* TODO: Mandar un mensaje al usuario que indique que
			 * hace falta una contraseña */
			if (pass1 == null || pass1 == "") return false;
			
			// En caso contrario, encriptar la contraseña
			var md5 = MD5 (pass1);
			document.getElementById("md5").value = md5;
			document.getElementById("pass1").value = "";
			document.getElementById("pass2").value = "";
			
			return true;
		}
	</script>
</head>
<body>
	<?php
		if ($tipo == 'm') {
			echo "<h1>Nuevo maestro</h1>";
		} else if ($tipo == 'u') {
			echo "<h1>Nuevo usuario</h1>";
		}
	?>
	<form action="post_nuevo_usuario.php" method="post" onsubmit="return valida_form()">
		<table border="0">
		<?php
			echo "<input name=\"tipo\" id=\"tipo\" type=\"hidden\" value=\"" . $tipo . "\" />";
		?>
		<tr><td>Código:</td>
		<td><input name="codigo" id="codigo" type="text" /></td></tr>
		<tr><td>Nombre:</td>
		<td><input name="nombre" id="nombre" type="text" /></td></tr>
		<tr><td>Apellido:</td>
		<td><input name="apellido" id="apellido" type="text" /></td></tr>
		<tr><td>Correo (opcional):</td>
		<td><input name="correo" id="correo" type="text" /></td></tr>
		<tr><td>Nip:</td>
		<td><input name="pass1" id="pass1" type="password" /></td></tr>
		<tr><td>Repite nip:</td>
		<td><input name="pass2" id="pass2" type="password" /></td></tr>
		</table>
		<input name="md5" id="md5" type="hidden" />
		<input type="submit" value="Agregar" />
	</form>
</body>
</html>