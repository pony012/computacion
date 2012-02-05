<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	if (!isset ($_SESSION['permisos']['admin_evaluaciones']) || $_SESSION['permisos']['admin_evaluaciones'] != 1) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
	
	require_once "../mysql-con.php";
	
	if (!isset ($_GET['id']) || $_GET['id'] < 0) {
		header ("Location: evaluaciones.php");
		agrega_mensaje (1, "Error desconocido");
		exit;
	} else {
		settype ($_GET['id'], 'integer');
		$query = sprintf ("SELECT E.Descripcion, E.Id, E.Exclusiva, E.Estado, UNIX_TIMESTAMP (E.Apertura) AS Apertura, UNIX_TIMESTAMP (E.Cierre) AS Cierre, GE.Descripcion AS Grupo FROM Evaluaciones AS E INNER JOIN Grupos_Evaluaciones AS GE ON E.Grupo = GE.Id WHERE E.Id='%s' LIMIT 1", $_GET['id']);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (mysql_num_rows ($result) == 0) {
			agrega_mensaje (1, "La forma de evaluación especificada no existe");
			exit;
		}
		
		$object = mysql_fetch_object ($result);
		mysql_free_result ($result);
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
			$('#apertura').datetimepicker({
				dateFormat: 'D dd M yy',
				timeFormat: 'hh:mm',
				separator: ' a las ',
				onClose: function(dateText, inst) {
					var endDateTextBox = $('#cierre');
					if (endDateTextBox.val() != '') {
						var testStartDate = new Date(dateText);
						var testEndDate = new Date(endDateTextBox.val());
						if (testStartDate > testEndDate)
						    endDateTextBox.val(dateText);
					}
					else {
						endDateTextBox.val(dateText);
					}
				},
				onSelect: function (selectedDateTime){
					var start = $(this).datetimepicker('getDate');
					$('#cierre').datetimepicker('option', 'minDate', new Date(start.getTime()));
				}
			});
			$('#cierre').datetimepicker({
				dateFormat: 'D dd M yy',
				timeFormat: 'hh:mm',
				separator: ' a las ',
				onClose: function(dateText, inst) {
					var startDateTextBox = $('#apertura');
					if (startDateTextBox.val() != '') {
						var testStartDate = new Date(startDateTextBox.val());
						var testEndDate = new Date(dateText);
						if (testStartDate > testEndDate)
						    startDateTextBox.val(dateText);
					}
					else {
						startDateTextBox.val(dateText);
					}
				},
				onSelect: function (selectedDateTime){
					var end = $(this).datetimepicker('getDate');
					$('#apertura').datetimepicker('option', 'maxDate', new Date(end.getTime()) );
				}
			});
		});
	</script>
	<script language="javascript" type="text/javascript">
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
	</script>
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes ();
	echo "<h1>Editar forma de evaluación</h1>\n";
	
	echo "<form action=\"post_eval.php\" method=\"POST\" onsubmit=\"return validar()\"><input type=\"hidden\" name=\"modo\" value=\"editar\" />\n";
	printf ("<input type=\"hidden\" name=\"id\" value=\"%s\" />", $_GET['id']);
	printf ("<p>Nombre de la forma de evaluación:<input type=\"text\" id=\"descripcion\" name=\"descripcion\" value=\"%s\" /></p>\n", $object->Descripcion);
	printf ("<p>Del tipo: <b>%s</b></p>", $object->Grupo);
		
	if ($object->Exclusiva == 1) {
		echo "<input type=\"checkbox\" id=\"exclusiva\" name=\"exclusiva\" value=\"1\" checked=\"checked\" /><label for=\"exclusiva\">Exclusiva para el maestro</label>\n";
	} else {
		echo "<input type=\"checkbox\" id=\"exclusiva\" name=\"exclusiva\" value=\"1\" /><label for=\"exclusiva\">Exclusiva para el maestro</label>\n";
	}
	
	/* El estado de la evaluacion */
	echo "<p><b>Estado de la evaluación</b></p><p>Abierta: Las calificaciones pueden ser subidas en cualquier momento.<br />Cerrada: Nadie puede subir calificaciones para esta evaluación.<br />Basada en fechas: El tiempo de subida se define por el rango de fechas</p><p>Estado: <select name=\"estado\" id=\"estado\" onchange=\"actualizar_cajas ()\">";
	
	foreach (array ('open' => 'Abierta', 'closed' => 'Cerrada', 'time' => 'Basada en fechas') as $valor => $descr) {
		if ($object->Estado == $valor) {
			printf ("<option value=\"%s\" selected=\"selected\">%s</option>\n", $valor, $descr);
		} else {
			printf ("<option value=\"%s\">%s</option>\n", $valor, $descr);
		}
	}
	echo "</select></p>\n";
	
	echo "<p>Fecha de apertura: <input type=\"text\" id=\"apertura\" /></p><input type=\"hidden\" id=\"inicio\" name=\"inicio\" />\n<p>Fecha de cierre: <input type=\"text\" id=\"cierre\" /></p><input type=\"hidden\" id=\"fin\" name=\"fin\" />\n";
	
	echo "<input type=\"submit\" value=\"Actualizar\" /></form>\n";
	
	/* Forzar una actualización javascript de las fechas de inicio, fin */
	echo "<script language=\"javascript\" type=\"text/javascript\">\n$(function() {";
	printf ("var d1 = new Date (%s);\n", ($object->Apertura * 1000));
	echo "$('#apertura').datetimepicker('setDate', d1);\n";
	echo "var start = $('#apertura').datetimepicker('getDate');\n$('#cierre').datetimepicker('option', 'minDate', new Date(start.getTime()));";
	
	printf ("var d2 = new Date (%s);\n", ($object->Cierre * 1000));
	echo "$('#cierre').datetimepicker('setDate', d2);\n";
	echo "var end = $('#cierre').datetimepicker('getDate');\n$('#apertura').datetimepicker('option', 'maxDate', new Date(end.getTime()) );";
	echo "});</script>";
	?>
</body>
</html>
