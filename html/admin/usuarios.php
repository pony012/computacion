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
	<h1>Maestros:</h1>
	<?php
		require_once "../mysql-con.php";
		
		$offset = (isset ($_GET['off'])) ? $_GET['off'] : 0;
		settype ($offset, "integer");
		$cant = 10;
		
		echo "<p>Mostrando registros del ". $offset ." al ". ($offset + $cant) . "</p>";
		
		echo "<table border=\"1\">";
		
		/* Mostrar la cabecera */
		echo "<thead><tr><th>Código</th><th>Nombre</th><th>Activo</th><th>Editar</th></tr></thead>\n";
		
		/* Empezar la consulta mysql */
		$query = "SELECT m.codigo, m.nombre, s.activo FROM Maestros AS m INNER JOIN Sesiones_Maestros AS s ON m.Codigo = s.Codigo LIMIT ". $offset . ",". $cant;
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr><td>" . $object->codigo . "</td><td>" . $object->nombre;
			if ($object->activo == 1) {
				echo "</td><td><img src=\"../img/day.png\" /></td></tr>\n";
			} else {
				echo "</td><td><img src=\"../img/night.png\" /></td></tr>\n";
			}
		}
		
		echo "</tbody>";
		echo "</table>\n";
		
		echo "<p>";
		/* Mostrar las flechas de dezplamiento */
		echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?off=0\"><img src=\"../img/first.png\" /></a>\n";
		
		if ($offset > 1) {
			$prev = $offset - $cant;
			if ($prev < 0) $prev = 0;
			echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?off=".$prev."\"><img src=\"../img/prev.png\" /></a>\n";
		}
		
		/* FIXME: No mostrar si sobrepasa la cantidad de filas */
		$next = $offset + $cant;
		echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?off=".$next."\"><img src=\"../img/next.png\" /></a>\n";
		/* FIXME: Mostrar el botón de último */
		echo "</p>\n";
	?>
	<ul>
	<li><a href="nuevo_usuario.php?t=u">Agregar un nuevo usuario</a></li>
	<li><a href="nuevo_usuario.php?t=m">Agregar nuevo maestro</a></li>
	</ul>
</body>
</html>
