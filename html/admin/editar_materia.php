<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	/* Validar la clave la materia */
	if (!isset ($_GET['clave']) || !preg_match ("/^([A-Za-z])([A-Za-z0-9]){2}([0-9]){2}$/", $_GET['clave'])) {
		header ("Location: materias.php");
		agrega_mensaje (3, "Clave incorrecta");
		exit;
	}
	
	$clave_materia = $_GET['clave'];
	
	database_connect ();
		
	$query = sprintf ("SELECT * FROM Materias WHERE Clave='%s'", $clave_materia);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: materias.php");
		agrega_mensaje (3, "Error desconocido");
		mysql_free_result ($result);
		exit;
	}
	
	$materia = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	/* Ahora sí, checar por todos los permisos */
	if (!has_permiso ('crear_materias')) {
		/* Si no tienes el permiso global de crear_materias checamos por la academia */
		if (is_null ($materia->Academia)) { /* Si no pertence a una academia, bye bye */
			/* Privilegios insuficientes */
			agrega_mensaje (3, "Privilegios insuficientes 1");
			header ("Location: vistas.php");
			exit;
		} else {
			$query = sprintf ("SELECT Maestro, Materias FROM Academias WHERE Id = '%s'", $materia->Academia);
			$result = mysql_query ($query, $mysql_con);
			$academia = mysql_fetch_object ($result);
			mysql_free_result ($result);
			
			if ($academia->Maestro != $_SESSION['codigo']) { /* No eres el presidente */
				agrega_mensaje (3, "Privilegios insuficientes");
				header ("Location: vistas.php");
				exit;
			}
			
			if ($academia->Materias != 1) { /* No hay edición de materias para esta academia */
				agrega_mensaje (1, "La academia no permite la edición de materias\nContacte al jefe de departamento");
				header ("Location: academias.php");
				exit;
			}
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<link rel="stylesheet" type="text/css" href="../css/theme.css" />
	<title><?php echo $cfg['nombre']; ?></title>
	<script language="javascript" type="text/javascript">
		// <![CDATA[
		function validar () {
			var evals = document.getElementById ("evals");
			var agregados = document.getElementById ("agregados");
			
			if (agregados.options.length == 0) {
				alert ("No hay formas de evaluacion seleccionadas");
				return false;
			}
			
			for (i = 0; i < agregados.length; i++) {
				evals.innerHTML += "<input type=\"hidden\" name=\"evals[]\" value=\"" + agregados.options[i].value + "\"/\>";
			}
			return true;
		}
		
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
		// ]]>
	</script>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes (); ?>
	<h1>Editar materia</h1>
	<form action="editar_materia_2.php" method="post" onsubmit="return validar()">
	<p><b>Advertencia</b>: Cambiar las formas de evaluación de una materia borra todas las calificaciones existentes</p>
	<?php
		printf ("<p>Clave de la materia: <input type=\"text\" name=\"clave\" id=\"clave\" maxlength=\"5\" value=\"%s\" readonly=\"readonly\" /></p>", $materia->Clave);
		printf ("<p>Descripción: <input type=\"text\" name=\"descripcion\" id=\"descripcion\" maxlength=\"99\" value=\"%s\"/></p>", $materia->Descripcion);
		echo "<p>Formas de evaluación disponibles: <br />";
		
		database_connect ();
		
		echo "<select id=\"disponibles\">";
		$result = mysql_query ("SELECT * FROM Grupos_Evaluaciones", $mysql_con);
		
		while (($grupo_e = mysql_fetch_object ($result))) {
			/* FIXME: Optgroup vacio cuando no hay formas de evaluación */
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
		
		echo "</select><img class=\"icon\" src=\"../img/add2.png\" onclick=\"return agregar ()\" alt=\"agregar\"/></p>";
		echo "<p>Formas de evaluación seleccionadas: <br />";
		echo "<select size=\"10\" id=\"agregados\">";
		
		/* Recuperar los actualmente selccionados */
		/* SELECT P.Tipo, E.Descripcion FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave = 'ET213' */
		$query = sprintf ("SELECT P.Tipo, E.Descripcion FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave = '%s' ORDER BY E.Grupo, P.Tipo", $materia->Clave);
		
		$result = mysql_query ($query, $mysql_con);
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<option value=\"%s\">%s</option>\n", $object->Tipo, $object->Descripcion);
		}
		
		echo "</select>";
	?>
	<img class="icon" src="../img/remove2.png" onclick="return eliminar ()" alt="eliminar" /></p>
	<span id="evals"></span>
	<input type="submit" value="Siguiente" />
	</form>
</body>
</html>
