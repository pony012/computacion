<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	if (!has_permiso ('asignar_aplicadores')) {
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
		// ]]>
	</script>
	<script language="javascript" type="text/javascript">
		// <![CDATA[
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
		
		function validar () {
			if (document.getElementById ("materia").value == "NULL" || document.getElementById ("evaluacion").value == "NULL") {
				alert ("No ha seleccionado una materia");
				return false;
			}
			
			var fecha = $('#SFecha').datetimepicker('getDate');
			
			if (fecha == null) {
				alert ("Fecha es null");
				return false;
			}
			
			var tf = fecha.getTime() / 1000;
			
			document.getElementById ("fecha").value = parseInt (tf);
			
			return true;
		}
		// ]]>
	</script>
	<title><?php echo $cfg['nombre']; ?></title>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes (); ?>
	<h1>Nuevo salón para aplicar evaluación</h1>
	<form method="post" action="post_nuevo_salon.php" onsubmit="return validar ();" ><p>Materia:
	<select name="materia" id="materia">
	<option value="NULL" selected="selected">Seleccione una materia</option>
	<?php
		database_connect ();
		/* SELECT DISTINCT P.Clave, M.Descripcion FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id INNER JOIN Materias AS M ON P.Clave = M.Clave WHERE E.Exclusiva = 0 */
		$query = "SELECT DISTINCT P.Clave, M.Descripcion FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id INNER JOIN Materias AS M ON P.Clave = M.Clave WHERE E.Exclusiva = 0";
		$result = mysql_query ($query, $mysql_con);
		while (($object = mysql_fetch_object ($result))) {
			printf ("<option value=\"%s\">%s - %s</option>\n", $object->Clave, $object->Clave, $object->Descripcion);
		}
		mysql_free_result ($result);
	?>
	</select></p>
	<p>Evaluación: <select name="evaluacion" id="evaluacion"><option value="NULL" selected="selected">Seleccione una materia primero</option></select></p>
	<p>Fecha y hora de aplicación:<input type="text" id="SFecha" /><input type="hidden" id="fecha" name="fecha" /></p>
	<p><input type="radio" id="pre_salon_1" name="pre_salon" checked="checked" onchange="actualizar_cajas ()"/><label for="pre_salon_1\">Un salón de la lista:</label>
	<select id="sel_salon" name="salon">
	<?php
		$query = "SELECT DISTINCT Salon FROM Salones_Aplicadores ORDER BY Salon";
		$result = mysql_query ($query, $mysql_con);
		while (($object = mysql_fetch_object ($result))) {
			printf ("<option value=\"%s\">%s</option>\n", $object->Salon, $object->Salon);
		}
		mysql_free_result ($result);
	?>
	</select></p>
	<p><input type="radio" id="pre_salon_2" name="pre_salon" onchange="actualizar_cajas ()"/><label for="pre_salon_2">Un nuevo salón:</label>
	<input type="text" id="txt_salon" name="salon" disabled="disabled" /></p>
	<p>Maestro a cargo del salón:<select name="maestro" id="maestro">
	<option value="NULL" selected="selected" >Pendiente</option>
	<?php
		$query = "SELECT Codigo, Nombre, Apellido FROM Maestros ORDER BY Apellido, Nombre";
		$result = mysql_query ($query, $mysql_con);
		while (($object = mysql_fetch_object ($result))) {
			printf ("<option value=\"%s\">%s %s</option>\n", $object->Codigo, $object->Apellido, $object->Nombre);
		}
		mysql_free_result ($result);
	?>
	</select></p>
	<input type="submit" value="Asignar alumnos" /></form>
</body>
</html>
