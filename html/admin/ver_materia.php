<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	/* Validar la clave la materia */
	if (!isset ($_GET['clave']) || !preg_match ("/^([A-Za-z])([A-Za-z0-9]){2}([0-9]){2}$/", $_GET['clave'])) {
		header ("Location: materias.php");
		agrega_mensaje (3, "Materia inválida");
		exit;
	}
	
	$clave_materia = $_GET['clave'];
	
	database_connect ();
	
	$query = sprintf ("SELECT * FROM Materias WHERE Clave = '%s'", $clave_materia);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: materias.php");
		agrega_mensaje (3, "Materia inválida");
		mysql_free_result ($result);
		exit;
	}
	
	$datos_materia = mysql_fetch_object ($result);
	mysql_free_result ($result);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<link rel="stylesheet" type="text/css" href="../css/theme.css" />
	<script language="javascript" src="../scripts/comun.js" type="text/javascript"></script>
	<title><?php echo $cfg['nombre']; ?></title>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes (); ?>
	<h1>Detalles de la materia</h1>
	<?php
		printf ("<p>Materia: %s</p><p>Descripcion: %s </p>\n", $datos_materia->Clave, $datos_materia->Descripcion);
		
		database_connect ();
		
		/* Recuperar la cantidad total de filas */
		$query = sprintf ("SELECT COUNT(*) AS TOTAL FROM Secciones WHERE Materia = '%s'", $clave_materia);
		$result = mysql_query ($query, $mysql_con);
		$row = mysql_fetch_object ($result);
		$total = $row->TOTAL;
		mysql_free_result ($result);
		
		$offset = (isset ($_GET['off'])) ? $_GET['off'] : 0;
		settype ($offset, "integer");
		$cant = 30;
		$show = $cant;
		
		if ($offset >= $total) $offset = $total - $cant;
		if ($offset < 0) $offset = 0;
		if (($offset + $cant) >= $total) $show = $total - $offset;
		
		printf ("<p>Grupos de la materia %s, mostrando de %s al %s</p>", $clave_materia, ($offset + 1), ($offset + $show));
		
		echo "<table border=\"1\">";
		
		/* Mostrar la cabecera */
		echo "<thead><tr><th>NRC</th><th>Clave</th><th>Materia</th><th>Seccion</th><th>Maestro</th></tr></thead>\n";
		
		/* Empezar la consulta mysql */
		$query = sprintf ("SELECT sec.Nrc, sec.Materia, mat.Descripcion, sec.Seccion, sec.Maestro, m.Nombre, m.Apellido, m.Codigo FROM Secciones AS sec INNER JOIN Materias AS mat ON sec.Materia = mat.Clave INNER JOIN Maestros AS m ON sec.Maestro = m.Codigo WHERE sec.Materia = '%s' LIMIT %s, %s", $clave_materia, $offset , $show);
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			/* El nrc */
			if ($_SESSION['codigo'] == $object->Codigo || has_permiso ('grupos_globales')) {
				printf ("<td><a href=\"ver_grupo.php?nrc=%s\">%s</a></td>",$object->Nrc, $object->Nrc);
			} else {
				printf ("<td>%s</td>", $object->Nrc);
			}
			printf ("<td>%s</td>", $object->Materia);
			printf ("<td>%s</td>", $object->Descripcion);
			printf ("<td>%s</td>", $object->Seccion);
			printf ("<td><a href=\"ver_maestro.php?codigo=%s\">%s %s</a></td>", $object->Codigo, $object->Apellido, $object->Nombre);
			echo "</tr>\n";
		}
		
		echo "</tbody></table>\n";
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
	?>
</body>
</html>
