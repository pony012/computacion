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
	<script type="text/javascript" type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
	<script type="text/javascript" type="text/javascript">
		function agregar () {
			var ids = document.getElementsByName("ids[]");
			var combo = document.getElementById ("disponibles");
			var selec = parseInt (combo.options[combo.selectedIndex].value);
			
			if (selec == -1) return nuevo ();
			
			alert ("Valor del seleccionado: " + selec);
			alert ("Valor de la colección de ids: " + ids);
			if (ids != null) {
				alert ("Voy al for, ids.length: " + ids.length);
				for (g = 0; g < ids.length; g++) {
					alert ("Aviso, en la zona de span hay un: " + ids[g].value);
					if (ids[g].value == selec) return;
				}
			}
			
			var separador = document.getElementById ("evals");
			
			separador.innerHTML += "<span name=\"eval" + selec + "\">" + 
			                       "<p><input id=\"ids\" name=\"ids[]\" type=\"hidden\" value=\"" + selec + "\" / >" +
			                       combo.options[combo.selectedIndex].text + " Porcentaje:" +
			                       "<input id=\"valor\" name=\"valor[]\" type=\"text\" value=\"100\" / ></p>" +
			                       "</span>";
			
		}
		
		function nuevo () {
			alert ("Agregar una nueva opción");
		}
	</script>
</head>
<body>
	<h1>Nueva materia</h1>
	<form action="post_materia.php" method="POST" >
	<input type="hidden" name="modo" value="nuevo" />
	<p>Clave de la materia: <input type="text" name="clave" id="clave" length="5" /></p>
	<p>Descripción: <input type="text" name="descripcion" id="descripcion" length="100" /></p>
	<?php
		echo "<select name=\"disponibles\" id=\"disponibles\">\n";
		
		echo "<optgroup label=\"Extraordinario\">";
		echo "<option value=\"0\">Extraordinario</option>";
		echo "</optgroup>\n";
		
		require_once '../mysql-con.php';
		
		$query = "SELECT * FROM Evaluaciones WHERE Id > 0";
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<optgroup label=\"Ordinario\">\n";
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<option value=\"%s\">%s</option>\n", $object->Id, $object->Descripcion);
		}
		
		echo "<option value=\"-1\">Nuevo...</option>\n";
		echo "</optgroup>";
		echo "</select>\n";
	?>
	<img class="icon" src="../img/add2.png" onclick="return agregar ()" />
	<span id="evals">
		
	</span>
	</form>
</body>
</html>
