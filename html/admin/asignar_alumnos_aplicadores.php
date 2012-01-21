<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['asignar_aplicadores']) || $_SESSION['permisos']['asignar_aplicadores'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	
	if (!isset ($_GET['id'])) {
		header ("Location: aplicadores_general.php?e=unknown");
		exit;
	}
	
	settype ($_GET['id'], 'integer');
	
	require_once '../mysql-con.php';
	
	/* SELECT * FROM Salones_Aplicadores AS SA INNER JOIN Evaluaciones as E ON SA.Tipo = E.Id INNER JOIN Materias AS M ON SA.Materia = M.Clave WHERE Id = 1 */
	$query = sprintf ("SELECT SA.Id, M.Clave, M.Descripcion, SA.Tipo, E.Descripcion AS Evaluacion, SA.Salon, UNIX_TIMESTAMP (SA.FechaHora) AS FechaHora, SA.Maestro FROM Salones_Aplicadores AS SA INNER JOIN Evaluaciones as E ON SA.Tipo = E.Id INNER JOIN Materias AS M ON SA.Materia = M.Clave WHERE SA.Id = '%s'", $_GET['id']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: aplicadores_general.php?e=noexiste");
		exit;
	}
	
	$datos = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	if (!is_null ($datos->Maestro)) {
		$query = sprintf ("SELECT Codigo, Nombre, Apellido FROM Maestros WHERE Codigo = '%s'", $datos->Maestro);
		$result = mysql_query ($query, $mysql_con);
		
		if (mysql_num_rows ($result) == 0) {
			header ("Location: aplicadores_general.php?e=maestro");
			exit;
		}
		
		$maestro = mysql_fetch_object ($result);
		mysql_free_result ($result);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<link rel="stylesheet" type="text/css" href="../css/theme.css" />
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script language="javascript" type="text/javascript">
		$(document).ready(function(){
			$('#btn_busqueda').click(function() {
				var txt = $('#txt_busqueda').val ();
				var mat = $('#j_materia').val();
				
				if (txt == "" || txt == null) {
					$('#disponibles').empty ();
					$('#disponibles').append ($("<optgroup></optgroup>").attr("label", "Demasiados alumnos para ser mostrados"));
					return;
				}
				$.getJSON("json.php",
				{
					modo: 'grupos',
					materia: mat,
					bus: txt
				},
				function(data) {
					$('#disponibles').empty ();
					$('#disponibles').append ($("<optgroup>").attr("label", "Búsqueda para " + txt));
					$.each(data, function(i,item){
						$('#disponibles').append ($("<option></option>").attr("value", item.Alumno).text(item.Apellido + " " + item.Nombre + " (" + item.Alumno + ")"));
					});
					$('#disponibles').append ($("</optgroup>"));
				});
			});
		});
	</script>
	<script language="javascript" type="text/javascript">
		function agregar () {
			var disponibles = document.getElementById ("disponibles");
			var alumnos = document.getElementById ("alumnos");
			var num = disponibles.options[disponibles.selectedIndex].value;
			
			for (i = 0; i < alumnos.length; i++) {
				if (alumnos.options[i].value == num) return; /* Item duplicado */
			}
			var nueva_opc = document.createElement ("option");
			nueva_opc.text = disponibles.options[disponibles.selectedIndex].text;
			nueva_opc.value = num;
			alumnos.add (nueva_opc, null);
		}
		
		function eliminar () {
			var alumnos = document.getElementById ("alumnos");
			if (alumnos.options.length == 0) return;
			alumnos.remove (alumnos.selectedIndex);
		}
		
		function validar () {
			var lista_al = document.getElementById ("lista_al");
			var alumnos = document.getElementById ("alumnos");
			
			if (alumnos.options.length == 0) {
				alert ("No alumnos seleccionados");
				return false;
			}
			
			for (i = 0; i < alumnos.length; i++) {
				lista_al.innerHTML += "<input type=\"hidden\" name=\"alumno[]\" value=\"" + alumnos.options[i].value + "\" />";
			}
			
			return true;
		}
	</script>
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
</head>
<body>
	<h1>Seleccionar alumnos</h1>
	<form method="POST" action="post_aplicadores.php" onsubmit="return validar()">
	<?php
		printf ("<input type=\"hidden\" name=\"id\" value=\"%s\" />", $datos->Id);
		printf ("<p>Materia: %s %s</p><input type=\"hidden\" id=\"j_materia\" value=\"%s\" />\n", $datos->Clave, $datos->Descripcion, $datos->Clave);
		printf ("<p>Evaluación: %s</p>\n", $datos->Evaluacion);
		
		printf ("<p>Fecha y hora seleccionada: %s</p>\n", strftime ("%a %e %h %Y a las %H:%M", $datos->FechaHora));
		printf ("<p>Salón: %s</p>\n", $datos->Salon);
		
		if (is_null ($datos->Maestro)) {
			echo "<p>Maestro a cargo: <b>Pendiente</b>\n";
		} else {
			printf ("<p>Maestro a cargo: %s %s</p>\n", $maestro->Apellido, $maestro->Nombre, $maestro->Codigo);
		}
		
		echo "<table border=\"1\"><tbody>";
		
		echo "<tr><td><select id=\"alumnos\" size=\"20\"><optgroup label=\"Alumnos en este salón\">";
		
		$query = sprintf ("SELECT A.Alumno, Al.Nombre, Al.Apellido FROM Alumnos_Aplicadores AS A INNER JOIN Alumnos AS Al ON A.Alumno = Al.Codigo WHERE A.Id = '%s' ORDER BY Al.Apellido, Al.Nombre", $datos->Id);
		$result = mysql_query ($query, $mysql_con);
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<option value=\"%s\">%s %s</option>\n", $object->Alumno, $object->Nombre, $object->Apellido);
		}
		echo "</optgroup></select></td>";
		
		echo "<td><img id=\"Agregar\" src=\"../img/add2.png\" alt=\"Agregar\" onclick=\"return agregar ()\" /><img id=\"Eliminar\" src=\"../img/remove2.png\" alt=\"Eliminar\" onclick=\"return eliminar ()\" /></td>\n";
		
		echo "<td><select id=\"disponibles\" size=\"20\"><optgroup label=\"Demasiados alumnos para ser mostrados\"></optgroup></select></td></tr>";
		
		echo "<tr><td colspan=\"3\"><input type=\"text\" id=\"txt_busqueda\" /><br /><input type=\"button\" id=\"btn_busqueda\" value=\"Buscar\" /></td></tr>";
		
		echo "</tbody></table>";
	?>
	<span id="lista_al"></span>
	<input type="submit" value="Agregar alumnos" />
	</form>
</body>
</html>
