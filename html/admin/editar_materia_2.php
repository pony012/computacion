<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	/* Validar la clave la materia */
	if (isset ($_GET['clave'])) {
		header ("Location: editar_materia.php?clave=" . $_GET['clave']);
		exit;
	}
	
	/* Si no llegamos por post de la página anterior, regresar a las materias */
	if (!isset ($_POST['clave']) || !isset ($_POST['descripcion']) || !isset ($_POST['evals']) || !is_array ($_POST['evals'])) {
		header ("Location: materias.php");
		exit;
	}
	
	require_once '../mysql-con.php';
		
	$query = sprintf ("SELECT * FROM Materias WHERE Clave='%s'", $_POST['clave']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: materias.php");
		agrega_mensaje (3, "Error desconocido");
		mysql_free_result ($result);
		mysql_close ($mysql_con);
		exit;
	}
	
	$materia = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	/* Ahora sí, checar por todos los permisos */
	if (!isset ($_SESSION['permisos']['crear_materias']) || $_SESSION['permisos']['crear_materias'] != 1) {
		/* Si no tienes el permiso global de crear_materias checamos por la academia */
		if (is_null ($materia->Academia)) { /* Si no pertence a una academia, bye bye */
			/* Privilegios insuficientes */
			agrega_mensaje (3, "Privilegios insuficientes");
			header ("Location: vistas.php");
			exit;
		} else {
			$query = sprintf ("SELECT Maestro, Materias FROM Academias WHERE Id = '%s'", $materia->Academia);
			$result = mysql_query ($query, $mysql_con);
			$academia = mysql_fetch_object ($result);
			mysql_free_result ($result);
	
			if ($academia->Maestro != $_SESSION['codigo']) {
				agrega_mensaje (3, "Privilegios insuficientes");
				header ("Location: vistas.php");
				exit;
			}
			
			if ($academia->Materias != 1) {
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
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
	<script language="javascript" type="text/javascript">
		// <![CDATA[
		function validar () {
			var grupos = document.getElementsByName("grupo[]");
			var suma, n;
			var porcen;
			
			for (g = 0; g < grupos.length; g++) {
				porcen = document.getElementsByName("p_" + grupos[g].value + "[]");
				
				suma = 0;
				for (h = 0; h < porcen.length; h++) {
					n = parseInt (porcen[h].value);
					if (n <= 0 || isNaN (n)) {
						/* Mandar mensaje de error */
						alert ("Porcentaje no válido");
						
						return false;
					}
				
					suma += n;
				}
			
				if (suma != 100) {
					alert ("Suma de porcentajes no válido");
					return false;
				}

			}
			
			return true;
		}
		// ]]>
	</script>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes (); ?>
	<h1>Editar materia</h1>
	<form action="post_materia.php" method="post" onsubmit="return validar ()" >
	<input type="hidden" name="modo" value="editar" />
	<p><b>Advertencia</b>: Cambiar las formas de evaluación de una materia borra todas las calificaciones existentes</p>
	<?php
		printf ("<p>Clave de la materia: <input type=\"text\" name=\"clave\" id=\"clave\" value=\"%s\" readonly=\"readonly\" maxlength=\"5\" /></p>\n", $_POST['clave']);
		printf ("<p>Descripción: <input type=\"text\" name=\"descripcion\" id=\"descripcion\" value=\"%s\" maxlength=\"99\" /></p>\n", $_POST['descripcion']);
		
		echo "<h2>Asignar porcentajes:</h2>\n";
		
		require_once '../mysql-con.php';
		
		sort ($_POST['evals'], SORT_NUMERIC);
		
		$todas = array ();
		$descripciones = array ();
		/* Recuperar las formas de evaluación */
		$result = mysql_query ("SELECT Id, Grupo, Descripcion FROM Evaluaciones ORDER BY Grupo, Id");
		
		while (($object = mysql_fetch_object ($result))) {
			$todas[$object->Id] = $object->Grupo;
			$descripciones[$object->Id] = $object->Descripcion;
		}
		
		mysql_free_result ($result);
		
		$limpias = array ();
		
		foreach ($_POST['evals'] as $value) {
			if (!isset ($todas[$value])) continue; /* Una evaluacion que no existe */
			$grupo = $todas[$value]; /* Guardar el grupo al que pertenece */
			if (!isset ($limpias[$grupo])) $limpias[$grupo] = array ();
			$limpias[$grupo][$value] = $descripciones[$value]; /* Meter esta forma de evaluacion bajo el grupo que pertenece */
		}
		
		unset ($todas);
		unset ($descripciones);
		
		foreach ($limpias as $key => $value) {
			$query = sprintf ("SELECT Descripcion FROM Grupos_Evaluaciones WHERE Id = '%s'", $key);
			$result = mysql_query ($query, $mysql_con);
			$object = mysql_fetch_object ($result);
			printf ("<h3>Para %s:</h3>", $object->Descripcion);
			mysql_free_result ($result);
			
			printf ("<input name=\"grupo[]\" type=\"hidden\" value=\"%s\" />\n", $key);
			$temp_por = (int) (100 / count ($value));
			foreach ($value as $id => $des) {
				printf ("<p>%s:<br /><input type=\"hidden\" name=\"eval_%s[]\" value=\"%s\" />\n", $des, $key, $id);
				printf ("Porcentaje: <input type=\"text\" name=\"p_%s[]\" value=\"%s%%\" /></p><hr />\n", $key, $temp_por);
			}
		}
	?>
	<input type="submit" value="Modificar materia" />
	</form>
</body>
</html>
