<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['crear_materias']) || $_SESSION['permisos']['crear_materias'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	
	/* Validar la clave la materia */
	if (!isset ($_GET['clave']) || !preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_GET['clave'])) {
		header ("Location: materias.php?e=clave");
		exit;
	}
	
	require_once '../mysql-con.php';
		
	$query = "SELECT * FROM Materias WHERE Clave='". $_GET['clave'] ."' LIMIT 1";
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: materias.php?e=noexiste");
		mysql_free_result ($result);
		mysql_close ($mysql_con);
		exit;
	}
	
	$object = mysql_fetch_object ($result);
	mysql_free_result ($result);
	mysql_close ($mysql_con);
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
		function habilita_depa1 () {
			if (document.getElementById ("depa1").checked) {
				document.getElementById ("porcentaje_depa1").disabled = false;
			} else {
				document.getElementById ("porcentaje_depa1").value = 0;
				document.getElementById ("porcentaje_depa1").disabled = true;
			}
		}
		
		function habilita_depa2 () {
			if (document.getElementById ("depa2").checked) {
				document.getElementById ("porcentaje_depa2").disabled = false;
			} else {
				document.getElementById ("porcentaje_depa2").value = 0;
				document.getElementById ("porcentaje_depa2").disabled = true;
			}
		}
		
		function habilita_puntos () {
			if (document.getElementById ("puntos").checked) {
				document.getElementById ("porcentaje_puntos").disabled = false;
			} else {
				document.getElementById ("porcentaje_puntos").value = 0;
				document.getElementById ("porcentaje_puntos").disabled = true;
			}
		}
	</script>
	<script language="javascript" type="text/javascript">
		function validar () {
			var n_1 = parseInt (document.getElementById ("porcentaje_depa1").value);
			var n_2 = parseInt (document.getElementById ("porcentaje_depa2").value);
			var n_p = parseInt (document.getElementById ("porcentaje_puntos").value);
			
			if (n_1 <= 0 || isNaN (n_1)) {
				document.getElementById ("porcentaje_depa1").value = 0;
				document.getElementById ("porcentaje_depa1").disabled = true;
				document.getElementById ("depa1").checked = false;
				n_1 = 0;
			}
			
			if (n_2 <= 0 || isNaN (n_2)) {
				document.getElementById ("porcentaje_depa2").value = 0;
				document.getElementById ("porcentaje_depa2").disabled = true;
				document.getElementById ("depa2").checked = false;
				n_2 = 0;
			}
			
			if (n_p <= 0 || isNaN (n_p)) {
				document.getElementById ("porcentaje_puntos").value = 0;
				document.getElementById ("porcentaje_puntos").disabled = true;
				document.getElementById ("puntos").checked = false;
				n_p = 0;
			}
			
			var suma = n_1 + n_2 + n_p;
			
			if (suma != 100) {
				alert ("El porcentaje es incorrecto");
				return false;
			}
			
			return true;
		}
	</script>
</head>
<body>
	<h1>Editar materia</h1>
	<form action="post_materia.php" method="POST" onsubmit="return validar ()" >
	<input type="hidden" name="modo" value="editar" />
	<?php
		echo "<p>Clave de la materia: ".$object->Clave."</p>";
		echo "<input type=\"hidden\" name=\"clave\" id=\"clave\" length=\"5\" value=\"".$_GET['clave']."\" />";
		
		echo "<p>Descripción: <input type=\"text\" name=\"descripcion\" id=\"descripcion\" length=\"100\" value=\"".$object->Descripcion."\" /></p>";
	
		echo "<h2>Forma de evaluación de la materia</h2>";
		
		if ($object->Depa1 == "0") {
			echo "<p><input type=\"checkbox\" value=\"1\" name=\"depa1\" id=\"depa1\" onchange=\"habilita_depa1 ()\" />
			<label for=\"depa1\">Tiene Departamental 1</label><br />
			Porcentaje: <input type=\"text\" value=\"0\" disabled=\"disabled\" name=\"porcentaje_depa1\" id=\"porcentaje_depa1\" /></p>";
		} else {
			echo "<p><input type=\"checkbox\" value=\"1\" checked=\"checked\" name=\"depa1\" id=\"depa1\" onchange=\"habilita_depa1 ()\" />
			<label for=\"depa1\">Tiene Departamental 1</label><br />
			Porcentaje: <input type=\"text\" value=\"".$object->Porcentaje_Depa1."\" name=\"porcentaje_depa1\" id=\"porcentaje_depa1\" /></p>";
		}
		
		if ($object->Depa2 == "0") {
			echo "<p><input type=\"checkbox\" value=\"1\" name=\"depa2\" id=\"depa2\" onchange=\"habilita_depa2 ()\" />
			<label for=\"depa2\">Tiene Departamental 2</label><br />
			Porcentaje: <input type=\"text\" value=\"0\" disabled=\"disabled\" name=\"porcentaje_depa2\" id=\"porcentaje_depa2\" /></p>";
		} else {
			echo "<p><input type=\"checkbox\" value=\"1\" checked=\"checked\" name=\"depa2\" id=\"depa2\" onchange=\"habilita_depa2 ()\" />
			<label for=\"depa2\">Tiene Departamental 2</label><br />
			Porcentaje: <input type=\"text\" value=\"".$object->Porcentaje_Depa2."\" name=\"porcentaje_depa2\" id=\"porcentaje_depa2\" /></p>";
		}
		
		if ($object->Puntos == "0") {
			echo "<p><input type=\"checkbox\" value=\"1\" name=\"puntos\" id=\"puntos\" onchange=\"habilita_puntos ()\" />
			<label for=\"puntos\">Otras Ponderaciones<label><br />
			Porcentaje: <input type=\"text\" value=\"0\" disabled=\"disabled\" name=\"porcentaje_puntos\" id=\"porcentaje_puntos\" /></p>";
		} else {
			echo "<p><input type=\"checkbox\" value=\"1\" checked=\"checked\" name=\"puntos\" id=\"puntos\" onchange=\"habilita_puntos ()\" />
			<label for=\"puntos\">Otras Ponderaciones<label><br />
			Porcentaje: <input type=\"text\" value=\"".$object->Porcentaje_Puntos."\" name=\"porcentaje_puntos\" id=\"porcentaje_puntos\" /></p>";
		}
	?>
	<p><input type="submit" value="Modificar materia" /></p>
	</form>
</body>
</html>