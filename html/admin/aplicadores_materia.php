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
	
	if (!preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_GET['materia'])) {
		header ("Location: aplicadores.php?e=clave");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	if (!isset ($_GET['tipo'])) { /* Cuando sólo abren Materia */
		$query = sprintf ("SELECT Materia FROM Aplicadores WHERE Materia = '%s' LIMIT 1", $_GET['materia']);
	} else { /* Cuando nos dan materia y Tipo */
		settype ($_GET['tipo'], 'integer');
		
		/* Verificar si el tipo de evaluación existe */
		$query = sprintf ("SELECT Tipo FROM Aplicadores WHERE Materia = '%s' AND Tipo = '%s' LIMIT 1", $_GET['materia'], $_GET['tipo']);
	}
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		mysql_free_result ($result);
		mysql_close ($mysql_con);
		header ("Location: aplicadores.php?e=noexiste");
		exit;
	}
	
	mysql_free_result ($result);
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
	
	/* SELECT COUNT(DISTINCT A.Materia, A.Tipo) AS TOTAL FROM Aplicadores AS A */
	if (isset ($_GET['tipo'])) {
		$query = sprintf ("SELECT COUNT(DISTINCT Materia, Tipo, Salon) AS TOTAL FROM Aplicadores WHERE Materia = '%s' AND Tipo = '%s'", $_GET['materia'], $_GET['tipo']);
	
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
	}
	
	if (!isset ($_GET['tipo'])) {
		$query = sprintf ("SELECT Descripcion FROM Materias WHERE Clave = '%s'", $_GET['materia']);
		$result = mysql_query ($query, $mysql_con);
		$object = mysql_fetch_object ($result);
		
		printf ("<p>Departamentales para la materia %s</p>\n", $object->Descripcion);
		echo "<p>Ver <a href=\"aplicadores_general.php\">todas las materias</a></p>";
		mysql_free_result ($result);
	} else {
		$query = sprintf ("SELECT P.Clave, M.Descripcion, E.Descripcion AS Evaluacion FROM Porcentajes AS P INNER JOIN Materias AS M ON P.Clave = M.Clave INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave = '%s' AND Tipo = '%s'", $_GET['materia'], $_GET['tipo']);
		$result = mysql_query ($query, $mysql_con);
		$object = mysql_fetch_object ($result);
		
		printf ("<p>Salones asignados para <a href=\"aplicadores_materia.php?materia=%s\">%s</a> en la fomra de evaluación %s</p>\n", $object->Clave, $object->Descripcion, $object->Evaluacion);
		mysql_free_result ($result);
	}
	
	echo "<table border=\"1\">";
	
	if (!isset ($_GET['tipo'])) {
		echo "<thead><tr><th>Materia</th><th>Tipo</th></tr></thead>\n";
		$query = sprintf ("SELECT DISTINCT A.Materia, A.Tipo, Mat.Descripcion, E.Descripcion AS Evaluacion FROM Aplicadores AS A INNER JOIN Materias AS Mat ON A.Materia = Mat.Clave INNER JOIN Evaluaciones AS E ON A.Tipo = E.Id WHERE Materia = '%s' ORDER BY A.Materia, A.Tipo", $_GET['materia']);
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<tr><td>%s %s</td>", $object->Materia, $object->Descripcion);
			printf ("<td><a href=\"aplicadores_materia.php?materia=%s&tipo=%s\">%s</a></td></tr>\n", $object->Materia, $object->Tipo, $object->Evaluacion);
		}
		
		mysql_free_result ($result);
		
		echo "</tbody>";
	} else {
		echo "<thead><tr><th>Materia</th><th>Salón</th><th>Fecha</th><th>Hora</th><th>Tipo</th><th>Maestro</th></tr></thead>\n";
		$query = sprintf ("SELECT DISTINCT A.Materia, Mat.Descripcion, A.Salon, UNIX_TIMESTAMP (A.FechaHora) AS FechaHora, A.Tipo, E.Descripcion AS Evaluacion, A.Maestro, M.Nombre, M.Apellido FROM Aplicadores AS A INNER JOIN Materias AS Mat ON A.Materia = Mat.Clave INNER JOIN Evaluaciones AS E ON A.Tipo = E.Id INNER JOIN Maestros AS M ON A.Maestro = M.Codigo WHERE A.Materia = '%s' AND A.Tipo = '%s' ORDER BY A.Salon, FechaHora LIMIT %s, %s", $_GET['materia'], $_GET['tipo'], $offset, $show);
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<tr><td>%s %s</td>", $object->Materia, $object->Descripcion);
			$link = array ('materia' => $object->Materia, 'tipo' => $object->Tipo, 'salon' => $object->Salon);
			printf ("<td><a href=\"ver_salon_aplicador.php?%s\">%s</a></td>", htmlentities (http_build_query ($link)), $object->Salon);
			printf ("<td>%s</td><td>%s</td>", strftime ("%a %e %h %Y", $object->FechaHora), strftime ("%H:%M", $object->FechaHora));
			printf ("<td>%s</td><td>%s %s</td></tr>\n", $object->Evaluacion, $object->Apellido, $object->Nombre);
		}
		echo "</tbody>";
	}
	
	echo "</table>";
	
	if (isset ($_GET['tipo'])) {
		/* Paginacion sólo para cuando hay tipo definido */
		echo "<p>";
		$next = $offset + $show;
		$ultimo = $total - ($total % $cant);
		if ($next >= $total) $next = $ultimo;
		$prev = $offset - $cant;
		if ($prev < 0) $prev = 0;
	
		/* Mostrar las flechas de dezplamiento */
		if ($offset > 0) {
			printf ("<a href=\"%s?off=0&materia=%s&tipo=%s\"><img class=\"icon\" src=\"../img/first.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], $_GET['materia'], $_GET['tipo']);
			printf ("<a href=\"%s?off=%s&materia=%s&tipo=%s\"><img class=\"icon\" src=\"../img/prev.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], $prev, $_GET['materia'], $_GET['tipo']);
		}
		if ($offset + $show < $total) { 
			printf ("<a href=\"%s?off=%s&materia=%s&tipo=%s\"><img class=\"icon\" src=\"../img/next.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], $next, $_GET['materia'], $_GET['tipo']);
			printf ("<a href=\"%s?off=%s&materia=%s&tipo=%s\"><img class=\"icon\" src=\"../img/last.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], $ultimo, $_GET['materia'], $_GET['tipo']);
		}
	
		echo "</p>\n";
	}
?>
<ul>
	<li><a href="nuevo_salon_aplicador.php">Nuevo salón para aplicar examen</a></li>
</ul>
</body>
</html>
