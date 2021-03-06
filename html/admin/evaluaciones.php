<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	if (!has_permiso ('admin_evaluaciones')) {
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
	<script language="javascript" src="../scripts/comun.js" type="text/javascript"></script>
	<title><?php echo $cfg['nombre']; ?></title>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes (); ?>
	<h1>Formas de evaluación</h1>
	<p>Las siguientes formas de evalución están disponibles para las materias</p>
	<table border="1">
	<thead><tr><th>Nombre</th><th>Tipo</th><th>Evaluación para uso del maestro</th><th>Subida</th><th>Tiempo de apertura</th><th>Tiempo de cierre</th><th>Acción</th></tr></thead><tbody>
	<?php
		setlocale (LC_ALL, "es_MX.UTF-8");
		database_connect ();
		
		/* Empezar la consulta mysql */
		$query = "SELECT E.Descripcion, E.Id, E.Exclusiva, E.Estado, UNIX_TIMESTAMP (E.Apertura) AS Apertura, UNIX_TIMESTAMP (E.Cierre) AS Cierre, GE.Descripcion AS Grupo FROM Evaluaciones AS E INNER JOIN Grupos_Evaluaciones AS GE ON E.Grupo = GE.Id ORDER BY E.Grupo, E.Id";
		
		$result = mysql_query ($query, $mysql_con);
		
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			
			printf ("<td>%s</td><td>%s</td>", $object->Descripcion, $object->Grupo);
			
			if ($object->Exclusiva == 1) {
				printf ("<td>Sí</td>");
			} else {
				printf ("<td>No</td>");
			}
			
			if ($object->Estado == 'time') {
				echo "<td>Por fechas</td>";
				printf ("<td>%s</td>", strftime ("%a %e %h %Y a las %H:%M", $object->Apertura));
				printf ("<td>%s</td>\n", strftime ("%a %e %h %Y a las %H:%M", $object->Cierre));
			} else {
				if ($object->Estado == 'open') {
					echo "<td>Abierta</td>";
				} else {
					echo "<td>Cerrada</td>";
				}
				echo "<td>No aplica</td><td>No aplica</td>\n";
			}
			
			/* Extraordinario ya *NO* es un caso especial */
			printf ("<td><a href=\"edit_eval.php?id=%s\"><img class=\"icon\" src=\"../img/properties.png\" alt=\"editar\" /></a>", $object->Id);
		
			printf ("<a href=\"eliminar_eval.php?id=%s\"\n", $object->Id);
			printf (" onclick=\"return confirmarDrop(this, '¿Realmente desea eliminar %s?')\">", $object->Descripcion);
			printf ("<img class=\"icon\" src=\"../img/remove.png\" alt=\"eliminar\" /></a></td>");
			
			echo "</tr>\n";
		}
	?>
	</tbody></table>
	<ul><li><a href="nueva_eval.php">Nueva forma de evaluación</a></li></ul>
</body>
</html>
