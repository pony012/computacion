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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<link rel="stylesheet" type="text/css" href="../css/theme.css" />
	<link rel="stylesheet" media="all" type="text/css" href="../css/smoothness/jquery-ui-1.8.16.custom.css" />
	<style type="text/css">
	/* css for timepicker */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
ui-datepicker-div, .ui-datepicker{ font-size: 80%; }
	</style>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../scripts/jquery-ui-timepicker-addon.js"></script>
	<script type="text/javascript" src="../scripts/ui-timepicker-es.js"></script>
	<script language="javascript" type="text/javascript">
		$(document).ready(function(){
			$('#SFecha').datetimepicker({
				dateFormat: 'D dd M yy',
				timeFormat: 'hh:mm',
				separator: ' a las ',
				stepMinute: 15
			});
			$('#SFecha').datetimepicker('setDate', (new Date()));
			
			$('#materia').change(function() {
				if ($('#materia').val() == 'NULL') {
					$('#evaluacion').empty ();
					$('#evaluacion').append ($("<option></option>").attr("value", "NULL").text("Selecciona una materia primero"));
					$('#aprox').val("indefinido");
					return;
				}
				$.getJSON("json.php",
				{
					modo: 'evals',
					materia: $('#materia').val(),
					exclusiva: '0'
				},
				function(data) {
					$('#evaluacion').empty ();
					$('#evaluacion').append ($("<option></option>").attr("value", "NULL").attr("selected", "selected").text("Seleccione una forma de evaluación"));
					$.each(data, function(i,item){
						$('#evaluacion').append ($("<option></option>").attr("value", item.Tipo).text(item.Descripcion));
					});
				});
				
				actualizar_aprox ();
			});
			
			$('#no_alumnos').focusout(function() {
				actualizar_aprox ();
			});
		});
	</script>
	<script language="javascript" type="text/javascript">
		function actualizar_aprox () {
			if (document.getElementById ('grupos').checked) return;
			
			if ($('#materia').val() == 'NULL') {
				$('#aprox').val("indefinido");
				return;
			}
			
			var num = parseInt ($('#no_alumnos').val ());
			
			if (!isNaN (num) && num > 0) {
				/* Calcular el aproximado de salones */
				$.getJSON ("json.php",
				{
					modo: 'count',
					tipo: 'alumnos',
					materia: $('#materia').val()
				},
				function (data) {
					var n_salones = Math.ceil (data.TOTAL / num);
					$('#aprox').val(n_salones);
				});
			} else {
				$('#aprox').val("indefinido");
				$('#no_alumnos').val (0);
			}
		}
		
		function por_grupos () {
			if (document.getElementById ('grupos').checked) {
				document.getElementById ('no_alumnos').disabled = true;
				document.getElementById ('aprox').disabled = true;
				document.getElementById ('aprox').value = "No aplica";
				
				document.getElementById ('disponibles').disabled = true;
				document.getElementById ('maestros').disabled = true;
			}
		}
		
		function por_otros () {
			if (!document.getElementById ('grupos').checked) {
				document.getElementById ('no_alumnos').disabled = false;
				document.getElementById ('aprox').disabled = false;
				actualizar_aprox ();
				
				document.getElementById ('disponibles').disabled = false;
				document.getElementById ('maestros').disabled = false;
			}
		}
		
		function validar () {
			if (document.getElementById ("materia").value == "NULL" || document.getElementById ("evaluacion").value == "NULL") {
				alert ("No ha seleccionado una materia, o forma de evaluación");
				return false;
			}
			if (!document.getElementById ('grupos').checked) {
				var no_al = parseInt (document.getElementById ("no_alumnos").value);
			
				if (isNaN (no_al) || no_al < 10) {
					/* El número de alumnos por salón es inválido */
					alert ("El número de alumnos por salón es inválido\n10 es el número mínimo");
					return false;
				}
			}
			var fecha = $('#SFecha').datetimepicker('getDate');
			
			if (fecha == null) {
				alert ("Fecha es null");
				return false;
			}
			
			var tf = fecha.getTime() / 1000;
			
			document.getElementById ("fecha").value = parseInt (tf);
			
			var lista_m = document.getElementById ("lista_m");
			var maestros = document.getElementById ("maestros");
			
			for (i = 0; i < maestros.length; i++) {
				lista_m.innerHTML += "<input type=\"hidden\" name=\"maestro[]\" value=\"" + maestros.options[i].value + "\" /\>";
			}
			
			return true;
		}
		
		function agregar () {
			var disponibles = document.getElementById ("disponibles");
			var maestros = document.getElementById ("maestros");
			var num = disponibles.options[disponibles.selectedIndex].value;
			
			for (i = 0; i < maestros.length; i++) {
				if (maestros.options[i].value == num) return; /* Item duplicado */
			}
			var nueva_opc = document.createElement ("option");
			nueva_opc.text = disponibles.options[disponibles.selectedIndex].text;
			nueva_opc.value = num;
			maestros.add (nueva_opc, null);
		}
		
		function eliminar () {
			var maestros = document.getElementById ("maestros");
			if (maestros.options.length == 0) return;
			maestros.remove (maestros.selectedIndex);
		}
	</script>
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
</head>
<body>
	<h1>Cálculo de salones automático</h1>
	<form action="post_auto_salones.php" method="post" onsubmit="return validar ()">
	<?php
		require_once '../mysql-con.php';
		
		/* SELECT DISTINCT P.Clave, M.Descripcion FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id INNER JOIN Materias AS M ON P.Clave = M.Clave WHERE E.Exclusiva = 0 */
		$query = "SELECT DISTINCT P.Clave, M.Descripcion FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id INNER JOIN Materias AS M ON P.Clave = M.Clave WHERE E.Exclusiva = 0";
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<p>Materia: <select name=\"materia\" id=\"materia\">\n";
		echo "<option value=\"NULL\" selected=\"selected\">Seleccione una materia</option>\n";
		while (($object = mysql_fetch_object ($result))) {
			printf ("<option value=\"%s\">%s - %s</option>\n", $object->Clave, $object->Clave, $object->Descripcion);
		}
		echo "</select></p>";
		mysql_free_result ($result);
	?>
	<p>Evaluación: <select name="evaluacion" id="evaluacion"><option value="NULL" selected="selected">Seleccione una materia primero</option></select></p>
	<p>Fecha y hora de aplicación:<input type="text" id="SFecha" /><input type="hidden" id="fecha" name="fecha" /></p>
	<p>Ordernar los alumnos:</p>
	<input type="radio" name="select_order" id="order" value="order" checked="checked" onchange="por_otros ()" /><label for="order">Alfabeticamente</label><br />
	<input type="radio" name="select_order" id="random" value="random" onchange="por_otros ()" /><label for="random">Aleatoriamente</label><br />
	<input type="radio" name="select_order" id="grupos" value="grupos" onchange="por_grupos ()" /><label for="grupos">Por grupos</label><br />
	<p>Número de alumnos por salón: <input type="text" id="no_alumnos" name="no_alumnos" value="20" /></p>
	<p>Cantidad de salones a utilizar: <input type="text" id="aprox" readonly="readonly" value="indefinido" /></p>
	<p>Preasignar maestros:<br />
	<?php
		$query = "SELECT Codigo, Nombre, Apellido FROM Maestros ORDER BY Apellido, Nombre";
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<select id=\"disponibles\">\n";
		while (($object = mysql_fetch_object ($result))) {
			printf ("<option value=\"%s\">%s %s</option>\n", $object->Codigo, $object->Apellido, $object->Nombre);
		}
		echo "</select>";
		mysql_free_result ($result);
	?><img id="Agregar" src="../img/add2.png" alt="Agregar" onclick="return agregar ()" /></p>
	<select id="maestros" size="20"><optgroup label="Maestros seleccionados" ></optgroup></select><img id="Eliminar" src="../img/remove2.png" alt="Eliminar" onclick="return eliminar ()" />
	<span id="lista_m"></span>
	<p><input type="submit" value="Generar salones" />
	<input type="submit" value="Generar salones y editar" /></p>
	</form>
