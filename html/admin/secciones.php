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
	<link rel="stylesheet" type="text/css" href="../css/theme.css" />
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
</head>
<body>
	<h1>Grupos</h1>
	<?php
		require_once "../mysql-con.php";
		
		$offset = (isset ($_GET['off'])) ? $_GET['off'] : 0;
		settype ($offset, "integer");
		$cant = 20;
		
		echo "<p>Mostrando los grupos del ". $offset ." al ". ($offset + $cant) . "</p>";
		
		echo "<table border=\"1\">";
		
		/* Mostrar la cabecera */
		echo "<thead><tr><th>NRC</th><th>Clave</th><th>Materia</th><th>Seccion</th><th>Maestro</th>";
		
		if ($_SESSION['permisos']['crear_grupos'] == 1) {
			echo "<th colspan=\"2\">Acción</th>";
		}
		
		echo "</tr></thead>\n";
		
		/* Empezar la consulta mysql */
		if ($_SESSION['permisos']['grupos_globales'] == 1) {
			$query = "SELECT sec.Nrc, sec.Materia, mat.Descripcion, sec.Seccion, sec.Maestro, m.Nombre, m.Apellido FROM Secciones AS sec INNER JOIN Materias AS mat ON sec.Materia = mat.Clave INNER JOIN Maestros AS m ON sec.Maestro = m.Codigo LIMIT ". $offset . ",". $cant;
		} else {
			$query = sprintf ("SELECT sec.Nrc, sec.Materia, mat.Descripcion, sec.Seccion, sec.Maestro, m.Nombre FROM Secciones AS sec INNER JOIN Materias AS mat ON sec.Materia = mat.Clave INNER JOIN Maestros AS m ON sec.Maestro = m.Codigo WHERE sec.Maestro='%s' LIMIT %s,%s", $_SESSION['codigo'], $offset, $cant);
		}
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			/* El nrc */
			echo "<td>".$object->Nrc."</td>";
			echo "<td>".$object->Materia."</td>";
			echo "<td>".$object->Descripcion."</td>";
			echo "<td>".$object->Seccion."</td>";
			echo "<td>".$object->Apellido." ".$object->Nombre."</td>";
			if ($_SESSION['permisos']['crear_grupos'] == 1) {
				echo "<td><a href=\"editar_seccion.php?nrc=" . $object->Nrc . "\"><img class=\"icon\" src=\"../img/properties.png\" /></a></td>\n";
				echo "<td><a href=\"post_eliminar_seccion.php?nrc=" . $object->Nrc . "\"";
				echo " onclick=\"return confirmarDrop(this, '¿Realmente desea eliminar el NRC ".$object->Nrc."?'\">";
				echo "<img class=\"icon\" src=\"../img/remove.png\" /></a></td>\n";
			}
			echo "</tr>\n";
		}
		
		echo "</tbody>";
		echo "</table>\n";
		
		echo "<p>";
		/* Mostrar las flechas de dezplamiento */
		echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?off=0\"><img class=\"icon\" src=\"../img/first.png\" /></a>\n";
		
		if ($offset > 1) {
			$prev = $offset - $cant;
			if ($prev < 0) $prev = 0;
			echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?off=".$prev."\"><img class=\"icon\" src=\"../img/prev.png\" /></a>\n";
		}
		
		/* FIXME: No mostrar si sobrepasa la cantidad de filas */
		$next = $offset + $cant;
		echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?off=".$next."\"><img class=\"icon\" src=\"../img/next.png\" /></a>\n";
		/* FIXME: Mostrar el botón de último */
		echo "</p>\n";
	?>
	<?php
		echo "<ul>";
		if ($_SESSION['permisos']['crear_grupos'] == 1) {
			echo "<li><a href=\"nueva_seccion.php\">Agregar una nueva seccion</a></li>\n";
		}
		echo "</ul>";
	?>
</body>
</html>
