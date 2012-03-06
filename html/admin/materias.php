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
	<h1>Materias</h1>
	<?php
		database_connect ();
		
		/* Recuperar la cantidad total de filas */
		$result = mysql_query ("SELECT COUNT(*) AS TOTAL FROM Materias", $mysql_con);
		$row = mysql_fetch_object ($result);
		$total = $row->TOTAL;
		mysql_free_result ($result);
		
		$offset = (isset ($_GET['off'])) ? $_GET['off'] : 0; /* FIXME: usar intval */
		settype ($offset, "integer");
		$cant = 40;
		$show = $cant;
		
		if ($offset >= $total) $offset = $total - $cant;
		if ($offset < 0) $offset = 0;
		if (($offset + $cant) >= $total) $show = $total - $offset;
		
		printf ("<p>Mostrando materias del %s al %s</p>", ($offset + 1), ($offset + $cant));
		
		echo "<table border=\"1\">";
		
		/* Mostrar la cabecera */
		echo "<thead><tr><th>Clave</th><th>Descripción</th><th>Acciones</th></tr></thead>\n<tbody>";
		
		/* Empezar la consulta mysql */
		$query = sprintf ("SELECT * FROM Materias LIMIT %s, %s", $offset, $show);
		
		$result = mysql_query ($query, $mysql_con);
		
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			/* El nrc */
			printf ("<td><a href=\"ver_materia.php?clave=%s\">%s</a></td><td>%s</td>", $object->Clave, $object->Clave, $object->Descripcion);
			
			echo "<td>";
			
			if (has_permiso ('crear_materias')) {
				printf ("<a href=\"editar_materia.php?clave=%s\"><img class=\"icon\" src=\"../img/properties.png\" alt=\"editar\" /></a>", $object->Clave);
				printf ("<a href=\"eliminar_materia.php?clave=%s\"\n", $object->Clave);
				printf (" onclick=\"return confirmarDrop(this, '¿Realmente desea eliminar la materia %s?')\">", $object->Clave);
				echo "<img class=\"icon\" src=\"../img/remove.png\" alt=\"eliminar\" /></a>";
			}
			echo "</td></tr>\n";
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
	?>
	<?php	
		if (has_permiso ('crear_materias')) {
			echo "<ul>";
			echo "<li><a href=\"nueva_materia.php\">Agregar una nueva materia</a></li>\n";
			echo "</ul>";
		}
	?>
</body>
</html>
