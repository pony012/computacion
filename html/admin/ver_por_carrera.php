<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	/* Validar la clave la materia */
	if (!isset ($_SESSION['permisos']['grupos_globales']) || $_SESSION['permisos']['grupos_globales'] != 1) {
		header ("Location: carreras.php");
		agrega_mensaje (3, "Privilegios insuficientes");
		exit;
	}
	
	if (!isset ($_GET['carrera']) || !preg_match ("/^([A-Za-z]){3}$/", $_GET['carrera'])) {
		header ("Location: carreras.php");
		agrega_mensaje (3, "Carrera inválida");
		exit;
	}
	
	$_GET['carrera'] = strtoupper ($_GET['carrera']);
	
	require_once '../mysql-con.php';
	
	$query = sprintf ("SELECT Clave FROM Carreras WHERE Clave='%s' LIMIT 1", $_GET['carrera']);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: carreras.php");
		agrega_mensaje (3, "Carrera inválida");
		mysql_free_result ($result);
		mysql_close ($mysql_con);
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
<body><?php require_once 'mensajes.php'; mostrar_mensajes (); ?>
	<h1>Alumnos por carrera</h1>
	<?php
		require_once "../mysql-con.php";
		
		$query = sprintf ("SELECT * FROM Carreras WHERE Clave = '%s'", $_GET['carrera']);
		$result = mysql_query ($query, $mysql_con);
		$object = mysql_fetch_object ($result);
		mysql_free_result ($result);
		
		printf ("<p>Alumnos de la carrera %s (%s)</p>\n", $object->Descripcion, $object->Clave);
		
		/* Recuperar la cantidad total de filas */
		$query = sprintf ("SELECT COUNT(*) AS TOTAL FROM Alumnos WHERE Carrera = '%s'", $_GET['carrera']);
		$result = mysql_query ($query, $mysql_con);
		$row = mysql_fetch_object ($result);
		$total = $row->TOTAL;
		mysql_free_result ($result);
		
		$offset = (isset ($_GET['off'])) ? $_GET['off'] : 0;
		settype ($offset, "integer");
		$cant = 30;
		$show = $cant;
		
		if ($offset >= $total) $offset = $total - $cant;
		if ($offset < 0) $offset = 0;
		if (($offset + $cant) >= $total) $show = $total - $offset;
		
		printf ("<p>Mostrando de %s al %s</p>", ($offset + 1), ($offset + $show));
		
		echo "<table border=\"1\">";
		
		/* Mostrar la cabecera */
		echo "<thead><tr><th>Carrera</th><th>Código</th><th>Alumno</th></tr></thead>\n";
		
		/* Empezar la consulta mysql */
		$query = sprintf ("SELECT * FROM Alumnos WHERE Carrera='%s' ORDER BY Apellido, Nombre LIMIT %s, %s", $_GET['carrera'], $offset , $show);
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			printf ("<td>%s</td>", $object->Carrera);
			printf ("<td>%s</td>", $object->Codigo);
			printf ("<td>%s %s</td>", $object->Apellido, $object->Nombre);
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
			printf ("<a href=\"%s?%s\"><img class=\"icon\" src=\"../img/first.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], htmlentities (http_build_query (Array ('off' => 0) + $link)));
			printf ("<a href=\"%s?%s\"><img class=\"icon\" src=\"../img/prev.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], htmlentities (http_build_query (Array ('off' => $prev) + $link)));
		}
		if ($offset + $show < $total) { 
			printf ("<a href=\"%s?%s\"><img class=\"icon\" src=\"../img/next.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], htmlentities (http_build_query (Array ('off' => $next) + $link)));
			printf ("<a href=\"%s?%s\"><img class=\"icon\" src=\"../img/last.png\" /></a>\n", $_SERVER['SCRIPT_NAME'], htmlentities (http_build_query (Array ('off' => $ultimo) + $link)));
		}
		
		echo "</p>\n";
	?>
</body>
</html>
