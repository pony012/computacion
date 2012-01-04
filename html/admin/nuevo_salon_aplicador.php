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
				separator: ' a las '
			});
			
			$('#materia').change(function() {
				if ($('#materia').val() == 'NULL') {
					$('#evaluacion').empty ();
					$('#evaluacion').append ($("<option></option>").attr("value", "NULL").text("Selecciona una materia primero"));
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
					$.each(data, function(i,item){
						$('#evaluacion').append ($("<option></option>").attr("value", item.Tipo).text(item.Descripcion));
					});
				});
			});
		});
	</script>
	<script language="javascript" type="text/javascript">
		function actualizar_cajas () {
			if (document.getElementById ("pre_salon_1").checked) {
				document.getElementById ("sel_salon").disabled = false;
				document.getElementById ("txt_salon").disabled = true;
				document.getElementById ("txt_salon").value = "";
			} else {
				document.getElementById ("sel_salon").disabled = true;
				document.getElementById ("txt_salon").disabled = false;
			}
		}
	</script>
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
</head>
<body>
	<h1>Nuevo salón para aplicar evaluación</h1>
	<?php
		require_once '../mysql-con.php';
		
		echo "<form><p>Materia:";
		
		/* SELECT DISTINCT P.Clave, M.Descripcion FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id INNER JOIN Materias AS M ON P.Clave = M.Clave WHERE E.Exclusiva = 0 */
		$query = "SELECT DISTINCT P.Clave, M.Descripcion FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id INNER JOIN Materias AS M ON P.Clave = M.Clave WHERE E.Exclusiva = 0";
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<select name=\"materia\" id=\"materia\">\n";
		echo "<option value=\"NULL\" selected=\"selected\">Seleccione una materia</option>\n";
		while (($object = mysql_fetch_object ($result))) {
			printf ("<option value=\"%s\">%s %s</option>\n", $object->Clave, $object->Clave, $object->Descripcion);
		}
		echo "</select></p>";
		mysql_free_result ($result);
		
		echo "<p>Evaluación: <select name=\"evaluacion\" id=\"evaluacion\"><option value=\"NULL\" selected=\"selected\">Seleccione una materia primero</option></select></p>\n";
		echo "<p>Fecha y hora de aplicación:<input type=\"text\" id=\"SFecha\" /></p>\n";
		echo "<p><input type=\"radio\" id=\"pre_salon_1\" name=\"pre_salon\" checked=\"checked\" onchange=\"actualizar_cajas ()\"/><label for=\"pre_salon_1\">Un salón de la lista:</label>\n";
		
		echo "<select id=\"sel_salon\" name=\"salon\">\n";
		$query = "SELECT DISTINCT Salon FROM Aplicadores ORDER BY Salon";
		
		$result = mysql_query ($query, $mysql_con);
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<option value=\"%s\">%s</option>\n", $object->Salon, $object->Salon);
		}
		
		mysql_free_result ($result);
		echo "</select></p>\n";
		
		echo "<p><input type=\"radio\" id=\"pre_salon_2\" name=\"pre_salon\" onchange=\"actualizar_cajas ()\"/><label for=\"pre_salon_2\">Un nuevo salón:</label>\n";
		echo "<input type=\"text\" id=\"txt_salon\" name=\"salon\" disabled=\"disabled\" /></p>\n";
		
		echo "<input type=\"submit\" value=\"Asignar alumnos\" /></form>";
	?>
</body>
</html>
