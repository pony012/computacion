<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	/* Luego verificar si tiene el permiso de crear grupos */
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
	<script language="javascript" type="text/javascript">
		function desactivar_puntos () {
			if (document.getElementById ("tiene_puntos").checked) {
				document.getElementById ("n_puntos").value = 0;
				document.getElementById ("n_puntos").disabled = true;
			} else {
				document.getElementById ("n_puntos").disabled = false;
			}
		}
		
		function desactivar_depa1 () {
			if (document.getElementById ("tiene_depa1").checked) {
				document.getElementById ("puntos_depa1").value = 0;
				document.getElementById ("puntos_depa1").disabled = true;
			} else {
				document.getElementById ("puntos_depa1").disabled = false;
			}
		}
		
		function desactivar_depa2 () {
			if (document.getElementById ("tiene_depa2").checked) {
				document.getElementById ("puntos_depa2").value = 0;
				document.getElementById ("puntos_depa2").disabled = true;
			} else {
				document.getElementById ("puntos_depa2").disabled = false;
			}
		}
		
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
			
			var j_n_puntos = document.getElementById("n_puntos").value;
			var j_puntos_depa1 = document.getElementById("puntos_depa1").value;
			var j_puntos_depa2 = document.getElementById("puntos_depa2").value;
			
			/* TODO: verificar la suma de puntos */
			return true;
		}
	</script>
</head>
<body>
	<h1>Agregar una nueva sección</h1>
	<form action="" method="get" onsubmit="return validar()">
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
	<!-- TODO: Meter en una tabla, por favor -->
	<p>Porcentaje de los punto asignados por el maestro: <input name="n_puntos" id="n_puntos" value="50" type="text" /><input name="tiene_puntos" id="tiene_puntos" type="checkbox" onchange="desactivar_puntos ()" />El maestro no asigna puntos en esta materia</p>
	<p>Porcentaje del primer departamental:<input name="puntos_depa1" id="puntos_depa1" value="25" type="text" /><input name="tiene_depa1" id="tiene_depa1" type="checkbox" onchange="desactivar_depa1 ()" />La materia no tiene departamental</p>
	<p>Porcentaje del segundo departamental:<input name="puntos_depa2" id="puntos_depa2" value="25" type="text" /><input name="tiene_depa2" id="tiene_depa2" type="checkbox" onchange="desactivar_depa2 ()" />La materia no tiene segundo departamental</p>
	
	<input type="submit" value="Enviar" />
	</form>
</body>
</html>
