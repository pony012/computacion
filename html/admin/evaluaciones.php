<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['admin_evaluaciones']) || $_SESSION['permisos']['admin_evaluaciones'] != 1) {
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
	<script language="javascript" src="../scripts/comun.js" type="text/javascript"></script>
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
</head>
<body>
	<h1>Formas de evaluación</h1>
	<p>Las siguientes formas de evalución están disponibles para las materias</p>
	<?php
		require_once "../mysql-con.php";
		
		echo "<table border=\"1\">";
		
		/* Mostrar la cabecera */
		echo "<thead><tr><th>Nombre</th><th colspan=\"2\">Acción</th></tr></thead>";
		
		/* Empezar la consulta mysql */
		$query = "SELECT * FROM Evaluaciones";
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			
			printf ("<td>%s<td>", $object->Descripcion);
			if ($object->Id != 0) { /* El extraordinario es un caso especial */
				printf ("<td><a href=\"edit_eval.php?m=e&id=%s\"><img class=\"icon\" src=\"../img/properties.png\" /></a>", $object->Id);
				
				printf ("<a href=\"eliminar_eval.php?id=%s\"\n", $object->Id);
				printf (" onclick=\"return confirmarDrop(this, '¿Realmente desea eliminar %s?')\">", $object->Descripcion);
				printf ("<img class=\"icon\" src=\"../img/remove.png\" /></a></td>");
			}
			
			echo "</tr>\n";
		}
		
		echo "</tbody></table>\n";
	?>
	<ul><li><a href="edit_eval.php?m=n">Nueva forma de evaluación</a></li></ul>
</body>
</html>
