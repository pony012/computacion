<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	/* Luego verificar si tiene el permiso de gestionar usuarios */
	if (!has_permiso ('aed_usuarios')) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		agrega_mensaje (3, "Privilegios insuficientes");
		exit;
	}
	
	if (!isset ($_GET['t'])) {
		header ("Location: usuarios.php");
		exit;
	}
	$tipo = $_GET['t'];
	
	if ($tipo != 'u' && $tipo != 'm') {
		header ("Location: usuarios.php");
		exit;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<link rel="stylesheet" type="text/css" href="../css/theme.css" />
	<title><?php echo $cfg['nombre']; ?></title>
	<script language="javascript" src="../scripts/md5.js" type="text/javascript"></script>
	<script language="javascript" type="text/javascript">
		// <![CDATA[
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
		// ]]>
	</script>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes ();

		if ($tipo == 'm') {
			echo "<h1>Nuevo maestro</h1>";
		} else if ($tipo == 'u') {
			echo "<h1>Nuevo usuario</h1>";
		}
	?>
	<form action="post_nuevo_usuario.php" method="post" onsubmit="return valida_form()">
		<?php printf ("<input name=\"tipo\" id=\"tipo\" type=\"hidden\" value=\"%s\" />", $tipo); ?>
		<table border="0">
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
