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
	<script language="javascript" src="../scripts/comun.js" type="text/javascript"></script>
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes (); ?>
	<h1>Grupos</h1>
	<?php
		require_once "../mysql-con.php";
		
		/* Recuperar la cantidad total de filas */
		$result = mysql_query ("SELECT COUNT(*) AS TOTAL FROM Secciones", $mysql_con);
		
		$row = mysql_fetch_object ($result);
		$total = $row->TOTAL;
		mysql_free_result ($result);
		
		$offset = (isset ($_GET['off'])) ? $_GET['off'] : 0;
		settype ($offset, "integer");
		$cant = 50;
		$show = $cant;
		
		if ($offset >= $total) $offset = $total - $cant;
		if ($offset < 0) $offset = 0;
		if (($offset + $cant) >= $total) $show = $total - $offset;
		
		printf ("<p>Mostrando los grupos del %s al %s</p>", ($offset + 1), ($offset + $show));
		
		echo "<table border=\"1\">";
		
		/* Mostrar la cabecera */
		echo "<thead><tr><th>NRC</th><th>Clave</th><th>Materia</th><th>Seccion</th><th>Maestro</th>";
		
		if ($_SESSION['permisos']['crear_grupos'] == 1) {
			echo "<th colspan=\"2\">Acción</th>";
		}
		
		echo "</tr></thead>\n";
		
		/* Empezar la consulta mysql */
		$query = sprintf ("SELECT sec.Nrc, sec.Materia, mat.Descripcion, sec.Seccion, sec.Maestro, m.Nombre, m.Apellido, m.Codigo FROM Secciones AS sec INNER JOIN Materias AS mat ON sec.Materia = mat.Clave INNER JOIN Maestros AS m ON sec.Maestro = m.Codigo LIMIT %s, %s", $offset, $show);
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			/* El nrc */
			if ($_SESSION['codigo'] == $object->Codigo || (isset ($_SESSION['permisos']['grupos_globales']) && $_SESSION['permisos']['grupos_globales'] == 1)) {
				printf ("<td><a href=\"ver_grupo.php?nrc=%s\">%s</a></td>",$object->Nrc, $object->Nrc);
			} else {
				printf ("<td>%s</td>", $object->Nrc);
			}
			printf ("<td><a href=\"ver_materia.php?clave=%s\">%s</a></td>", $object->Materia, $object->Materia);
			printf ("<td>%s</td>", $object->Descripcion);
			printf ("<td>%s</td>", $object->Seccion);
			printf ("<td><a href=\"ver_maestro.php?codigo=%s\">%s %s</a></td>", $object->Codigo, $object->Apellido, $object->Nombre);
			if ($_SESSION['permisos']['crear_grupos'] == 1) {
				printf ("<td><a href=\"editar_seccion.php?nrc=%s\"><img class=\"icon\" src=\"../img/properties.png\" alt=\"editar\"/></a></td>\n", $object->Nrc);
				printf ("<td><a href=\"eliminar_seccion.php?nrc=%s\"", $object->Nrc);
				printf (" onclick=\"return confirmarDrop(this, '¿Realmente desea eliminar el NRC %s?')\">", $object->Nrc);
				echo "<img class=\"icon\" src=\"../img/remove.png\" alt=\"eliminar\" /></a></td>\n";
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
