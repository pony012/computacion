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
	<h1>Materias</h1>
	<?php
		require_once "../mysql-con.php";
		
		$offset = (isset ($_GET['off'])) ? $_GET['off'] : 0;
		settype ($offset, "integer");
		$cant = 20;
		
		echo "<p>Mostrando registros del ". $offset ." al ". ($offset + $cant) . "</p>";
		
		echo "<table border=\"1\">";
		
		/* Mostrar la cabecera */
		echo "<thead><tr><th>Clave</th><th>Descripción</th><th>Departamental 1</th><th>Departamental 2</th><th>Puntos del maestro</th>";
		
		/* Si tiene permisos de edición, mostrar la columna de edición */
		if ($_SESSION['permisos']['crear_materias'] == 1) {
			echo "<th colspan=\"2\">Acción</th>";
		}
		
		echo "</tr></thead>\n";
		
		/* Empezar la consulta mysql */
		$query = "SELECT * FROM Materias LIMIT ". $offset . ",". $cant;
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			/* El nrc */
			echo "<td>".$object->Clave."</td>";
			echo "<td>".$object->Descripcion."</td>";
			echo "<td>".$object->Porcentaje_Depa1."</td>";
			echo "<td>".$object->Porcentaje_Depa2."</td>";
			echo "<td>".$object->Porcentaje_Puntos."</td>";
			
			if ($_SESSION['permisos']['crear_materias'] == 1) {
				echo "<td><a href=\"editar_materia.php?clave=" . $object->Clave . "\"><img class=\"icon\" src=\"../img/properties.png\" /></a></td>";
				echo "<td><a href=\"eliminar_materia.php?clave=" . $object->Clave . "\"\n";
				echo " onclick=\"return confirmarDrop(this, '¿Realmente desea eliminar la materia ".$object->Clave."?')\">";
				echo "<img class=\"icon\" src=\"../img/remove.png\" /></a></td>";
			}
			echo "</tr>\n";
		}
		
		echo "</tbody>";
		echo "</table>\n";
		
		echo "<p>";
		/* Mostrar las flechas de desplamiento */
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
		if ($_SESSION['permisos']['crear_materias'] == 1) {
			echo "<li><a href=\"nueva_materia.php\">Agregar una nueva materia</a></li>\n";
		}
		echo "</ul>";
	?>
</body>
</html>
