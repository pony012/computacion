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
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
</head>
<body>
	<h1>Grupos</h1>
	<?php
		require_once "../mysql-con.php";
		
		$offset = $_GET['off'];
		settype ($offset, "integer");
		$cant = 20;
		
		echo "<p>Mostrando los grupos del ". $offset ." al ". ($offset + $cant) . "</p>";
		
		echo "<table border=\"1\">";
		
		/* Mostrar la cabecera */
		echo "<thead><tr><th>NRC</th><th>Materia</th><th>Maestro</th></tr></thead>\n";
		
		/* Empezar la consulta mysql */
		if ($_SESSION['permisos']['grupos_globales'] == 1) {
			$query = "SELECT sec.Nrc, sec.Materia, mat.Descripcion, sec.Seccion, sec.Maestro, m.Nombre FROM Secciones AS sec INNER JOIN Materias AS mat ON sec.Materia = mat.Clave INNER JOIN Maestros AS m ON sec.Maestro = m.Codigo LIMIT ". $offset . ",". $cant;
		} else {
			$query = sprintf ("SELECT sec.Nrc, sec.Materia, mat.Descripcion, sec.Seccion, sec.Maestro, m.Nombre FROM Secciones AS sec INNER JOIN Materias AS mat ON sec.Materia = mat.Clave INNER JOIN Maestros AS m ON sec.Maestro = m.Codigo WHERE sec.Maestro='%s' LIMIT %s,%s", $_SESSION['codigo'], $offset, $cant);
		}
		var_dump ($query);
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			/* El nrc */
			echo "<td>".$object->Nrc."</td>";
			
			echo "<td>".$object->Materia." ".$object->Descripcion."</td>";
			echo "<td>".$object->Maestro." ".$object->Nombre."</td>";
			echo "</tr>\n";
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
	<?php
		if ($_SESSION['permisos']['crear_grupos'] == 1) {
			echo "<li><a href=\"nuevo_grupo.php\">Agregar una nueva seccion</a></li>\n";
		}
	?>
	</ul>
</body>
</html>
