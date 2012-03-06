<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_GET['tipo']) || ($_GET['tipo'] != 'n' && $_GET['tipo'] != 'e')) {
		header ("Location: academias.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	if (!has_permiso ('admin_academias')) {
		/* Privilegios insuficientes */
		header ("Location: academias.php");
		agrega_mensaje (3, "Privilegios insuficientes");
		exit;
	}
	
	/* Verificar que haya datos POST */
	if ($_GET['tipo'] == 'e' && (!isset ($_GET['id']) && $_GET['id'] < 0)) {
		/* No especificó un id */
		header ("Location: academias.php");
		exit;
	}
	
	if ($_GET['tipo'] == 'e') {
		require_once '../mysql-con.php';
		
		settype ($_GET['tipo'], 'integer');
		
		$query = sprintf ("SELECT * FROM Academias WHERE Id = '%s'", $_GET['id']);
		$result = mysql_query ($query, $mysql_con);
		
		if (mysql_num_rows ($result) == 0) {
			header ("Location: academias.php");
			agrega_mensaje (3, "La academia especificada no existe");
			exit;
		}
		
		$academia = mysql_fetch_object ($result);
		mysql_free_result ($result);
	} ?>
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
	if ($_GET['tipo'] == 'e') {
		echo "<h1>Editar academia</h1>\n<form method=\"post\" action=\"post_academia.php\"><input type=\"hidden\" name=\"modo\" value=\"editar\" />\n";
		
		printf ("<input type=\"hidden\" name=\"id\" value=\"%s\" />\n", $_GET['id']);
		printf ("<p>Nombre de la academia: <input type=\"text\" name=\"nombre\" value=\"%s\" /></p>\n", $academia->Nombre);
		
		echo "<p>Presidente de academia: <select name=\"maestro\">\n";
		
		$query = "SELECT Codigo, Nombre, Apellido FROM Maestros ORDER BY Apellido, Nombre";
		$result = mysql_query ($query, $mysql_con);
		
		if (is_null ($academia->Maestro)) {
			echo "<option value=\"NULL\" selected=\"selected\" >Indefinido</option>\n";
			
			while (($object = mysql_fetch_object ($result))) {
				printf ("<option value=\"%s\">%s %s</option>\n", $object->Codigo, $object->Apellido, $object->Nombre);
			}
		} else {
			echo "<option value=\"NULL\">Indefinido</option>\n";
			
			while (($object = mysql_fetch_object ($result))) {
				if ($academia->Maestro == $object->Codigo) {
					printf ("<option value=\"%s\" selected=\"selected\">%s %s</option>\n", $object->Codigo, $object->Apellido, $object->Nombre);
				} else {
					printf ("<option value=\"%s\">%s %s</option>\n", $object->Codigo, $object->Apellido, $object->Nombre);
				}
			}
		}
		
		mysql_free_result ($result);
		echo "</select></p><p><input type=\"submit\" value=\"Actualizar\" /></p></form>";
	} else { ?>
	<h1>Nueva Academia</h1>
	<form method="post" action="post_academia.php">
	<input type="hidden" name="modo" value="nuevo" />
	<p>Nombre de la academia: <input type="text" name="nombre" /></p>
	<p>Presidente de academia: <select name="maestro"><option value="NULL" selected="selected">Indefinido</option>
	<?php
		require_once '../mysql-con.php';
		
		$query = "SELECT Codigo, Nombre, Apellido FROM Maestros ORDER BY Apellido, Nombre";
		$result = mysql_query ($query, $mysql_con);
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<option value=\"%s\">%s %s</option>\n", $object->Codigo, $object->Apellido, $object->Nombre);
		}
		
		mysql_free_result ($result); ?>
	</select></p><p><input type="submit" value="Nueva academia" /></p></form>
	<?php } ?>
</body>
</html>
