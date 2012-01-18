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
<body>
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
			
			if (isset ($_SESSION['permisos']['grupos_globales']) && $_SESSION['permisos']['grupos_globales'] == 1) {
				printf ("<td><a href=\"ver_por_carrera.php?carrera=%s\">%s</a></td>", $object->Clave, $object->Clave);
			} else {
				printf ("<td>%s</td>", $object->Clave);
			}
			printf ("<td>%s</td>", $object->Descripcion);
			
			echo "<td>";
			if ($_SESSION['permisos']['admin_carreras'] == 1) {
				echo "<a href=\"editar_carrera.php?clave=" . $object->Clave . "\"><img class=\"icon\" src=\"../img/properties.png\" /></a>";
				echo "<a href=\"eliminar_carrera.php?clave=" . $object->Clave . "\"\n";
				echo " onclick=\"return confirmarDrop(this, '¿Realmente desea eliminar la carrera ".$object->Clave."?')\">";
				echo "<img class=\"icon\" src=\"../img/remove.png\" /></a>";
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
		if ($offset > 0) {
			printf ("<a href=\"%s?off=0\"><img class=\"icon\" src=\"../img/first.png\" /></a>\n", $_SERVER['SCRIPT_NAME']);
			printf ("<a href=\"%s?off=%s\"><img class=\"icon\" src=\"../img/prev.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], $prev);
		}
		if ($offset + $show < $total) { 
			printf ("<a href=\"%s?off=%s\"><img class=\"icon\" src=\"../img/next.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], $next);
			printf ("<a href=\"%s?off=%s\"><img class=\"icon\" src=\"../img/last.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], $ultimo);
		}
		
		echo "</p>\n";
	?>
	<?php
		echo "<ul>";
		if ($_SESSION['permisos']['admin_carreras'] == 1) {
			echo "<li><a href=\"nueva_carrera.php\">Nueva carrera</a></li>\n";
		}
		echo "</ul>";
	?>
</body>
</html>
