<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['asignar_aplicadores']) || $_SESSION['permisos']['asignar_aplicadores'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<link rel="stylesheet" type="text/css" href="../css/theme.css" />
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
</head>
<body>
<h1>Salones de aplicación de exámenes</h1>
<?php
	setlocale (LC_ALL, "es_MX.UTF-8");
	require_once '../mysql-con.php';
	
	/* SELECT DISTINCT `Materia`, `Salon`, `FechaHora`,`Tipo`,`Maestro` FROM `Aplicadores` */
	$query = "SELECT DISTINCT A.Materia, A.Salon, A.FechaHora, A.Tipo, A.Maestro, Mat.Descripcion, M.Nombre, M.Apellido, E.Descripcion AS Evaluacion FROM Aplicadores AS A INNER JOIN Materias AS Mat ON A.Materia = Mat.Clave INNER JOIN Maestros AS M ON A.Maestro = M.Codigo INNER JOIN Evaluaciones AS E ON A.Tipo = E.Id ORDER BY Materia, Tipo, Salon, FechaHora";
	
	$result = mysql_query ($query, $mysql_con);
	/* FIXME: Número de filas */
	$total = mysql_num_rows ($result);
	mysql_free_result ($result);
	
	$offset = (isset ($_GET['off'])) ? $_GET['off'] : 0;
	settype ($offset, "integer");
	$cant = 30;
	$show = $cant;
	
	if ($offset >= $total) $offset = $total - $cant;
	if ($offset < 0) $offset = 0;
	if (($offset + $cant) >= $total) $show = $total - $offset;
	
	echo "<p>Mostrando registros del ". ($offset + 1) ." al ". ($offset + $show) . "</p>";
	
	$query = sprintf ("SELECT DISTINCT A.Materia, A.Salon, UNIX_TIMESTAMP (A.FechaHora) AS FechaHora, A.Tipo, A.Maestro, Mat.Descripcion, M.Nombre, M.Apellido, E.Descripcion AS Evaluacion FROM Aplicadores AS A INNER JOIN Materias AS Mat ON A.Materia = Mat.Clave INNER JOIN Maestros AS M ON A.Maestro = M.Codigo INNER JOIN Evaluaciones AS E ON A.Tipo = E.Id ORDER BY Materia, Tipo, Salon, FechaHora LIMIT %s, %s", $offset, $show);
	
	$result = mysql_query ($query, $mysql_con);
	
	echo "<table border=\"1\">";
	
	echo "<thead><tr><th>Materia</th><th>Salón</th><th>Fecha</th><th>Hora</th><th>Tipo</th><th>Maestro</th></tr></thead>\n";
	
	echo "<tbody>";
	while (($object = mysql_fetch_object ($result))) {
		printf ("<tr><td><a href=\"ver_materia.php?nrc=%s\">%s</a></td><td>%s</td>", $object->Materia, $object->Descripcion, $object->Salon);
		printf ("<td>%s</td><td>%s</td>", strftime ("%a %e %h %Y", $object->FechaHora), strftime ("%H:%M", $object->FechaHora));
		printf ("<td>%s</td><td>%s %s</td></tr>\n", $object->Evaluacion, $object->Apellido, $object->Nombre);
	}
	
	mysql_free_result ($result);
	
	echo "</tbody>";
	echo "</table>\n";
	
	/* Paginacion */
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
</body>
</html>
