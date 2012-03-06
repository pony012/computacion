<?php require_once 'session_maestro.php'; check_valid_session (); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<link rel="stylesheet" type="text/css" href="../css/theme.css" />
	<script language="javascript" src="../scripts/comun.js" type="text/javascript"></script>
	<title><?php echo $cfg['nombre']; ?></title>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes ();?>
	<h1>Carreras</h1>
	<?php
		require_once '../mysql-con.php';
		
		$result = mysql_query ("SELECT COUNT(*) AS TOTAL FROM Carreras", $mysql_con);
		$row = mysql_fetch_object ($result);
		$total = $row->TOTAL;
		mysql_free_result ($result);
		
		$offset = (isset ($_GET['off'])) ? $_GET['off'] : 0;
		settype ($offset, "integer");
		$cant = 20;
		$show = $cant;
		
		if ($offset >= $total) $offset = $total - $cant;
		if ($offset < 0) $offset = 0;
		if (($offset + $cant) >= $total) $show = $total - $offset;
		
		printf ("<p>Carreras, del %s al %s</p>", ($offset + 1), ($offset + $show));
		
		echo "<table border=\"1\">";
		
		/* Mostrar la cabecera */
		echo "<thead><tr><th>Clave</th><th>Descripción</th><th>Acciones</th></tr></thead>";
		
		$query = sprintf ("SELECT * FROM Carreras LIMIT %s, %s", $offset, $show);
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			
			if (has_permiso ('grupos_globales')) {
				printf ("<td><a href=\"ver_por_carrera.php?carrera=%s\">%s</a></td>", $object->Clave, $object->Clave);
			} else {
				printf ("<td>%s</td>", $object->Clave);
			}
			printf ("<td>%s</td>", $object->Descripcion);
			
			echo "<td>";
			if (has_permiso ('admin_carreras')) {
				printf ("<a href=\"editar_carrera.php?clave=%s\"><img class=\"icon\" src=\"../img/properties.png\" alt=\"editar\"/></a>", $object->Clave);
				printf ("<a href=\"eliminar_carrera.php?clave=%s\"\n", $object->Clave);
				printf (" onclick=\"return confirmarDrop(this, '¿Realmente desea eliminar la carrera %s?')\">", $object->Descripcion);
				echo "<img class=\"icon\" src=\"../img/remove.png\" alt=\"eliminar\"/></a>";
			}
			echo "</td></tr>";
		}
		
		mysql_free_result ($result);
		
		echo "</tbody></table>\n";
		
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
	?>
	<?php if (has_permiso ('admin_carreras')) {
		echo "<ul><li><a href=\"nueva_carrera.php\">Nueva carrera</a></li></ul>\n";
	} ?>
</body>
</html>
