<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	if (!isset ($_SESSION['permisos']['crear_materias']) || $_SESSION['permisos']['crear_materias'] != 1) {
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
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
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
				evals.innerHTML += "<input type=\"hidden\" name=\"evals[]\" value=\"" + agregados.options[i].value + "\"/>";
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
	<h1>Nueva materia</h1>
	<form action="nueva_materia_2.php" method="POST" onsubmit="return validar()">
	<p>Clave de la materia: <input type="text" name="clave" id="clave" length="5" /></p>
	<p>Descripción: <input type="text" name="descripcion" id="descripcion" length="100" /></p>
	<p>Formas de evaluación disponibles: <br />
	<?php
		require_once '../mysql-con.php';
		
		echo "<select id=\"disponibles\">";
		$result = mysql_query ("SELECT * FROM Grupos_Evaluaciones", $mysql_con);
		
		while (($grupo_e = mysql_fetch_object ($result))) {
			printf ("<optgroup label=\"%s\">", $grupo_e->Descripcion);
			
			$query = sprintf ("SELECT Id, Descripcion FROM Evaluaciones WHERE Grupo = '%s' ORDER BY Id", $grupo_e->Id);
			$result_evals = mysql_query ($query, $mysql_con);
			
			while (($object = mysql_fetch_object ($result_evals))) {
				printf ("<option value=\"%s\">%s</option>", $object->Id, $object->Descripcion);
			}
			
			mysql_free_result ($result_evals);
			echo "</optgroup>";
		}
		
		mysql_free_result ($result); /* Posiblemente lo utilice después */
		
		echo "</select>";
	?>
	</select><img class="icon" src="../img/add2.png" onclick="return agregar ()" /></p>
	<p>Formas de evaluación seleccionadas: <br />
	<select size="10" id="agregados"></select><img class="icon" src="../img/remove2.png" onclick="return eliminar ()" /></p>
	<span id="evals"></span>
	<input type="submit" value="Siguiente" />
	</form>
</body>
</html>
