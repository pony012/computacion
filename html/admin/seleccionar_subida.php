<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	/* Verificar que sea al menos presidente de una academia */
	$query = sprintf ("SELECT * FROM Academias WHERE Maestro = '%s' LIMIT 1", $_SESSION['codigo']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: vistas.php");
		agrega_mensaje (3, "Privilegios insuficientes");
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
	<script language="javascript" src="../scripts/comun.js" type="text/javascript"></script>
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes ();?>
	<h1>Subida de calificaciones</h1>
	<?php
		require_once '../mysql-con.php';
		
		$query = sprintf ("SELECT M.Clave, M.Descripcion FROM Materias AS M INNER JOIN Academias AS A ON M.Academia = A.Id WHERE A.Maestro = '%s' AND A.Subida = 1 ORDER BY M.Clave", $_SESSION['codigo']);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (mysql_num_rows ($result) == 0) {
			printf ("<p>Por el momento no hay materias que permitan subida de calificaciones</p>");
			mysql_free_result ($result);
		} else {
			echo "<p>Materia: <select name=\"materia\" id=\"materia\"><option value=\"NULL\">Seleccione una materia</option>\n";
			
			while (($object = mysql_fetch_object ($result))) {
				printf ("<option value=\"%s\">%s - %s</option>\n", $object->Clave, $object->Clave, $object->Descripcion);
			}
			
			mysql_free_result ($result);
			echo "</select></p>\n";
			
			echo "<p>Departamental: <select name=\"depa\" id=\"depa\"><option value=\"NULL\">Seleccione una materia primero</option></select></p>\n";
		}
	?>
</body>
</html>
