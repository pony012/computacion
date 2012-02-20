<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	/* Validar los datos $_GET */
	if (!isset ($_GET['id'])) {
		header ("Location: seleccionar_subida.php");
		exit;
	}
	
	$id_salon = strval (intval ($_GET['id']));
	
	require_once '../mysql-con.php';
	require_once 'mensajes.php';
	
	/* Verificar que el id del salón exista, y guardar la clave de la materia */
	$query = sprintf ("SELECT * FROM Salones_Aplicadores WHERE Id = '%s'", $id_salon);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: seleccionar_subida.php");
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	$object = mysql_fetch_object ($result);
	mysql_free_result ($result);
	$materia = $object->Materia;
	$evaluacion = $object->Tipo;
	$salon_nombre = $object->Salon;
	
	/* Verificar que la materia exista, pertenece a una academia y este maestro es dueño de la academia */
	/* SELECT * FROM Academias AS A INNER JOIN Materias AS M ON M.Academia = A.Id WHERE A.Maestro = '2066907' AND M.Clave = 'ET200' */
	$query = sprintf ("SELECT M.Descripcion, A.Maestro, A.Subida FROM Academias AS A INNER JOIN Materias AS M ON M.Academia = A.Id WHERE M.Clave = '%s'", $materia);
	
	$result = mysql_query ($query, $mysql_con);
	if (mysql_num_rows ($result) == 0) {
		mysql_free_result ($result);
		header ("Location: seleccionar_subida.php");
		agrega_mensaje (1, "La materia no pertenece a una academia");
		exit;
	}
	
	$object = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	if ($object->Maestro != $_SESSION['codigo']) { /* Gracias pero no eres el presidente de la academia */
		header ("Location: seleccionar_subida.php");
		agrega_mensaje (1, "Usted no es el presidente de la academia");
		exit;
	}
	
	if ($object->Subida != 1) { /* Tampoco está autorizado para subir calificaciones */
		header ("Location: seleccionar_subida.php");
		agrega_mensaje (3, "No tiene permiso para subir calificaciones en esta academia");
		exit;
	}
	
	$materia_descripcion = $object->Descripcion;
	
	/* Verificar que la forma de evaluacion exista con la materia especificada
	 * Adicionalmente, recoger si la forma de evaluación está abierta */
	/* SELECT * FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave = 'ET200' AND P.Tipo = 0 */
	
	$query = sprintf ("SELECT *, UNIX_TIMESTAMP (E.Apertura) AS Apertura, UNIX_TIMESTAMP (E.Cierre) AS Cierre FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave = '%s' AND P.Tipo = %s", $materia, $evaluacion);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) { /* La forma de evaluacion no existe para esa materia */
		mysql_free_result ($result);
		header ("Location: seleccionar_subida.php");
		agrega_mensaje (3, "La forma de evaluación no existe para esta materia");
		exit;
	}
	
	$object = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	if ($object->Exclusiva != 0) { /* Esta forma de evaluación es para subida del maestro */
		header ("Location: seleccionar_subida.php");
		agrega_mensaje (3, "Esta forma de evaluación es para subida del maestro");
		exit;
	}
	
	if ($object->Estado == 'closed') { /* Esta forma de evaluación está deshabilitada */
		header ("Location: seleccionar_subida.php");
		agrega_mensaje (1, "La forma de evaluación no permite subida de calificaciones");
		exit;
	} else if ($object->Estado == 'time') {
		$now = time ();
		if ($now < $object->Apertura || $now >= $object->Cierre) {
			header ("Location: seleccionar_subida.php");
			setlocale (LC_ALL, "es_MX.UTF-8");
			agrega_mensaje (1, sprintf ("Fuera de tiempo para subida de calificaciones<br>%s se abre el %s", $object->Descripcion, strftime ("%A %e de %B de %Y a las %H:%M", $object->Apertura)));
			exit;
		}
	}
	
	$evaluacion_descripcion = $object->Descripcion;
	$evaluacion_ponderacion = $object->Ponderacion;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<link rel="stylesheet" type="text/css" href="../css/theme.css" />
	<script language="javascript" type="text/javascript">
		// <![CDATA[
		function validar () {
			var cadena;
			var entero;
			
			var ponderacion = document.getElementById ('ponderacion').value
			var cals = document.getElementsByName ("cal[]");
			var valores = document.getElementsByName ("valor[]");
			
			for (g = 0; g < cals.length; g++) {
				cadena = cals[g].value;
				
				if (cadena == null || cadena == "" || cadena == "--") {
					valores[g].value = "";
					continue;
				}
				
				/* TODO: ¿Permitir NP a los maestros? */
				
				if (cadena.charAt(cadena.length - 1) == '%') {
					cadena = cadena.substring (0, cadena.length - 1);
					entero = parseInt (cadena);
					
					if (isNaN (entero) || entero > 100 || entero < 0) {
						/* Poner en color rojo la caja equivocada */
						alert ("Valor equivocado");
						cals[g].select ();
						return false;
					}
					
					valores[g].value = Math.floor ((ponderacion * entero) / 100);
				} else {
					entero = parseInt (cadena);
					
					if (isNaN (entero) || entero < 0 || entero > ponderacion) {
						/* Poner en color rojo la caja equivocada */
						alert ("Valor equivocado");
						cals[g].select ();
						return false;
					}
					valores[g].value = entero;
				}
			}
			return true;
		}
		// ]]>
	</script>
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes (); ?>
	<h1>Subida de calificaciones</h1>
	<?php
		printf ("<p>Subida para la materia %s<br />Forma de evaluación: %s<br />Salón: %s<br/></p>", $materia_descripcion, $evaluacion_descripcion, $salon_nombre);
		printf ("<p>El valor para esta evaluación es de <b>%s puntos</b>, puede especificar este valor en puntos (del 0 al %s) o en porcentaje (ej, 80%%). En caso de señalar un porcentaje, éste será convertido a su valor en puntos. Puede poner \"--\" para representar una calificación vacía</p>", $evaluacion_ponderacion, $evaluacion_ponderacion);
		
		echo "<form action=\"post_subida_depa.php\" method=\"post\" onsubmit=\"return validar()\" autocomplete=\"off\">";
		
		printf ("<input type=\"hidden\" name=\"id\" value=\"%s\" />", $_GET['id']);
		printf ("<input type=\"hidden\" id=\"ponderacion\" value=\"%s\" />", $evaluacion_ponderacion);
		echo "<table border=\"1\"><thead><tr><th>Codigo</th><th>Alumno</th><th>Calificacion anterior (en puntos)</th><th>Nueva Calificación</th></tr></thead>\n";
		
		echo "<tbody>\n";
		
		/* SELECT * FROM Alumnos_Aplicadores AS AA INNER JOIN Alumnos AS A ON AA.Alumno = A.Codigo INNER JOIN Calificaciones AS C ON C.Alumno = AA.Alumno INNER JOIN Secciones AS S ON C.Nrc = S.Nrc WHERE AA.Id = 4 AND Tipo = 1 AND S.Materia = ET209 */
		$query = sprintf ("SELECT A.Codigo, A.Nombre, A.Apellido, C.Tipo, C.Valor FROM Alumnos_Aplicadores AS AA INNER JOIN Alumnos AS A ON AA.Alumno = A.Codigo INNER JOIN Calificaciones AS C ON C.Alumno = AA.Alumno INNER JOIN Secciones AS S ON C.Nrc = S.Nrc WHERE AA.Id = '%s' AND C.Tipo = '%s' AND S.Materia = '%s' ORDER BY A.Apellido, A.Nombre", $id_salon, $evaluacion, $materia);
		
		$result = mysql_query ($query, $mysql_con);
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<tr><td>%s</td><td>%s %s<input type=\"hidden\" name=\"alumno[]\" value=\"%s\" /></td>\n", $object->Codigo, $object->Apellido, $object->Nombre, $object->Codigo);
			if (is_null($object->Valor)) {
				echo "<td>--</td><td><input size=\"5\" type=\"text\" name=\"cal[]\" value=\"--\" />";
			} else { /* TODO: El valor del NP es -1 */
				printf ("<td>%s</td><td><input size=\"5\" type=\"text\" name=\"cal[]\" value=\"%s\" />", $object->Valor, $object->Valor);
			}
			echo "<input type=\"hidden\" name=\"valor[]\" /></td></tr>\n";
		}
		mysql_free_result ($result);
		
		echo "</tbody></table>\n";
		
		echo "<p><input type=\"submit\" value=\"Subir calificaciones\" /></p>";
		echo "</form>";
	?>
</body>
</html>
