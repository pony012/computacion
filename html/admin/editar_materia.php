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
	
	/* Validar la clave la materia */
	if (!isset ($_GET['clave']) || !preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_GET['clave'])) {
		header ("Location: materias.php?e=clave");
		exit;
	}
	
	require_once '../mysql-con.php';
		
	$query = "SELECT * FROM Materias WHERE Clave='". $_GET['clave'] ."' LIMIT 1";
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: materias.php?e=noexiste");
		mysql_free_result ($result);
		mysql_close ($mysql_con);
		exit;
	}
	
	$object = mysql_fetch_object ($result);
	mysql_free_result ($result);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
	<script language="javascript" type="text/javascript">
		function validar () {
			var evals = document.getElementById ("evals");
			var agregados = document.getElementById ("agregados");
			
			if (agregados.options.length == 0) {
				alert ("No hay formas de evaluacion seleccionadas");
				return false;
			}
			
			for (i = 0; i < agregados.length; i++) {
				evals.innerHTML += "<input type=\"hidden\" name=\"evals[]\" value=\"" + agregados.options[i].value; + "\"/>";
			}
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
</head>
<body>
	<h1>Editar materia</h1>
	<form action="editar_materia_2.php" method="POST" onsubmit="return validar()">
	<input type="hidden" name="modo" value="repost" />
	<?php
		printf ("<p>Clave de la materia: <input type=\"text\" name=\"clave\" id=\"clave\" length=\"5\" value=\"%s\" readonly=\"readonly\" /></p>", $object->Clave);
		printf ("<p>Descripción: <input type=\"text\" name=\"descripcion\" id=\"descripcion\" length=\"100\" value=\"%s\"/></p>", $object->Descripcion);
		echo "<p>Formas de evaluación disponibles: <br />";
		echo "<select id=\"disponibles\"><optgroup label=\"Extraordinario\"><option value=\"0\">Extraordinario</option></optgroup>";
	
		require_once '../mysql-con.php';
		$result = mysql_query ("SELECT * FROM Evaluaciones WHERE Id > 0", $mysql_con);
		
		echo "<optgroup label=\"Ordinario\">\n";
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<option value=\"%s\">%s</option>\n", $object->Id, $object->Descripcion);
		}
		mysql_free_result ($result);
		
		echo "</optgroup>";
		echo "</select><img class=\"icon\" src=\"../img/add2.png\" onclick=\"return agregar ()\" /></p>";
		echo "<p>Formas de evaluación seleccionadas: <br />";
		echo "<select size=\"10\" id=\"agregados\">";
		
		/* Recuperar los actualmente selccionados */
		/* SELECT P.Tipo, E.Descripcion FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave = 'ET213' */
		$query = sprintf ("SELECT P.Tipo, E.Descripcion FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave = '%s'", $_GET['clave']);
		
		$result = mysql_query ($query, $mysql_con);
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<option value=\"%s\">%s</option>\n", $object->Tipo, $object->Descripcion);
		}
		
		echo "</select>";
	?>
	<img class="icon" src="../img/remove2.png" onclick="return eliminar ()" /></p>
	<span id="evals"></span>
	<input type="submit" value="Siguiente" />
	</form>
</body>
</html>
