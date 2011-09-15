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
	<style type="text/css">
	.oculto {	
		display:none;
	}
	</style>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	<script language="javascript" type="text/javascript">
		function ocultar_depas () {
			var j_depa = document.getElementsByName("depas");
			
			for (g = 0; g < j_depa.length; g++) {
				if (j_depa[g].checked) {
					if (g == 0) {
						/* La primera casilla de verificación, sin depas */
						$("#j_depa_1").slideUp("fast");
						$("#j_depa_2").slideUp("fast");
					} else if (g == 1) {
						$("#j_depa_1").slideDown("fast");
						$("#j_depa_2").slideUp("fast");
					} else if (g == 2) {
						$("#j_depa_1").slideDown("fast");
						$("#j_depa_2").slideDown("fast");
					}
				}
			}
		}
		
		function ocultar_puntos () {
			if (document.getElementById ("puntos").checked) {
				$("#j_puntos").stop (false, true);
				$("#j_puntos").slideDown("fast");
			} else {
				$("#j_puntos").stop (false, true);
				$("#j_puntos").slideUp("fast");
			}
		}
	</script>
</head>
<body>
	<h1>Agregar una nueva sección</h1>
	<form action="" method="post">
	<p>Nrc:<input name="nrc" id="nrc" type="text" /></p>
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
		
		mysql_close ($mysql_con);
	?>
	<p><input type="radio" name="depas" checked="checked" value="0" onchange="ocultar_depas ()" />La materia no tiene departamentales<br />
	<input type="radio" name="depas" value="1" onchange="ocultar_depas ()" />La materia tiene un departamental<br />
	<input type="radio" name="depas" value="2" onchange="ocultar_depas ()" />La materia tiene dos departamentales</p>
	
	<div class="oculto" id="j_depa_1">Porcentaje del primer departamental:
	<input name="puntos_depa1" id="puntos_depa1" type="text" />
	</div>
	<div class="oculto" id="j_depa_2">Porcentaje del segundo departamental: 
	<input name="puntos_depa2" id="puntos_depa2" type="text" />
	</div>
	
	<p><input name="puntos" id="puntos" type="checkbox" value="1" onchange="ocultar_puntos ()" />El maestro asigna puntos en esta materia</p>
	<div class="oculto" id="j_puntos">Porcentaje de los punto asignados por el maestro:
	<input name="n_puntos" id="n_puntos" type="text" />
	</div>
	
	</form>
</body>
</html>
