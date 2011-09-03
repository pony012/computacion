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
</head>
<body>
	<?php
		$tipo = $_GET['t'];
		
		if ($tipo == 'm') {
			echo "<h1>Nuevo maestro</h1>";
		} else if ($tipo == 'u') {
			echo "<h1>Nuevo usuario</h1>";
		} else {
			echo "<h1>Tipo de usuario desconocido</h1>";
			echo "<p>No debería estar viendo este error.....</ br>Regresar al <a href=\"vistas.php\">menú</a></p>";
			exit ();
		}
	?>
	<form action="" method="post">
		<table border="0">
		<?php
			echo "<input name=\"tipo\" id=\"tipo\" type=\"hidden\" value=\"" . $tipo . "\" />";
		?>
		<tr><td>Código:</td>
		<td><input name="codigo" id="codigo" type="text" /></td></tr>
		<tr><td>Nombre:</td>
		<td><input name="nombre" id="nombre" type="text" /></td></tr>
		<tr><td>Correo: (opcional)</td>
		<td><input name="correo" id="correo" type="text" /></td></tr>
		</table>
	</form>
</body>
</html>
