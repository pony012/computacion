<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_GET['nrc']) || !isset ($_GET['eval'])) {
		header ("Location: vistas.php");
		exit;
	}
	
	settype ($_GET['eval'], 'integer');
	
	/* Validar primero el NRC */
	if (!preg_match ("/^([0-9]){1,5}$/", $_GET['nrc'])) {
		header ("Location: secciones.php?e=nrc");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	/* Primero verificar que esté abierta la materia para subida de calificaciones */
	/* SELECT * FROM Secciones AS S INNER JOIN Porcentajes AS P ON S.Materia = P.Clave INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE S.Nrc = '1758' AND P.Tipo = '1' AND EXCLUSIVA = 1 */
	/* Esta query descarta nrc inexistente, tipo de evaluacion inexistente para esa materia, y que la forma de evaluación no sea exclusiva del maestro */
	$query = sprintf ("SELECT S.Maestro, UNIX_TIMESTAMP(E.Apertura) AS Apertura, UNIX_TIMESTAMP(E.Cierre) AS Cierre, E.Descripcion AS Evaluacion, P.Ponderacion FROM Secciones AS S INNER JOIN Porcentajes AS P ON S.Materia = P.Clave INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE S.Nrc = '%s' AND P.Tipo = '%s' AND E.Exclusiva = 1", $_GET['nrc'], $_GET['eval']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		/* TODO: Usar argumento next para regresar a la página anterior */
		header ("Location: vistas.php");
		exit;
	}
	
	$datos_eval = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	if ($datos_eval->Maestro != $_SESSION['codigo']) {
		/* Lo siento, pero tu no eres el maestro de este grupo */
		header ("Location: vistas.php?e=maestro");
		exit;
	}
	
	/* Verificar que los tiempos estén abiertos */
	$now = time ();
	if ($datos_eval->Cierre - $datos_eval->Apertura == 0 || ($now < $datos_eval->Apertura || $now >= $datos_eval->Cierre)) {
		/* Esta evaluación está cerrada */
		header ("Location: vistas.php?e=cerrada");
		exit;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<link rel="stylesheet" type="text/css" href="../css/theme.css" />
	<script language="javascript" type="text/javascript">
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
	</script>
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
</head>
<body>
	<h1>Subida de calificaciones</h1>
	<?php
		/* Recuperar nombre de la materia y del maestro */
		/* SELECT * FROM Secciones AS S INNER JOIN Materias AS M ON S.Materia = M.Clave INNER JOIN Maestros as Mas ON S.Maestro = Mas.Codigo WHERE S.Nrc = '1758' */
		$query = sprintf ("SELECT S.Materia, S.Maestro, S.Seccion, M.Descripcion, Mas.Nombre, Mas.Apellido FROM Secciones AS S INNER JOIN Materias AS M ON S.Materia = M.Clave INNER JOIN Maestros as Mas ON S.Maestro = Mas.Codigo WHERE S.Nrc = '%s'", $_GET['nrc']);
	
		$result = mysql_query ($query, $mysql_con);
	
		$nrc = mysql_fetch_object ($result);
		mysql_free_result ($result);
		
		printf ("<p>Nrc: %s</p><p>Materia: %s %s, Sección: %s</p><p>Maestro: %s %s</p><p>Forma de evaluación: <b>%s</b></p>", $_GET['nrc'], $nrc->Materia, $nrc->Descripcion, $nrc->Seccion, $nrc->Apellido, $nrc->Nombre, $datos_eval->Evaluacion);
		
		printf ("<p>El valor para esta evaluación es de <b>%s puntos</b>, puede especificar este valor en puntos (del 0 al %s) o en porcentaje (ej, 80%%). En caso de señalar un porcentaje, éste será convertido a su valor en puntos. Puede poner \"--\" para representar una calificación vacía</p>", $datos_eval->Ponderacion, $datos_eval->Ponderacion);
		echo "<form action=\"post_subida_maestros.php\" method=\"POST\" onsubmit=\"return validar()\" autocomplete=\"off\">";
		
		printf ("<input type=\"hidden\" name=\"nrc\" value=\"%s\" /><input type=\"hidden\" name=\"eval\" value=\"%s\" />", $_GET['nrc'], $_GET['eval']);
		printf ("<input type=\"hidden\" id=\"ponderacion\" value=\"%s\" />", $datos_eval->Ponderacion);
		echo "<table border=\"1\"><thead><tr><th>No. Lista</th><th>Alumno</th><th>Calificacion anterior (en puntos)</th><th>Nueva Calificación</th></tr></thead>\n";
		
		echo "<tbody>\n";
		
		/* SELECT * FROM Calificaciones AS C INNER JOIN Alumnos AS A ON C.Alumno = A.Codigo WHERE C.Nrc ='1758' AND C.Tipo = '4' ORDER BY A.Apellido, A.Nombre */
		$query = sprintf ("SELECT C.Alumno, C.Valor, A.Apellido, A.Nombre FROM Calificaciones AS C INNER JOIN Alumnos AS A ON C.Alumno = A.Codigo WHERE C.Nrc ='%s' AND C.Tipo = '%s' ORDER BY A.Apellido, A.Nombre", $_GET['nrc'], $_GET['eval']);
		
		$result = mysql_query ($query, $mysql_con);
		
		$g = 0;
		while (($object = mysql_fetch_object ($result))) {
			$g++;
			
			printf ("<tr><td>%s</td><td>%s %s<input type=\"hidden\" name=\"alumno[]\" value=\"%s\" /></td>\n", $g, $object->Apellido, $object->Nombre, $object->Alumno);
			if (is_null($object->Valor)) {
				echo "<td>--</td><td><input size=\"5\" type=\"text\" name=\"cal[]\" value=\"--\" /></td></tr>\n";
			} else { /* TODO: El valor del NP es -1 */
				printf ("<td>%s</td><td><input size=\"5\" type=\"text\" name=\"cal[]\" value=\"%s\" /></td></tr>\n", $object->Valor, $object->Valor);
			}
			echo "<input type=\"hidden\" name=\"valor[]\" />";
		}
		mysql_free_result ($result);
		
		echo "</tbody></table>\n";
		
		echo "<p><input type=\"submit\" value=\"Subir calificaciones\" /></p>";
		echo "</form>";
	?>
</body>
</html>
