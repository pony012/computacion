<?php require_once 'session_maestro.php'; check_valid_session (); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<link rel="stylesheet" type="text/css" href="../css/theme.css" />
	<title><?php echo $cfg['nombre']; ?></title>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes (); ?>
	<h1>Maestros:</h1>
	<?php
		database_connect ();
		
		/* Recuperar la cantidad total de filas */
		$result = mysql_query ("SELECT COUNT(*) AS TOTAL FROM Maestros", $mysql_con);
		$row = mysql_fetch_object ($result);
		$total = $row->TOTAL;
		mysql_free_result ($result);
		
		$offset = (isset ($_GET['off'])) ? $_GET['off'] : 0;
		settype ($offset, "integer");
		$cant = 30;
		$show = $cant;
		
		if ($offset >= $total) $offset = $total - $cant;
		if ($offset < 0) $offset = 0;
		if (($offset + $cant) >= $total) $show = $total - $offset;
		
		printf ("<p>Mostrando usuarios del %s al %s</p>", ($offset + 1), ($offset + $show));
		
		echo "<table border=\"1\">";
		
		/* Mostrar la cabecera */
		echo "<thead><tr><th>Código</th><th>Nombre</th>";
		if (has_permiso ('aed_usuarios')) {
			echo "<th>Activo</th><th>Editar</th>";
		}
		echo "</tr></thead>\n";
		
		/* Empezar la consulta mysql */
		$query = sprintf ("SELECT m.codigo, m.Apellido, m.nombre, s.activo FROM Maestros AS m INNER JOIN Sesiones_Maestros AS s ON m.Codigo = s.Codigo LIMIT %s, %s", $offset, $show);
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			printf ("<td><a href=\"ver_maestro.php?codigo=%s\">%s</a></td>", $object->codigo, $object->codigo);
			printf ("<td>%s %s</td>", $object->Apellido, $object->Nombre);
			if (has_permiso ('aed_usuarios')) {
				if ($object->activo == 1) {
					echo "<td><img src=\"../img/day.png\" alt=\"activo\" /></td>";
				} else {
					echo "<td><img src=\"../img/night.png\" alt=\"desactivado\" /></td>";
				}
			}
			echo "</tr>\n";
		}
		
		echo "</tbody>";
		echo "</table>\n";
		
		echo "<p>";
		
		$next = $offset + $show;
		$ultimo = $total - ($total % $cant);
		if ($next >= $total) $next = $ultimo;
		$prev = $offset - $cant;
		if ($prev < 0) $prev = 0;
		
		/* Mostrar las flechas de dezplamiento */
		$link = $_GET;
		if ($offset > 0) {
			printf ("<a href=\"%s?%s\"><img class=\"icon\" src=\"../img/first.png\" alt=\"primero\" /></a>\n", $_SERVER['SCRIPT_NAME'], htmlentities (http_build_query (Array ('off' => 0) + $link)));
			printf ("<a href=\"%s?%s\"><img class=\"icon\" src=\"../img/prev.png\" alt=\"anterior\"/></a>\n", $_SERVER['SCRIPT_NAME'], htmlentities (http_build_query (Array ('off' => $prev) + $link)));
		}
		if ($offset + $show < $total) { 
			printf ("<a href=\"%s?%s\"><img class=\"icon\" src=\"../img/next.png\" alt=\"siguiente\"/></a>\n", $_SERVER['SCRIPT_NAME'], htmlentities (http_build_query (Array ('off' => $next) + $link)));
			printf ("<a href=\"%s?%s\"><img class=\"icon\" src=\"../img/last.png\" alt=\"ultimo\" /></a>\n", $_SERVER['SCRIPT_NAME'], htmlentities (http_build_query (Array ('off' => $ultimo) + $link)));
		}
		
		echo "</p>\n";
		
		if (has_permiso ('aed_usuarios')) { ?>
	<ul>
	<li><a href="nuevo_usuario.php?t=u">Agregar un nuevo usuario</a></li>
	<li><a href="nuevo_usuario.php?t=m">Agregar nuevo maestro</a></li>
	</ul><?php } ?>
</body>
</html>
