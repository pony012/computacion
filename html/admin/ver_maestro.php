<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	/* Validar la clave la materia */
	if (!isset ($_GET['codigo']) || !preg_match ("/^([0-9]){7}$/", $_GET['codigo'])) {
		header ("Location: usuarios.php?e=codigo");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	$query = sprintf ("SELECT * FROM Maestros WHERE Codigo='%s' LIMIT 1", $_GET['codigo']);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: usuarios.php?e=noexiste");
		mysql_free_result ($result);
		mysql_close ($mysql_con);
		exit;
	}
	
	$object = mysql_fetch_object ($result);
	mysql_free_result ($result);
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
	<h1>Profesor</h1>
	<?php
		printf ("<p>Profesor: %s %s (%s)</p>\n", $object->Nombre, $object->Apellido, $object->Codigo);
		printf ("<p>Correo electrónico: %s</p>", $object->Correo);
		
		require_once "../mysql-con.php";
		
		/* Recuperar la cantidad total de filas */
		$query = sprintf ("SELECT COUNT(*) AS TOTAL FROM Secciones WHERE Maestro = '%s'", $_GET['codigo']);
		$result = mysql_query ($query, $mysql_con);
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
		
		echo "<p>Grupos del maestro. (Mostrando del ". ($offset + 1) ." al ". ($offset + $show) . ")</p>";
		
		echo "<table border=\"1\">";
		
		/* Mostrar la cabecera */
		echo "<thead><tr><th>NRC</th><th>Clave</th><th>Materia</th><th>Seccion</th><th>Maestro</th></tr></thead>\n";
		
		/* Empezar la consulta mysql */
		$query = sprintf ("SELECT sec.Nrc, sec.Materia, mat.Descripcion, sec.Seccion, sec.Maestro, m.Nombre, m.Apellido FROM Secciones AS sec INNER JOIN Materias AS mat ON sec.Materia = mat.Clave INNER JOIN Maestros AS m ON sec.Maestro = m.Codigo WHERE sec.Maestro = '%s' LIMIT %s, %s", $_GET['codigo'], $offset , $show);
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			/* El nrc */
			echo "<td>".$object->Nrc."</td>";
			printf ("<td><a href=\"ver_materia.php?clave=%s\">%s</a></td>", $object->Materia, $object->Materia);
			echo "<td>".$object->Descripcion."</td>";
			echo "<td>".$object->Seccion."</td>";
			echo "<td>".$object->Apellido." ".$object->Nombre."</td>";
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
		if ($offset > 0) {
			printf ("<a href=\"%s?codigo=%s&off=0\"><img class=\"icon\" src=\"../img/first.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], $_GET['codigo']);
			printf ("<a href=\"%s?codigo=%s&off=%s\"><img class=\"icon\" src=\"../img/prev.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], $_GET['codigo'], $prev);
		}
		if ($offset + $show < $total - 1) { 
			printf ("<a href=\"%s?codigo=%s&off=%s\"><img class=\"icon\" src=\"../img/next.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], $_GET['codigo'], $next);
			printf ("<a href=\"%s?codigo=%s&off=%s\"><img class=\"icon\" src=\"../img/last.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], $_GET['codigo'], $ultimo);
		}
		
		echo "</p>\n";
	?>
</body>
</html>