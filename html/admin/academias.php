<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	if (!isset ($_SESSION['permisos']['admin_academias']) || $_SESSION['permisos']['admin_academias'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		agrega_mensaje (3, "Privilegios insuficientes");
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
	<h1>Academias</h1>
	<?php
		require_once '../mysql-con.php';
		
		$query = "SELECT * FROM Academias";
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<table border=\"1\"><thead><tr><th>Nombre</th><th>Presidente</th><th>Acciones</th></tr></thead><tbody>";
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<tr><td><a href=\"ver_academia.php?id=%s\">%s</a></td>", $object->Id, $object->Nombre);
			
			if (is_null ($object->Maestro)) {
				echo "<td><b>Indefinido</b></td></tr>\n";
			} else {
				$query = sprintf ("SELECT Nombre, Apellido FROM Maestros WHERE Codigo = '%s'", $object->Maestro);
				$result_maestro = mysql_query ($query, $mysql_con);
				$maestro = mysql_fetch_object ($result_maestro);
				printf ("<td>%s %s</td></tr>\n", $maestro->Apellido, $maestro->Nombre);
				mysql_free_result ($result_maestro);
			}
		}
		
		echo "</tbody></table>";
		
		mysql_free_result ($result);
	?>
</body>
</html>
