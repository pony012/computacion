<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	if (!has_permiso ('asignar_aplicadores')) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
	
	if (!isset ($_GET['materia']) || !preg_match ("/^([A-Za-z])([A-Za-z0-9]){2}([0-9]){2}$/", $_GET['materia'])) {
		agrega_mensaje (1, "La clave especificada es incorrecta");
		header ("Location: aplicadores_general.php");
		exit;
	}
	
	$materia = strtoupper ($_GET['materia']);
	if (isset ($_GET['tipo'])) $eval = strval (intval ($_GET['tipo'])); /* Sanear la forma de evaluación */
	
	database_connect ();
	
	if (!isset ($eval)) { /* Cuando sólo abren Materia */
		$query = sprintf ("SELECT Materia FROM Salones_Aplicadores WHERE Materia = '%s' LIMIT 1", $materia);
	} else { /* Cuando nos dan materia y Tipo */
		/* Verificar si el tipo de evaluación existe */
		$query = sprintf ("SELECT Tipo FROM Salones_Aplicadores WHERE Materia = '%s' AND Tipo = '%s' LIMIT 1", $materia, $eval);
	}
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		mysql_free_result ($result);
		mysql_close ($mysql_con);
		agrega_mensaje (3, "Datos incorrectos");
		header ("Location: aplicadores_general.php");
		exit;
	}
	
	mysql_free_result ($result);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<link rel="stylesheet" type="text/css" href="../css/theme.css" />
	<script language="javascript" src="../scripts/comun.js" type="text/javascript"></script>
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes (); ?>
<h1>Salones de aplicación de exámenes</h1>
<?php
	setlocale (LC_ALL, "es_MX.UTF-8");
	database_connect ();
	
	/* SELECT COUNT(DISTINCT A.Materia, A.Tipo) AS TOTAL FROM Aplicadores AS A */
	if (isset ($eval)) {
		$query = sprintf ("SELECT COUNT(DISTINCT Materia, Tipo, Salon) AS TOTAL FROM Salones_Aplicadores WHERE Materia = '%s' AND Tipo = '%s'", $materia, $eval);
	
		$result = mysql_query ($query, $mysql_con);
		$object = mysql_fetch_object ($result);
		$total = $object->TOTAL;
		mysql_free_result ($result);
	
		$offset = (isset ($_GET['off'])) ? $_GET['off'] : 0;
		settype ($offset, "integer");
		$cant = 30;
		$show = $cant;
	
		if ($offset >= $total) $offset = $total - $cant;
		if ($offset < 0) $offset = 0;
		if (($offset + $cant) >= $total) $show = $total - $offset;
	}
	
	if (!isset ($eval)) {
		$query = sprintf ("SELECT Descripcion FROM Materias WHERE Clave = '%s'", $materia);
		$result = mysql_query ($query, $mysql_con);
		$object = mysql_fetch_object ($result);
		
		printf ("<p>Departamentales para la materia %s</p>\n", $object->Descripcion);
		echo "<p>Ver <a href=\"aplicadores_general.php\">todas las materias</a></p>";
		mysql_free_result ($result);
	} else {
		$query = sprintf ("SELECT P.Clave, M.Descripcion, E.Descripcion AS Evaluacion FROM Porcentajes AS P INNER JOIN Materias AS M ON P.Clave = M.Clave INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave = '%s' AND Tipo = '%s'", $materia, $eval);
		$result = mysql_query ($query, $mysql_con);
		$object = mysql_fetch_object ($result);
		
		printf ("<p>Salones asignados para <a href=\"aplicadores_materia.php?materia=%s\">%s</a> en la forma de evaluación %s</p>\n", $object->Clave, $object->Descripcion, $object->Evaluacion);
		mysql_free_result ($result);
	}
	
	echo "<table border=\"1\">";
	
	if (!isset ($eval)) {
		echo "<thead><tr><th>Materia</th><th>Tipo</th></tr></thead>\n";
		$query = sprintf ("SELECT DISTINCT A.Materia, A.Tipo, Mat.Descripcion, E.Descripcion AS Evaluacion FROM Salones_Aplicadores AS A INNER JOIN Materias AS Mat ON A.Materia = Mat.Clave INNER JOIN Evaluaciones AS E ON A.Tipo = E.Id WHERE Materia = '%s' ORDER BY A.Materia, A.Tipo", $materia);
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<tr><td>%s %s</td>", $object->Materia, $object->Descripcion);
			$link = array ('materia' => $object->Materia, 'tipo' => $object->Tipo);
			printf ("<td><a href=\"aplicadores_materia.php?%s\">%s</a></td></tr>\n", htmlentities (http_build_query ($link)), $object->Evaluacion);
		}
		
		mysql_free_result ($result);
		
		echo "</tbody>";
	} else {
		echo "<thead><tr><th>Materia</th><th>Salón</th><th>Fecha</th><th>Hora</th><th>Tipo</th><th>Maestro</th><th>Acciones</th></tr></thead>\n";
		/* INNER JOIN Maestros AS M ON A.Maestro = M.Codigo */
		$query = sprintf ("SELECT A.Id, A.Materia, Mat.Descripcion, A.Salon, UNIX_TIMESTAMP (A.FechaHora) AS FechaHora, A.Tipo, E.Descripcion AS Evaluacion, A.Maestro FROM Salones_Aplicadores AS A INNER JOIN Materias AS Mat ON A.Materia = Mat.Clave INNER JOIN Evaluaciones AS E ON A.Tipo = E.Id WHERE A.Materia = '%s' AND A.Tipo = '%s' ORDER BY A.Salon, FechaHora LIMIT %s, %s", $materia, $eval, $offset, $show);
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		
		while (($object = mysql_fetch_object ($result))) {
			printf ("<tr><td>%s %s</td>", $object->Materia, $object->Descripcion);
			$link = array ('id' => $object->Id);
			printf ("<td><a href=\"ver_salon_aplicador.php?%s\">%s</a></td>", htmlentities (http_build_query ($link)), $object->Salon);
			printf ("<td>%s</td><td>%s</td>", strftime ("%a %e %h %Y", $object->FechaHora), strftime ("%H:%M", $object->FechaHora));
			printf ("<td>%s</td>\n", $object->Evaluacion);
			if (!is_null ($object->Maestro)) {
				$query_m = sprintf ("SELECT Nombre, Apellido FROM Maestros WHERE Codigo = '%s'", $object->Maestro);
				$result_m = mysql_query ($query_m);
				$maestro = mysql_fetch_object ($result_m);
				printf ("<td>%s %s</td>\n", $maestro->Apellido, $maestro->Nombre);
				mysql_free_result ($result_m);
			} else {
				echo "<td><b>Indefinido</b></td>\n";
			}
			printf ("<td><a href=\"asignar_alumnos_aplicadores.php?%s\"><img class=\"icon\" src=\"../img/properties.png\" alt=\"editar\" /></a>", htmlentities (http_build_query ($link)));
			printf ("<a href=\"eliminar_salon_aplicador.php?%s\" onclick=\"return confirmarDrop(this, '¿Realmente desea eliminar el salón %s?')\"><img class=\"icon\" src=\"../img/remove.png\" alt=\"eliminar\" /></a></td></tr>", htmlentities (http_build_query ($link)), $object->Salon);
		}
		echo "</tbody>";
		
		mysql_free_result ($result);
	}
	
	echo "</table>";
	
	if (isset ($eval)) {
		/* Paginacion sólo para cuando hay tipo definido */
		echo "<p>";
		$next = $offset + $show;
		$ultimo = $total - ($total % $cant);
		if ($next >= $total) $next = $ultimo;
		$prev = $offset - $cant;
		if ($prev < 0) $prev = 0;
	
		/* Mostrar las flechas de dezplamiento */
		$link = $_GET;
		if ($offset > 0) {
			printf ("<a href=\"%s?%s\"><img class=\"icon\" src=\"../img/first.png\" alt=\"primero\" /></a>\n", $_SERVER['SCRIPT_NAME'], htmlentities (http_build_query (Array ('off' => 0) + $link)));
			printf ("<a href=\"%s?%s\"><img class=\"icon\" src=\"../img/prev.png\" alt=\"anterior\"/></a>\n", $_SERVER['SCRIPT_NAME'], htmlentities (http_build_query (Array ('off' => $prev) + $link)));
		}
		if ($offset + $show < $total) { 
			printf ("<a href=\"%s?%s\"><img class=\"icon\" src=\"../img/next.png\" alt=\"siguiente\"/></a>\n", $_SERVER['SCRIPT_NAME'], htmlentities (http_build_query (Array ('off' => $next) + $link)));
			printf ("<a href=\"%s?%s\"><img class=\"icon\" src=\"../img/last.png\" alt=\"ultimo\" /></a>\n", $_SERVER['SCRIPT_NAME'], htmlentities (http_build_query (Array ('off' => $ultimo) + $link)));
		}
	
		echo "</p>\n";
	}
?>
<ul>
	<li><a href="auto_aplicadores.php">Calcular automáticamente salones para un departamental</a></li>
	<li><a href="nuevo_salon_aplicador.php">Nuevo salón para aplicar examen</a></li>
</ul>
</body>
</html>
