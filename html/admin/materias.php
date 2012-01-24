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
<body><?php require_once 'mensajes.php'; mostrar_mensajes ();?>
	<h1>Materias</h1>
	<?php
		require_once "../mysql-con.php";
		
		/* Recuperar la cantidad total de filas */
		$result = mysql_query ("SELECT COUNT(*) AS TOTAL FROM Materias", $mysql_con);
		$row = mysql_fetch_object ($result);
		$total = $row->TOTAL;
		mysql_free_result ($result);
		
		$offset = (isset ($_GET['off'])) ? $_GET['off'] : 0;
		settype ($offset, "integer");
		$cant = 40;
		$show = $cant;
		
		if ($offset >= $total) $offset = $total - $cant;
		if ($offset < 0) $offset = 0;
		if (($offset + $cant) >= $total) $show = $total - $offset;
		
		printf ("<p>Mostrando materias del %s al %s</p>", ($offset + 1), ($offset + $cant));
		
		echo "<table border=\"1\">";
		
		/* Mostrar la cabecera */
		echo "<thead><tr><th>Clave</th><th>Descripción</th><th>Acciones</th>";
		
		echo "</tr></thead>\n";
		
		/* Empezar la consulta mysql */
		$query = sprintf ("SELECT * FROM Materias LIMIT %s, %s", $offset, $show);
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			/* El nrc */
			printf ("<td><a href=\"ver_materia.php?clave=%s\">%s</a></td>", $object->Clave, $object->Clave);
			echo "<td>".$object->Descripcion."</td>";
			echo "<td>";
			
			if ($_SESSION['permisos']['crear_materias'] == 1) {
				echo "<a href=\"editar_materia.php?clave=" . $object->Clave . "\"><img class=\"icon\" src=\"../img/properties.png\" /></a>";
				echo "<a href=\"eliminar_materia.php?clave=" . $object->Clave . "\"\n";
				echo " onclick=\"return confirmarDrop(this, '¿Realmente desea eliminar la materia ".$object->Clave."?')\">";
				echo "<img class=\"icon\" src=\"../img/remove.png\" /></a>";
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
		if ($_SESSION['permisos']['crear_materias'] == 1) {
			echo "<li><a href=\"nueva_materia.php\">Agregar una nueva materia</a></li>\n";
		}
		echo "</ul>";
	?>
</body>
</html>
