<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	if (!isset ($_SESSION['permisos']['asignar_aplicadores']) || $_SESSION['permisos']['asignar_aplicadores'] != 1) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
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
<body><?php require_once 'mensajes.php'; mostrar_mensajes (); ?>
<h1>Salones de aplicación de exámenes</h1>
<?php
	setlocale (LC_ALL, "es_MX.UTF-8");
	require_once '../mysql-con.php';
	
	/* SELECT COUNT(DISTINCT A.Materia, A.Tipo) AS TOTAL FROM Aplicadores AS A */
	$query = "SELECT COUNT(DISTINCT Materia, Tipo) AS TOTAL FROM Salones_Aplicadores";
	
	$result = mysql_query ($query, $mysql_con);
	$object = mysql_fetch_object ($result);
	$total = $object->TOTAL;
	mysql_free_result ($result);
	
	$offset = (isset ($_GET['off'])) ? $_GET['off'] : 0;
	settype ($offset, "integer");
	$cant = 30;
	$show = $cant;
	
	if ($offset >= $total) $offset = $total - $cant;
	if ($offset < 0) $offset = 0;
	if (($offset + $cant) >= $total) $show = $total - $offset;
	
	printf ("<p>Departamentales asignados (mostrando del %s al %s)</p>", ($offset + 1), ($offset + $show));
	
	/* SELECT DISTINCT A.Materia, A.Tipo, M.Descripcion, E.Descripcion AS Evaluaciones FROM Aplicadores AS A INNER JOIN Materias AS M ON A.Materia = M.Clave INNER JOIN Evaluaciones AS E ON A.Tipo = E.Id */
	$query = sprintf ("SELECT DISTINCT A.Materia, A.Tipo, M.Descripcion, E.Descripcion AS Evaluacion FROM Salones_Aplicadores AS A INNER JOIN Materias AS M ON A.Materia = M.Clave INNER JOIN Evaluaciones AS E ON A.Tipo = E.Id ORDER BY A.Materia, A.Tipo LIMIT %s, %s", $offset, $show);
	
	$result = mysql_query ($query, $mysql_con);
	
	echo "<table border=\"1\">";
	
	echo "<thead><tr><th>Materia</th><th>Tipo</th></tr></thead>\n";
	
	echo "<tbody>";
	while (($object = mysql_fetch_object ($result))) {
		printf ("<tr><td><a href=\"aplicadores_materia.php?materia=%s\">%s %s</a></td>", $object->Materia, $object->Materia, $object->Descripcion);
		printf ("<td><a href=\"aplicadores_materia.php?materia=%s&tipo=%s\">%s</a></td></tr>\n", $object->Materia, $object->Tipo, $object->Evaluacion);
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
		printf ("<a href=\"%s?off=0\"><img class=\"icon\" src=\"../img/first.png\" /></a>\n", $_SERVER['SCRIPT_NAME']);
		printf ("<a href=\"%s?off=%s\"><img class=\"icon\" src=\"../img/prev.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], $prev);
	}
	if ($offset + $show < $total) { 
		printf ("<a href=\"%s?off=%s\"><img class=\"icon\" src=\"../img/next.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], $next);
		printf ("<a href=\"%s?off=%s\"><img class=\"icon\" src=\"../img/last.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], $ultimo);
	}
	
	echo "</p>\n";
?>
<ul>
	<li><a href="auto_aplicadores.php">Calcular automáticamente salones para un departamental</a></li>
	<li><a href="nuevo_salon_aplicador.php">Nuevo salón para aplicar examen</a></li>
</ul>
</body>
</html>
