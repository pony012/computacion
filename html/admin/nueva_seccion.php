<?php
	require_once 'session_maestro.php';
	
	/* Luego verificar si tiene el permiso de crear grupos */
	if (!has_permiso ('crear_grupos')) {
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
	<title><?php echo $cfg['nombre']; ?></title>
	<script language="javascript" type="text/javascript">
		// <![CDATA[
		function validar () {
			/* Hay que validar varias cosas
			 * Primero, que el nrc sean sólo numeros y de longitud 5 */
			var j_nrc = document.getElementById ("nrc").value;
			
			if (!/^([0-9])+$/.test(j_nrc) || j_nrc.length > 5) {
				alert ("Nrc no es un número");
				return false;
			}
			
			/* Validar la seccion */
			var j_sec = document.getElementById ("seccion").value;
			
			if (!/^([Dd])([0-9]){2}$/.test(j_sec)) {
				alert ("Sección no válida");
				return false;
			}
			
			/* Convertir la sección a mayúculas */
			document.getElementById ("seccion").value = j_sec.toUpperCase();
			
			return true;
		}
		// ]]>
	</script>
</head>
<body>
	<h1>Agregar una nueva sección</h1>
	<form action="post_nueva_seccion.php" method="post" onsubmit="return validar()">
	<p>Nrc:<input name="nrc" id="nrc" type="text" /></p>
	<?php
		database_connect ();
		/* Listar todas las materias */
		echo "<p>Materia:<select name=\"materia\" id=\"materia\">\n";
		$query = "SELECT * FROM Materias";
		
		$result = mysql_query ($query, $mysql_con);
		
		while (($object = mysql_fetch_object ($result))) {
			echo "<option value=\"".$object->Clave."\">";
			echo $object->Clave . " - " . $object->Descripcion;
			echo "</option>\n";
		}
		mysql_free_result ($result);
		
		echo "</select></p>\n";
		echo "<p>Sección:<input name=\"seccion\" id=\"seccion\" type=\"text\" /></p>\n";
		echo "<p>Maestro:<select name=\"maestro\" id=\"maestro\">\n";
		
		$query = "SELECT Codigo, Nombre, Apellido FROM Maestros";
		
		$result = mysql_query ($query, $mysql_con);
		
		while (($object = mysql_fetch_object ($result))) {
			echo "<option value=\"".$object->Codigo."\">";
			echo $object->Apellido . " " . $object->Nombre;
			echo "</option>\n";
		}
		
		echo "</select></p>\n";
		
		mysql_free_result ($result);
	?>
	<input type="submit" value="Enviar" />
	</form>
</body>
</html>
