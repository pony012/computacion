<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	if (!has_permiso ('admin_evaluaciones')) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
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
	<link rel="stylesheet" media="all" type="text/css" href="../css/timepicker.css" />
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../scripts/jquery-ui-timepicker-addon.js"></script>
	<script type="text/javascript" src="../scripts/ui-timepicker-es.js"></script>
	<script language="javascript" type="text/javascript">
		// <![CDATA[
		$(document).ready(function(){
			$('#apertura').datetimepicker({
				dateFormat: 'D dd M yy',
				timeFormat: 'hh:mm',
				separator: ' a las ',
				onClose: function(dateText, inst) {
					if (dateText == '') {
						var testStartDate = new Date ();
						$(this).datetimepicker('setDate', testStartDate);
					} else {
						var testStartDate = $(this).datetimepicker('getDate');
					}
					
					var endDateTextBox = $('#cierre');
					
					if (endDateTextBox.val() == '') {
						endDateTextBox.datetimepicker('setDate', testStartDate);
					} else {
						var testEndDate = endDateTextBox.datetimepicker('getDate');
						if (testStartDate > testEndDate) endDateTextBox.datetimepicker('setDate', dateText);
					}
				}
			});
			$('#cierre').datetimepicker({
				dateFormat: 'D dd M yy',
				timeFormat: 'hh:mm',
				separator: ' a las ',
				onClose: function(dateText, inst) {
					if (dateText == '') {
						var testEndDate = new Date ();
						$(this).datetimepicker('setDate', testEndDate);
					} else {
						var testEndDate = $(this).datetimepicker('getDate');
					}
					
					var startDateTextBox = $('#apertura');
					
					if (startDateTextBox.val() == '') {
						startDateTextBox.datetimepicker('setDate', testEndDate);
					} else {
						var testStartDate = startDateTextBox.datetimepicker('getDate');
						if (testStartDate > testEndDate) startDateTextBox.datetimepicker('setDate', dateText);
					}
				}
			});
		});
		// ]]>
	</script>
	<script language="javascript" type="text/javascript">
		// <![CDATA[
		function validar () {
			var x = document.getElementById ("descripcion").value;
			
			if (x == null || x == "") {
				/* No puede haber una descripcion vacia */
				alert ("Descripcion vacia");
				return false;
			}
			
			var ap = $('#apertura').datetimepicker('getDate');
			var ci = $('#cierre').datetimepicker('getDate');
			var estado = document.getElementById ("estado");
			
			if (estado.value == "time") {
				if (ap == null || ci == null) {
					alert ("Alguna de las fechas está vacía");
					return false;
				}
		
				var t1 = ap.getTime () / 1000;
				var t2 = ci.getTime () / 1000;
		
				if (t2 < t1) {
					/* El lapso es negativo */
					alert ("Se ha especificado un intervalo negativo");
					return false;
				} else if (t1 == t2) {
					alert ("Se ha especificado un intervalo vacio");
					return false;
				}
				
				document.getElementById ("inicio").value = t1;
				document.getElementById ("fin").value = t2;
			} else {
				document.getElementById ("inicio").value = "0";
				document.getElementById ("fin").value = "0";
			}
			
			return true;
		}
		
		function actualizar_cajas () {
			var estado = document.getElementById ("estado");
			var apertura = document.getElementById ("apertura");
			var cierre = document.getElementById ("cierre");
			
			if (estado.value == "time") {
				apertura.disabled = false;
				cierre.disabled = false;
			} else {
				apertura.disabled = true;
				cierre.disabled = true;
			}
		}
		// ]]>
	</script>
	<title><?php echo $cfg['nombre']; ?></title>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes (); ?>
	<h1>Nueva forma de evaluación</h1>
	<form action="post_eval.php" method="post" onsubmit="return validar()"><input type="hidden" name="modo" value="nuevo" />
	<p>Ingrese el nombre de la forma de evaluación: <input type="text" id="descripcion" name="descripcion" /></p>
	<p>Del tipo: <select name="grupo" id="grupo" ><?php
	database_connect ();
	
	$query = "SELECT * FROM Grupos_Evaluaciones";
	$result = mysql_query ($query, $mysql_con);
	
	while (($object = mysql_fetch_object ($result))) {
		printf ("<option value=\"%s\">%s</option>\n", $object->Id, $object->Descripcion);
	}
	
	mysql_free_result ($result);
	?></select></p>
	<p><b>Subida de calificaciones</b></p><p>Abierta: Las calificaciones pueden ser subidas en cualquier momento.<br />Cerrada: Nadie puede subir calificaciones para esta evaluación.<br />Basada en fechas: El tiempo de subida se define por el rango de fechas</p>
	<p>Subida: <select name="estado" id="estado" onchange="actualizar_cajas ()">
		<option value="open">Abierta</option>
		<option value="closed">Cerrada</option>
		<option value="time" selected="selected">Basada en fechas</option>
	</select></p>
	<p><input type="checkbox" id="exclusiva" name="exclusiva" value="1" /><label for="exclusiva">Para uso del maestro</label><br />Indica si esta forma de evaluación es para subida del maestro.</p>
	<p>Fecha de apertura: <input type="text" id="apertura" /></p>
	<input type="hidden" id="inicio" name="inicio" />
	<p>Fecha de cierre: <input type="text" id="cierre" /></p>
	<input type="hidden" id="fin" name="fin" />
	<input type="submit" value="Nueva" /></form>
</body>
</html>
