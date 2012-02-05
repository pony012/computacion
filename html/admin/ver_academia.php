<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	if (!isset ($_GET['id']) || $_GET['id'] < 0) {
		header ("Location: academias.php");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	$query = sprintf ("SELECT * FROM Academias WHERE Id = '%s'", $_GET['id']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: academias.php");
		agrega_mensaje (3, "Academia desconocida");
		exit;
	}
	
	$academia = mysql_fetch_object ($result);
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
<body><?php require_once 'mensajes.php'; mostrar_mensajes ();
	printf ("<h1>Academia %s</h1>\n", $academia->Nombre);
	
	if (!is_null ($academia->Maestro)) {
		$query = sprintf ("SELECT Nombre, Apellido FROM Maestros WHERE Codigo = '%s'", $academia->Maestro);
		
		$result = mysql_query ($query, $mysql_con);
		
		$maestro = mysql_fetch_object ($result);
		printf ("<p>Presidente de la academia: %s %s</p>", $maestro->Apellido, $maestro->Nombre);
		mysql_free_result ($result);
	} else {
		echo "<p><b>Presidente indefinido</b</p>";
	}
	
	echo "<p>Materias de la academia:</p>";
	
	$query = sprintf ("SELECT * FROM Materias WHERE Academia = '%s'", $_GET['id']);
	
	$result = mysql_query ($query, $mysql_con);
	
	echo "<table border=\"1\"><thead><tr><th>Clave</th><th>Materia</th>";
	
	if (isset ($_SESSION['permisos']['admin_academias']) && $_SESSION['permisos']['admin_academias'] == 1) {
		echo "<th>Acciones</th>";
	}
	
	echo "</tr></thead><tbody>";
	
	while (($object = mysql_fetch_object ($result))) {
		printf ("<tr><td>%s</td><td>%s</td>", $object->Clave, $object->Descripcion);
		
		if (isset ($_SESSION['permisos']['admin_academias']) && $_SESSION['permisos']['admin_academias'] == 1) {
			/* Acciones */
			$link = array ('materia' => $object->Clave, 'id' => $academia->Id);
			printf ("<td><a href=\"eliminar_materia_academia.php?%s\"\n onclick=\"return confirmarDrop(this, '¿Realmente desea eliminar la materia %s de la academia %s?')\"><img class=\"icon\" src=\"../img/remove.png\" /></a></td>", htmlentities (http_build_query ($link)), $object->Clave, $academia->Nombre);
		}
	}
	
	echo "</tbody></table>";
	
	/* Mostrar Agregar sólo si tiene permisos */
	if (isset ($_SESSION['permisos']['admin_academias']) && $_SESSION['permisos']['admin_academias'] == 1) {
		echo "<form method=\"POST\" action=\"post_materia_academia.php\">\n<p>Agregar una materia a esta academia:</p><p>\n";
		printf ("<input type=\"hidden\" name=\"id\" value=\"%s\" />\n", $academia->Id);
		echo "<select name=\"materia\"><option selected=\"selected\" value=\"NULL\">Seleccione una materia</option>";
		
		$query = "SELECT Clave, Descripcion FROM Materias WHERE Academia IS NULL";
		$result = mysql_query ($query, $mysql_con);
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<option value=\"%s\">%s - %s</option>\n", $object->Clave, $object->Clave, $object->Descripcion);
		}
		echo "</select>\n<input type=\"image\" src=\"../img/add2.png\" class=\"icon\" /></form>\n";
	} ?>
</body>
</html>