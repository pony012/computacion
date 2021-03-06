<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	if (!isset ($_GET['id'])) {
		header ("Location: evaluaciones.php");
		exit;
	}
	
	if (!has_permiso ('admin_evaluaciones')) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
	
	$id_eval = strval (intval ($_GET['id']));
	database_connect ();
	
	$query = sprintf ("SELECT E.Descripcion, E.Id, E.Exclusiva, E.Estado, UNIX_TIMESTAMP (E.Apertura) AS Apertura, UNIX_TIMESTAMP (E.Cierre) AS Cierre, GE.Descripcion AS Grupo FROM Evaluaciones AS E INNER JOIN Grupos_Evaluaciones AS GE ON E.Grupo = GE.Id WHERE E.Id='%s'", $id_eval);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: evaluaciones.php");
		agrega_mensaje (1, "La forma de evaluación especificada no existe");
		exit;
	}
	
	$evaluacion = mysql_fetch_object ($result);
	mysql_free_result ($result);
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
	<h1>Editar forma de evaluación</h1>
	<form action="post_eval.php" method="post" onsubmit="return validar()"><input type="hidden" name="modo" value="editar" />
	<?php printf ("<input type=\"hidden\" name=\"id\" value=\"%s\" />", $evaluacion->Id);
	printf ("<p>Nombre de la forma de evaluación:<input type=\"text\" id=\"descripcion\" name=\"descripcion\" value=\"%s\" /></p>\n", $evaluacion->Descripcion);
	printf ("<p>Del tipo: <b>%s</b></p>", $evaluacion->Grupo);
		
	if ($evaluacion->Exclusiva == 1) {
		echo "<input type=\"checkbox\" id=\"exclusiva\" name=\"exclusiva\" value=\"1\" checked=\"checked\" /><label for=\"exclusiva\">Exclusiva para el maestro</label>\n";
	} else {
		echo "<input type=\"checkbox\" id=\"exclusiva\" name=\"exclusiva\" value=\"1\" /><label for=\"exclusiva\">Exclusiva para el maestro</label>\n";
	}
	
	/* El estado de la evaluacion */
	echo "<p><b>Estado de la evaluación</b></p><p>Abierta: Las calificaciones pueden ser subidas en cualquier momento.<br />Cerrada: Nadie puede subir calificaciones para esta evaluación.<br />Basada en fechas: El tiempo de subida se define por el rango de fechas</p><p>Estado: <select name=\"estado\" id=\"estado\" onchange=\"actualizar_cajas ()\">";
	
	foreach (array ('open' => 'Abierta', 'closed' => 'Cerrada', 'time' => 'Basada en fechas') as $valor => $descr) {
		if ($evaluacion->Estado == $valor) {
			printf ("<option value=\"%s\" selected=\"selected\">%s</option>\n", $valor, $descr);
		} else {
			printf ("<option value=\"%s\">%s</option>\n", $valor, $descr);
		}
	}
	echo "</select></p>\n";
	
	if ($evaluacion->Estado == 'time') {
		echo "<p>Fecha de apertura: <input type=\"text\" id=\"apertura\" /></p><input type=\"hidden\" id=\"inicio\" name=\"inicio\" />\n<p>Fecha de cierre: <input type=\"text\" id=\"cierre\" /></p><input type=\"hidden\" id=\"fin\" name=\"fin\" />\n";
		
		/* Forzar una actualización javascript de las fechas de inicio, fin */
		echo "<script language=\"javascript\" type=\"text/javascript\">\n// <![CDATA[\n$(function() {";
		printf ("var d1 = new Date (%s);\n", ($evaluacion->Apertura * 1000));
		echo "$('#apertura').datetimepicker('setDate', d1);\n";
				
		printf ("var d2 = new Date (%s);\n", ($evaluacion->Cierre * 1000));
		echo "$('#cierre').datetimepicker('setDate', d2);\n";
		echo "});\n// ]]>\n</script>";
	} else {
		echo "<p>Fecha de apertura: <input type=\"text\" id=\"apertura\" disabled=\"disabled\" /></p><input type=\"hidden\" id=\"inicio\" name=\"inicio\" />\n<p>Fecha de cierre: <input type=\"text\" id=\"cierre\" disabled=\"disabled\" /></p><input type=\"hidden\" id=\"fin\" name=\"fin\" />\n";
	} ?>
	<input type="submit" value="Actualizar" /></form>
</body>
</html>
