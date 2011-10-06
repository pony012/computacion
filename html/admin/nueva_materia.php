<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['crear_materias']) || $_SESSION['permisos']['crear_materias'] != 1) {
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
	<h1>Nueva materia</h1>
	<form action="post_nueva_materia.php" method="POST">
	<p>Clave de la materia: <input type="text" name="clave" id="clave" length="5" /></p>
	<p>Descripción: <input type="text" name="descripcion" id="descripcion" length="100" /></p>
	<p><input type="checkbox" value="1" checked="checked" name="depa1" id="depa1" />Tiene Departamental 1<br />
	&nbsp;&nbsp;&nbsp;Porcentaje: <input type="text" value="40" name="porcentaje_depa1" id="porcentaje_depa1" /></p>
	<p><input type="checkbox" value="1" checked="checked" name="depa2" id="depa2" />Tiene Departamental 2<br />
	&nbsp;&nbsp;&nbsp;Porcentaje: <input type="text" value="40" name="porcentaje_depa2" id="porcentaje_depa2" /></p>
	<p><input type="checkbox" value="1" checked="checked" name="puntos" id="puntos" />Otras Ponderaciones<br />
	&nbsp;&nbsp;&nbsp;Porcentaje: <input type="text" value="40" name="porcentaje_puntos" id="porcentaje_puntos" /></p>
	<p><input type="submit" value="Nueva materia" />
	</form>
</body>
</html>
