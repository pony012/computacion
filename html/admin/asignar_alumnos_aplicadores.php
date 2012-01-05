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
	
	if (!preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_GET['materia'])) {
		header ("Location: aplicadores.php?e=clave");
		exit;
	}
	
	if (!preg_match ("/^([0-9]){1,7}$/", $_GET['maestro'])) {
		header ("Location: aplicadores.php?e=maestro");
		exit;
	}
	
	settype ($_GET['fecha'], 'integer');
	settype ($_GET['evaluacion'], 'integer');
	
	require_once '../mysql-con.php';
	
	/* SELECT * FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id INNER JOIN Materias AS M ON P.Clave = M.Clave */
	$query = sprintf ("SELECT E.Descripcion AS Evaluacion, M.Descripcion, P.Clave, P.Tipo FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id INNER JOIN Materias AS M ON P.Clave = M.Clave WHERE P.Tipo='%s' AND P.Clave='%s' AND E.Exclusiva = 0", $_GET['evaluacion'], $_GET['materia']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: aplicadores.php?e=noexiste");
		exit;
	}
	
	$datos = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	$query = sprintf ("SELECT Codigo, Nombre, Apellido FROM Maestros WHERE Codigo = '%s'", $_GET['maestro']);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: aplicadores.php?e=maestro");
		exit;
	}
	
	$maestro = mysql_fetch_object ($result);
	mysql_free_result ($result);
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
		printf ("<p>Materia: %s %s</p><input type=\"hidden\" id=\"j_materia\" name=\"materia\" value=\"%s\" />\n", $datos->Clave, $datos->Descripcion, $datos->Clave);
		printf ("<p>Evaluación: %s</p><input type=\"hidden\" name=\"evaluacion\" value=\"%s\" />\n", $datos->Evaluacion, $datos->Tipo);
		
		$tiempo_fecha = $_GET['fecha'] - ($_GET['fecha'] % 900);
		printf ("<p>Fecha y hora seleccionada: %s</p><input type=\"hidden\" name=\"fechahora\" value=\"%s\" />\n", strftime ("%a %e %h %Y a las %H:%M", $tiempo_fecha), $tiempo_fecha);
		printf ("<p>Salón: %s</p><input type=\"hidden\" name=\"salon\" value=\"%s\" />\n", $_GET['salon'], $_GET['salon']);
		printf ("<p>Maestro a cargo: %s %s</p><input type=\"hidden\" name=\"maestro\" value=\"%s\" />\n", $maestro->Apellido, $maestro->Nombre, $maestro->Codigo);
		
		echo "<table border=\"1\"><tbody>";
		
		echo "<tr><td><select id=\"alumnos\" size=\"20\"><optgroup label=\"Alumnos en este salón\">";
		
		$query = sprintf ("SELECT A.Alumno, Al.Nombre, Al.Apellido FROM Aplicadores AS A INNER JOIN Alumnos AS Al ON A.Alumno = Al.Codigo WHERE A.Materia = '%s' AND A.Salon = '%s' AND A.FechaHora = FROM_UNIXTIMESTAMP(%s) AND A.Tipo = '%s' ORDER BY Al.Apellido, Al.Nombre", $datos->Clave, mysql_real_escape_string ($_GET['salon']), $tiempo_fecha, $datos->Tipo);
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
