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
		
		echo "<p>Carreras, del ". ($offset + 1) ." al ". ($offset + $show) . "</p>";
		
		echo "<table border=\"1\">";
		
		/* Mostrar la cabecera */
		echo "<thead><tr><th>Clave</th><th>Descripción</th><th>Acciones</th></tr></thead>";
		
		$query = "SELECT * FROM Carreras LIMIT ". $offset . ",". $show;
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			echo "<td>".$object->Clave."</td>";
			echo "<td>".$object->Descripcion."</td>";
			
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
			echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?off=0\"><img class=\"icon\" src=\"../img/first.png\" /></a>\n";
			echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?off=".$prev."\"><img class=\"icon\" src=\"../img/prev.png\" /></a>\n";
		}
		if ($offset + $show < $total) { 
			echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?off=".$next."\"><img class=\"icon\" src=\"../img/next.png\" /></a>\n";
			echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?off=".$ultimo."\"><img class=\"icon\" src=\"../img/last.png\" /></a>\n";
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
