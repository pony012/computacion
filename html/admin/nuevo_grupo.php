<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	/* Luego verificar si tiene el permiso de gestionar usuarios */
	if (!isset ($_SESSION['permisos']['crear_grupos']) || $_SESSION['permisos']['crear_grupos'] != 1) {
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
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
	
	<script language="javascript" type="text/javascript">
		
	</script>
</head>
<body>
	<h1>Agregar una nueva sección</h1>
	<form action="" method="POST">
	<p>Nrc:<input name="nrc" id="nrc" type="text" length="5" /></p>
	<?php
		require_once "../mysql-con.php";
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
		echo "<p>Sección:<input name=\"seccion\" id=\"seccion\" type=\"text\" length=\"3\" /></p>\n";
		echo "<p>Maestro:<select name=\"maestro\" id=\"maestro\">\n";
		
		$query = "SELECT Codigo, Nombre, Apellido FROM Maestros";
		
		$result = mysql_query ($query, $mysql_con);
		
		while (($object = mysql_fetch_object ($result))) {
			echo "<option value=\"".$object->Codigo."\">";
			echo $object->Apellido . " " . $object->Nombre;
			echo "</option>\n";
		}
		
		echo "</select></p>\n";
		
		mysql_close ($mysql_con);
	?>
	<p><input type="radio" name="depas" id="depas" checked="checked" value="0" />La materia no tiene departamentales<br />
	<input type="radio" name="depas" id="depas" value="1" />La materia tiene un departamental<br />
	<input type="radio" name="depas" id="depas" value="2" />La materia tiene dos departamentales</p>
	
	<span id="j_puntos_depa1" style="visibility:hidden">Porcentaje del primer departamental:
	<input name="puntos_depa1" id="puntos_depa1" type="text" />
	</span>
	<span id="j_puntos_depa2" style="visibility:hidden"><br />Porcentaje del segundo departamental: 
	<input name="puntos_depa2" id="puntos_depa2" type="text" />
	</span>
	
	<p><input name="puntos" id="puntos" type="checkbox" value="D" onchange="ocultar_puntos_maestro ()" />El maestro asigna puntos en esta materia</p>
	<span id="j_puntos" style="visibility:hidden">Porcentaje de los punto asignados por el maestro:
	<input name="n_puntos" id="n_puntos" type="text" />
	</span>
	
	</form>
</body>
</html>
