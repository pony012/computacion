<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	/* Validar los datos $_GET */
	if (!isset ($_GET['materia']) || !isset ($_GET['depa'])) {
		header ("Location: seleccionar_subida.php");
		exit;
	}
	
	$evaluacion = strval (intval ($_GET['depa']));
	
	if (!preg_match ("/^([A-Za-z])([A-Za-z0-9]){2}([0-9]){2}$/", $_GET['materia'])) {
		header ("Location: seleccionar_subida.php");
		exit;
	}
	
	require_once '../mysql-con.php';
	require_once 'mensajes.php';
	
	/* Verificar que la materia exista, pertenece a una academia y este maestro es dueño de la academia */
	/* SELECT * FROM Academias AS A INNER JOIN Materias AS M ON M.Academia = A.Id WHERE A.Maestro = '2066907' AND M.Clave = 'ET200' */
	
	$query = sprintf ("SELECT M.Descripcion, A.Maestro, A.Subida FROM Academias AS A INNER JOIN Materias AS M ON M.Academia = A.Id WHERE M.Clave = '%s'", $_GET['materia']);
	
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
	
	$query = sprintf ("SELECT *, UNIX_TIMESTAMP (E.Apertura) AS Apertura, UNIX_TIMESTAMP (E.Cierre) AS Cierre FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave = '%s' AND P.Tipo = %s", $_GET['materia'], $evaluacion);
	
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
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes ();?>
	<h1>Subida de calificaciones</h1>
	<?php printf ("<p>Materia: %s<br />\nEvaluación %s</p>", $materia_descripcion, $evaluacion_descripcion);
	
	echo "<p>Salones de aplicación</p>";
	
	$query = sprintf ("SELECT *, UNIX_TIMESTAMP (FechaHora) AS FechaHora FROM Salones_Aplicadores WHERE Materia = '%s' AND Tipo = '%s' ORDER BY Salon", $_GET['materia'], $evaluacion);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		/* No hay salones de aplicacion */
		printf ("<p><b>No hay salones de aplicación para la materia y forma de evaluación seleccionada</b></p>");
	} else {
		/* Imprimir la cabecera */
		echo "<table border=\"1\"><thead><tr><th>Salón</th><th>Fecha</th><th>Hora</th><th>Aplicado por el maestro</th></tr></thead><tbody>";
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<tr><td><a href=\"subida_calificaciones_depa.php?id=%s\">%s</a></td><td>%s</td><td>%s</td>", $object->Id, $object->Salon, strftime ("%a %e %h %Y", $object->FechaHora), strftime ("%H:%M", $object->FechaHora));
			if (!is_null ($object->Maestro)) {
				$query_m = sprintf ("SELECT Nombre, Apellido FROM Maestros WHERE Codigo = '%s'", $object->Maestro);
				$result_m = mysql_query ($query_m);
				$maestro = mysql_fetch_object ($result_m);
				printf ("<td>%s %s</td></tr>\n", $maestro->Apellido, $maestro->Nombre);
				mysql_free_result ($result_m);
			} else {
				echo "<td><b>Indefinido</b></td></tr>\n";
			}
		}
		echo "</tbody></table>";
	}
	mysql_free_result ($result); ?>
</body>
</html>
