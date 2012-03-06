<?php require_once 'session_maestro.php'; check_valid_session (); ?>
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
	<h1>Academias</h1>
	<?php
		database_connect ();
		
		$query = "SELECT * FROM Academias";
		
		$result = mysql_query ($query, $mysql_con);
		
		if (has_permiso ('admin_academias')) {
			echo "<form method=\"post\" action=\"permisos_academia.php\"><table border=\"1\"><thead><tr><th></th><th>Nombre</th><th>Presidente</th><th>Subida de calificaciones</th><th>Editar materias</th><th>Acciones</th></tr></thead><tbody>";
		} else {
			echo "<table border=\"1\"><thead><tr><th>Nombre</th><th>Presidente</th></tr></thead><tbody>";
		}
		
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			if (has_permiso ('admin_academias')) {
				printf ("<td><input type=\"checkbox\" name=\"id[]\" value=\"%s\" /></td>", $object->Id);
			}
			printf ("<td><a href=\"ver_academia.php?id=%s\">%s</a></td>", $object->Id, $object->Nombre);
			
			if (is_null ($object->Maestro)) {
				echo "<td><b>Indefinido</b></td>\n";
			} else {
				$query = sprintf ("SELECT Nombre, Apellido FROM Maestros WHERE Codigo = '%s'", $object->Maestro);
				$result_maestro = mysql_query ($query, $mysql_con);
				$maestro = mysql_fetch_object ($result_maestro);
				printf ("<td>%s %s</td>\n", $maestro->Apellido, $maestro->Nombre);
				mysql_free_result ($result_maestro);
			}
			
			if (has_permiso ('admin_academias')) {
				/* Si tiene los permisos de subida */
				if ($object->Subida == 1) {
					echo "<td><img src=\"../img/day.png\" alt=\"activo\" /></td>";
				} else {
					echo "<td><img src=\"../img/night.png\" alt=\"inactivo\" /></td>";
				}
				/* Si tiene edición de materias */
				if ($object->Materias == 1) {
					echo "<td><img src=\"../img/day.png\" alt=\"activo\" /></td>";
				} else {
					echo "<td><img src=\"../img/night.png\" alt=\"inactivo\" /></td>";
				}
				
				$link = array ('tipo' => 'e', 'id' => $object->Id);
				printf ("<td><a href=\"editar_academia.php?%s\"><img class=\"icon\" src=\"../img/properties.png\" alt=\"editar\" /></a>", htmlentities (http_build_query ($link)));
				printf ("<a href=\"eliminar_academia.php?id=%s\" onclick=\"return confirmarDrop(this, '¿Realmente desea eliminar la academia %s?')\"><img class=\"icon\" src=\"../img/remove.png\" alt=\"eliminar\" /></a>", $object->Id, $object->Nombre);
				printf ("<a href=\"permisos_academia.php?id=%s\"><img class=\"icon\" src=\"../img/subida.png\" alt=\"permisos\" /></a></td>", $object->Id);
			}
			echo "</tr>";
		}
		mysql_free_result ($result);?>
		</tbody></table>
		<?php if (has_permiso ('admin_academias')) {
			echo "<input type=\"submit\" value=\"Modificar múltiples\" /></form>"; /* Botón editar y cerrar el formulario */
			echo "<ul><li><a href=\"editar_academia.php?tipo=n\">Nueva academia</a></li></ul>\n";
		} ?>
</body>
</html>
