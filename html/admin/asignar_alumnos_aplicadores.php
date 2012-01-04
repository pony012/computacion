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
			$('#boton_bus').click(function() {
				var txt = $('#busqueda').val ();
				var mat = $('#materia').val();
				alert ("Materia: " + mat);
				alert ("Texto a buscar:" + txt);
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
					alert ("Json dice sí");
					alert (data);
					$('#disponibles').empty ();
					$.each(data, function(i,item){
						if (i < 5) alert (item);
						$('#disponibles').append ($("<option></option>").attr("value", item.Alumno).text(item.Apellido + " " + item.Nombre));
					});
				});
			});
		});
	</script>
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
</head>
<body>
	<h1>Seleccionar alumnos</h1>
	<?php
		printf ("<p>Materia: %s %s</p><input type=\"hidden\" id=\"materia\" name=\"materia\" value=\"%s\" />\n", $datos->Clave, $datos->Descripcion, $datos->Clave);
		printf ("<p>Evaluación: %s</p><input type=\"hidden\" name=\"evaluacion\" value=\"%s\" />\n", $datos->Evaluacion, $datos->Tipo);
		
		$tiempo_fecha = $_GET['fecha'] - ($_GET['fecha'] % 900);
		printf ("<p>Fecha y hora seleccionada: %s</p><input type=\"hidden\" name=\"fechahora\" value=\"%s\" />\n", strftime ("%a %e %h %Y a las %H:%M", $tiempo_fecha), $tiempo_fecha);
		printf ("<p>Salón: %s</p><input type=\"hidden\" name=\"salon\" value=\"%s\" />\n", $_GET['salon'], $_GET['salon']);
		printf ("<p>Maestro a cargo: %s %s</p><input type=\"hidden\" name=\"maestro\" value=\"%s\" />\n", $maestro->Apellido, $maestro->Nombre, $maestro->Codigo);
		
		echo "<table border=\"1\"><tbody>";
		
		echo "<tr><td><select size=\"20\"><optgroup label=\"Alumnos en este salón\">";
		
		$query = sprintf ("SELECT A.Alumno, Al.Nombre, Al.Apellido FROM Aplicadores AS A INNER JOIN Alumnos AS Al ON A.Alumno = Al.Codigo WHERE A.Materia = '%s' AND A.Salon = '%s' AND A.FechaHora = FROM_UNIXTIMESTAMP(%s) AND A.Tipo = '%s' ORDER BY Al.Apellido, Al.Nombre", $datos->Clave, mysql_real_escape_string ($_GET['salon']), $tiempo_fecha, $datos->Tipo);
		$result = mysql_query ($query, $mysql_con);
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<option value=\"%s\">%s %s</option>\n", $object->Alumno, $object->Nombre, $object->Apellido);
		}
		echo "</optgroup></select></td>";
		
		echo "<td>Botón más, botón menos</td>";
		
		echo "<td><select id=\"disponibles\" size=\"20\"><optgroup label=\"Demasiados alumnos para ser mostrados\"></optgroup></select></td></tr>";
		
		echo "<tr><td colspan=\"3\"><input type=\"text\" id=\"busqueda\" /><br /><input type=\"button\" id=\"boton_bus\" value=\"Buscar\" /></td></tr>";
		
		echo "</tbody></table>";
	?>
</body>
</html>
