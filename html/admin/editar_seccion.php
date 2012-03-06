<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	/* Luego verificar si tiene el permiso de crear grupos */
	if (!has_permiso ('crear_grupos')) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
	
	if (!isset ($_GET['nrc']) || !preg_match ("/^([0-9]){1,5}$/", $_GET['nrc'])) {
		agrega_mensaje (3, "Error desconocido");
		header ("Location: secciones.php");
		exit;
	}
	database_connect ();
	
	$query = sprintf ("SELECT sec.*, m.descripcion FROM Secciones AS sec INNER JOIN Materias AS m ON sec.Materia = m.Clave WHERE Nrc='%s' LIMIT 1", mysql_real_escape_string ($_GET['nrc']));
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: secciones.php");
		agrega_mensaje (1, "El nrc especificado no existe");
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
	<title><?php echo $cfg['nombre']; ?></title>
</head>
<body>
	<h1>Editar una sección</h1>
	<p>Por motivos de seguridad, sólo se puede modificar el maestro que imparte esta sección</p>
	<form action="post_editar_seccion.php" method="post">
	<?php
		echo "<p>Nrc: ".$object->Nrc."</p>";
		echo "<input type=\"hidden\" value=\"".$object->Nrc."\" name=\"nrc\" />";
		echo "<p>Materia: ".$object->Materia." - ".$object->descripcion."</p>";
		
		echo "<p>Sección: ".$object->Seccion."</p>";
		
		echo "<p>Maestro:<select name=\"maestro\" id=\"maestro\">\n";
		
		database_connect ();
		
		$query = "SELECT Codigo, Nombre, Apellido FROM Maestros";
		
		$result = mysql_query ($query, $mysql_con);
		
		while (($maestro = mysql_fetch_object ($result))) {
			if ($object->Maestro == $maestro->Codigo) {
				echo "<option value=\"".$maestro->Codigo."\" selected=\"selected\" >";
			} else {
				echo "<option value=\"".$maestro->Codigo."\">";
			}
			echo $maestro->Apellido . " " . $maestro->Nombre;
			echo "</option>\n";
		}
		
		echo "</select></p>\n";
		mysql_free_result ($result);
	?>
	<input type="submit" value="Enviar" />
	</form>
</body>
</html>
