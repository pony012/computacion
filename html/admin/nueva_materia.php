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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<link rel="stylesheet" type="text/css" href="../css/theme.css" />
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
	<?php if (!isset($_POST['post']) || $_POST['post'] != 'post') { ?>
	<script language="javascript" type="text/javascript">
		function validar () {
			var clave = document.getElementById ("clave").value;
			var evals = document.getElementById ("evals");
			var agregados = document.getElementById ("agregados");
			/* Validaciones sobre la clave */
			if (!/^([A-Za-z]){2}([0-9]){3}$/.test(clave)) {
				/* Clave incorrecta */
				alert ("Clave incorrecta");
				return false;
			}
			
			if (agregados.options.length == 0) {
				alert ("No hay formas de evaluacion seleccionadas");
				return false;
			}
			
			for (i = 0; i < agregados.length; i++) {
				evals.innerHTML += "<input type=\"hidden\" name=\"evals[]\" value=\"" + agregados.options[i].value; + "\"/>";
			}
			alert (evals.innerHTML);
			return true;
		}
	</script>
	<script language="javascript" type="text/javascript">
		function agregar () {
			var agregados = document.getElementById ("agregados");
			var disponibles = document.getElementById ("disponibles");
			
			var num = disponibles.options[disponibles.selectedIndex].value;
			
			for (i = 0; i < agregados.length; i++) {
				if (agregados.options[i].value == num) return; /* Item duplicado */
			}
			
			var nueva_opc = document.createElement ("option");
			nueva_opc.text = disponibles.options[disponibles.selectedIndex].text;
			nueva_opc.value = num;
			
			agregados.add (nueva_opc, null);
		}
		
		function eliminar () {
			var agregados = document.getElementById ("agregados");
			if (agregados.options.length == 0) return;
			agregados.remove (agregados.selectedIndex);
		}
	</script>
	<?php } else if ($_POST['post'] == 'post') { ?>
	<script language="javascript" type="text/javascript">
		function validar () {
			var porcen = document.getElementsByName("porcentajes[]");
			var suma = 0, n;
			
			for (g = 0; g < porcen.length; g++) {
				n = parseInt (porcen[g].value);
				alert ("Voy en " + g + ": " + n);
				if (n <= 0 || isNaN (n)) {
					/* Mandar mensaje de error */
					alert ("Porcentaje no válido");
					return false;
				}
				
				suma += n;
			}
			
			alert (suma);
			
			if (suma != 100) {
				alert ("Suma de porcentajes no válido");
				return false;
			}
			
			return true;
		}
	</script>
	<?php } ?>
</head>
<body>
	<h1>Nueva materia</h1>
	<?php if (!isset($_POST['post']) || $_POST['post'] != 'post') { ?>
	<form action="nueva_materia.php" method="POST" onsubmit="return validar()">
	<input type="hidden" name="post" value="post" />
	<p>Clave de la materia: <input type="text" name="clave" id="clave" length="5" /></p>
	<p>Descripción: <input type="text" name="descripcion" id="descripcion" length="100" /></p>
	<p>Formas de evaluación disponibles: <br />
	<select id="disponibles"><optgroup label="Extraordinario"><option value="0">Extraordinario</option></optgroup>
	<?php
		require_once '../mysql-con.php';
		$result = mysql_query ("SELECT * FROM Evaluaciones WHERE Id > 0", $mysql_con);
		
		echo "<optgroup label=\"Ordinario\">\n";
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<option value=\"%s\">%s</option>\n", $object->Id, $object->Descripcion);
		}
		
		echo "</optgroup>";
	?>
	</select><img class="icon" src="../img/add2.png" onclick="return agregar ()" /></p>
	<p>Formas de evaluación seleccionadas: <br />
	<select size="10" id="agregados"></select><img class="icon" src="../img/remove2.png" onclick="return eliminar ()" /></p>
	<span id="evals"></span>
	<input type="submit" value="Siguiente" />
	</form>
	<?php } else if ($_POST['post'] == 'post') {
		echo "<form action=\"post_materia.php\" method=\"POST\" onsubmit=\"return validar ()\" >\n";
		echo "<input type=\"hidden\" name=\"post\" value=\"nuevo\" />\n";
		printf ("<p>Clave de la materia: <input type=\"text\" name=\"clave\" id=\"clave\" value=\"%s\" readonly=\"readonly\" length=\"5\" /></p>\n", $_POST['clave']);
		printf ("<p>Descripción: <input type=\"text\" name=\"descripcion\" id=\"descripcion\" value=\"%s\" readonly=\"readonly\" length=\"100\" /></p>\n", $_POST['descripcion']);
		
		echo "<h2>Asignar porcentajes:</h2>\n";
		
		asort ($_POST['evals'], SORT_NUMERIC);
		
		require_once '../mysql-con.php';
		
		/* Recuperar las formas de evaluación */
		$result = mysql_query ("SELECT * FROM Evaluaciones", $mysql_con);
		while (($row = mysql_fetch_row ($result)) != FALSE) $ar[$row[0]] = $row[1];
		mysql_free_result ($result);
		
		foreach ($_POST['evals'] as $eval) {
			if (!isset ($ar[$eval])) continue;
			printf ("<p>%s:<br /><input type=\"hidden\" name=\"evals[]\" value=\"%s\" />Porcentaje: <input type=\"text\" value=\"0%%\" name=\"porcentajes[]\" /><hr /></p>\n", $ar[$eval], $eval);
			unset ($ar[$eval]);
		}
		
		echo "<input type=\"submit\" value=\"Agregar materia\" />\n";
		echo "</form>\n";
	} ?>
</body>
</html>
