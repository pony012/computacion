<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
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
	<h1>Acciones para el sistema</h1>
	<?php
		echo "<ul>";
		
		/* Si tiene el permiso de gestionar usuarios, mostrar el enlace */
		if ($_SESSION['permisos']['aed_usuarios'] == 1) {
			echo "<li><a href=\"gestionar_usuarios.php\">Gestionar usuarios</a></li>\n";
		}
		
		echo "</ul>";
	?>
</body>
</html>
