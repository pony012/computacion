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
	if (!isset ($_GET['clave']) || !preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_GET['clave'])) {
		header ("Location: materias.php");
		agrega_mensaje (3, "Materia inválida");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	$query = "SELECT * FROM Materias WHERE Clave='". $_GET['clave'] ."' LIMIT 1";
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: materias.php");
		agrega_mensaje (3, "Materia inválida");
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
<body><?php require_once 'mensajes.php'; mostrar_mensajes (); ?>
	<h1>Detalles de la materia</h1>
	<?php
		echo "<p>Materia: " . $object->Clave . "</p>\n";
		echo "<p>Descripción: " . $object->Descripcion . "</p>\n";
		
		require_once "../mysql-con.php";
		
		/* Recuperar la cantidad total de filas */
		$query = sprintf ("SELECT COUNT(*) AS TOTAL FROM Secciones WHERE Materia = '%s'", $_GET['clave']);
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
		
		printf ("<p>Grupos de la materia %s, mostrando de %s al %s</p>", $_GET['clave'], ($offset + 1), ($offset + $show));
		
		echo "<table border=\"1\">";
		
		/* Mostrar la cabecera */
		echo "<thead><tr><th>NRC</th><th>Clave</th><th>Materia</th><th>Seccion</th><th>Maestro</th></tr></thead>\n";
		
		/* Empezar la consulta mysql */
		$query = sprintf ("SELECT sec.Nrc, sec.Materia, mat.Descripcion, sec.Seccion, sec.Maestro, m.Nombre, m.Apellido, m.Codigo FROM Secciones AS sec INNER JOIN Materias AS mat ON sec.Materia = mat.Clave INNER JOIN Maestros AS m ON sec.Maestro = m.Codigo WHERE sec.Materia = '%s' LIMIT %s, %s", $_GET['clave'], $offset , $show);
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			/* El nrc */
			if ($_SESSION['codigo'] == $object->Codigo || (isset ($_SESSION['permisos']['grupos_globales']) && $_SESSION['permisos']['grupos_globales'] == 1)) {
				printf ("<td><a href=\"ver_grupo.php?nrc=%s\">%s</a></td>",$object->Nrc, $object->Nrc);
			} else {
				printf ("<td>%s</td>", $object->Nrc);
			}
			printf ("<td>%s</td>", $object->Materia);
			printf ("<td>%s</td>", $object->Descripcion);
			printf ("<td>%s</td>", $object->Seccion);
			printf ("<td><a href=\"ver_maestro.php?codigo=%s\">%s %s</a></td>", $object->Codigo, $object->Apellido, $object->Nombre);
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
			printf ("<a href=\"%s?%s\"><img class=\"icon\" src=\"../img/first.png\" alt=\"primero\" /></a>\n", $_SERVER['SCRIPT_NAME'], htmlentities (http_build_query (Array ('off' => 0) + $link)));
			printf ("<a href=\"%s?%s\"><img class=\"icon\" src=\"../img/prev.png\" alt=\"anterior\"/></a>\n", $_SERVER['SCRIPT_NAME'], htmlentities (http_build_query (Array ('off' => $prev) + $link)));
		}
		if ($offset + $show < $total) { 
			printf ("<a href=\"%s?%s\"><img class=\"icon\" src=\"../img/next.png\" alt=\"siguiente\"/></a>\n", $_SERVER['SCRIPT_NAME'], htmlentities (http_build_query (Array ('off' => $next) + $link)));
			printf ("<a href=\"%s?%s\"><img class=\"icon\" src=\"../img/last.png\" alt=\"ultimo\" /></a>\n", $_SERVER['SCRIPT_NAME'], htmlentities (http_build_query (Array ('off' => $ultimo) + $link)));
		}
		
		echo "</p>\n";
	?>
</body>
</html>
