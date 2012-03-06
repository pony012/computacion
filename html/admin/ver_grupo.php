<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	/* Validar la clave la materia */
	if (!isset ($_GET['nrc']) || !preg_match ("/^([0-9]){1,5}$/", $_GET['nrc'])) {
		header ("Location: vistas.php");
		agrega_mensaje (3, "El nrc especificado no existe");
		exit;
	}
	
	database_connect ();
	/* SELECT * FROM Secciones AS Sec INNER JOIN Materias AS M ON Sec.Materia = M.Clave INNER JOIN Maestros AS Mas ON Sec.Maestro = Mas.Codigo WHERE Sec.Nrc='2006' */
	$query = sprintf ("SELECT Sec.Nrc, Sec.Seccion, M.Clave, M.Descripcion, Mas.Codigo, Mas.Nombre, Mas.Apellido FROM Secciones AS Sec INNER JOIN Materias AS M ON Sec.Materia = M.Clave INNER JOIN Maestros AS Mas ON Sec.Maestro = Mas.Codigo WHERE Sec.Nrc='%s'", $_GET['nrc']);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: vistas.php");
		agrega_mensaje (3, "El nrc especificado no existe");
		mysql_free_result ($result);
		exit;
	}
	
	$grupo = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	if ($grupo->Codigo != $_SESSION['codigo'] && !has_permiso ('grupos_globales')) {
		/* No puedes ver el grupo porque no tienes permisos globales */
		header ("Location: vistas.php");
		agrega_mensaje (3, "Privilegios insuficientes");
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
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
	<script language="javascript" type="text/javascript">
		// <![CDATA[
		$(document).ready(function(){
			$('#cal_tabs').tabs ();
		});
		// ]]>
	</script>
	<title><?php echo $cfg['nombre']; ?></title>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes (); ?>
	<h1>Grupo</h1>
	<?php
		printf ("<p>Materia: <a href=\"ver_materia.php?clave=%s\">%s - %s</a></p>", $grupo->Clave, $grupo->Clave, $grupo->Descripcion);
		printf ("<p>Maestro: <a href=\"ver_maestro.php?codigo=%s\">%s %s</a></p>", $grupo->Codigo, $grupo->Nombre, $grupo->Apellido);
		printf ("<p>Seccion: %s</p>", $grupo->Seccion);
		
		database_connect ();
		
		$query = sprintf ("SELECT E.Id, E.Descripcion, E.Estado, E.Exclusiva, UNIX_TIMESTAMP (E.Apertura) AS Apertura, UNIX_TIMESTAMP (E.Cierre) AS Cierre FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave='%s' AND E.Exclusiva = 1 ORDER BY E.Grupo, E.Id", $grupo->Clave);
		
		$result = mysql_query ($query, $mysql_con);
		
		if ($_SESSION['codigo'] == $grupo->Codigo) {
			echo "<p>Subida de calificaciones:</p><p>";
			
			while (($object = mysql_fetch_object ($result))) {
				if ($object->Estado == 'closed') continue; /* Esta forma de evaluación está deshabilitada */
				if ($object->Estado == 'time') {
					$now = time ();
					if ($now < $object->Apertura || $now >= $object->Cierre) continue; /* Fuera de tiempo */
				}
				
				$link = array ('nrc' => $_GET['nrc'], 'eval' => $object->Id);
				printf ("Para <a href=\"subida_calificaciones_maestro.php?%s\">%s</a><br />", htmlentities (http_build_query ($link)), $object->Descripcion);
			}
			echo "</p>";
		}/* No es el maestro, o no hay permiso de subida */
		
		mysql_free_result ($result);
		
		echo "<div id=\"cal_tabs\"><ul>\n";
		
		/* Recuperar los grupos de evaluaciones, mostrar las tabs */
		$query = sprintf ("SELECT DISTINCT E.Grupo, GE.Descripcion FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id INNER JOIN Grupos_Evaluaciones AS GE ON E.Grupo = GE.Id WHERE P.Clave = '%s'", $grupo->Clave);
		
		$result_ge = mysql_query ($query, $mysql_con);
		
		while (($object_ge = mysql_fetch_object ($result_ge))) printf ("<li><a href=\"#%s\">%s</a></li>", $object_ge->Descripcion, $object_ge->Descripcion);
		
		/* No free_result, con este mismo resultado voy a iterar */
		echo "</ul>";
		
		mysql_data_seek ($result_ge, 0);
		
		/* Iterar por cada grupo de evaluaciones, Ordinario, extra, etc... */
		while (($object_ge = mysql_fetch_object ($result_ge))) {
			printf ("<div id=\"%s\">", $object_ge->Descripcion);
			
			echo "<table border=\"1\"><thead><tr><th>No. Lista</th><th>Código</th><th>Alumno</th>\n";
			
			/* Recuperar las formas de evaluacion de este grupo (ej, Depa 1, Depa 2, etc...) */
			$query = sprintf ("SELECT E.Descripcion FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave='%s' AND E.Grupo = '%s' ORDER BY E.Id", $grupo->Clave, $object_ge->Grupo);
			
			$result = mysql_query ($query, $mysql_con);
			
			while (($object = mysql_fetch_object ($result))) printf ("<th>%s</th>", $object->Descripcion);
			mysql_free_result ($result);
			echo "<th>Promedio</th></tr></thead>\n<tbody>\n";
			
			/* Primero recuperar los alumnos de este grupo */
			$query = sprintf ("SELECT G.Alumno, A.Nombre, A.Apellido FROM Grupos AS G INNER JOIN Alumnos AS A ON G.Alumno = A.Codigo WHERE Nrc='%s' ORDER BY A.Apellido, A.Nombre", $_GET['nrc']);
			
			$result = mysql_query ($query, $mysql_con);
			
			$g = 0; /* Para mostrar el número de lista */
			while (($alumno = mysql_fetch_object ($result))) {
				$g++;
				printf ("<tr><td>%s</td><td>%s</td><td>%s %s</td>", $g, $alumno->Alumno, $alumno->Apellido, $alumno->Nombre);
				
				/* Ahora sí, recuperar las calificaciones de este, todas las formas de evaluacion de este grupo */
				$query = sprintf ("SELECT C.Valor FROM Calificaciones AS C INNER JOIN Evaluaciones AS E ON C.Tipo = E.Id WHERE C.Alumno = '%s' AND C.Nrc = '%s' AND E.Grupo = '%s' ORDER BY C.Tipo", $alumno->Alumno, $_GET['nrc'], $object_ge->Grupo);
				
				$cal_result = mysql_query ($query, $mysql_con);
				$promedio = 0;
				
				while (($cal = mysql_fetch_object ($cal_result))) {
					if (is_null ($cal->Valor)) {
						echo "<td>--</td>";
					} else { /* FIXME: El valor de los NP es -1 */
						printf ("<td>%s</td>", $cal->Valor);
						$promedio += $cal->Valor;
					}
				}
				
				printf ("<td>%s</td>", $promedio);
				
				mysql_free_result ($cal_result); /* Las calificaciones de este alumno */
				
				echo "</tr>\n";
			}
			mysql_free_result ($result); /* El resultado de los alumnos */
			
			/* Ahora, mostrar los promedios al final de la tabla */
			echo "<tr><td colspan=\"3\">Promedios</td>";
			$query = sprintf ("SELECT P.Tipo FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE Clave = '%s' AND E.Grupo = '%s'", $grupo->Clave, $object_ge->Grupo);
			
			$result = mysql_query ($query, $mysql_con);
			$total = 0;
			while (($object = mysql_fetch_object ($result))) {
				$query = sprintf ("SELECT Promedio FROM Promedios WHERE Nrc = '%s' AND Tipo = '%s'", $_GET['nrc'], $object->Tipo);
				
				$result_promedio = mysql_query ($query, $mysql_con);
				if (mysql_num_rows ($result_promedio) == 0) {
					echo "<td>--</td>";
				} else {
					$promedio = mysql_fetch_object ($result_promedio);
					printf ("<td>%s</td>", $promedio->Promedio);
					$total += $promedio->Promedio;
				}
				
				mysql_free_result ($result_promedio);
			}
			printf ("<td>%s</td></tr>\n", $total);
			mysql_free_result ($result);
			
			echo "</tbody></table></div>\n";
		} /* While de los grupos de evaluaciones */
		
		mysql_free_result ($result_ge);
		echo "</div>"; /* Tabs */
	?>
</body>
</html>
