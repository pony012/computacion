<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['asignar_aplicadores']) || $_SESSION['permisos']['asignar_aplicadores'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	
	settype ($_GET['salon'], 'integer');
	
	require_once '../mysql-con.php';
	
	/* Select Materia FROM Aplicadores WHERE Materia = '%s' AND Tipo = '%s' AND Salon = '%s' LIMIT 1 */
	$query = sprintf ("SELECT Id FROM Salones_Aplicadores WHERE Id = '%s' LIMIT 1", $_GET['salon']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		mysql_free_result ($result);
		mysql_close ($mysql_con);
		header ("Location: aplicadores_general.php?e=noexiste");
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
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
</head>
<body>
<h1>Salón de aplicación</h1>
<?php
	setlocale (LC_ALL, "es_MX.UTF-8");
	require_once '../mysql-con.php';
	
	/* SELECT A.Materia, M.Descripcion, A.Maestro, MAS.Nombre, MAS.Apellido, A.Tipo, E.Descripcion AS Evaluacion, UNIX_TIMESTAMP (A.FechaHora) AS
	 FechaHora, A.Salon FROM Aplicadores AS A INNER JOIN Materias AS M ON A.Materia = M.Clave INNER JOIN Maestros AS MAS ON A.Maestro = MAS.Codigo
	  INNER JOIN Evaluaciones AS E ON A.Tipo = E.Id WHERE A.Materia = 'CC422' AND A.Tipo = '2' AND A.Salon = 'Salón 2' LIMIT 1*/
	$query = sprintf ("SELECT A.Materia, M.Descripcion, A.Maestro, A.Tipo, E.Descripcion AS Evaluacion, UNIX_TIMESTAMP (A.FechaHora) AS FechaHora, A.Salon FROM Salones_Aplicadores AS A INNER JOIN Materias AS M ON A.Materia = M.Clave INNER JOIN Evaluaciones AS E ON A.Tipo = E.Id WHERE A.Id = '%s' LIMIT 1", $_GET['salon']);
	
	$result = mysql_query ($query, $mysql_con);
	
	$datos_salon = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	printf ("<p>Materia: %s %s<br />\n", $datos_salon->Materia, $datos_salon->Descripcion);
	if (!is_null ($datos_salon->Maestro)) {
		$query_m = sprintf ("SELECT Nombre, Apellido FROM Maestros WHERE Codigo = '%s'", $datos_salon->Maestro);
		$result_m = mysql_query ($query_m);
		$maestro = mysql_fetch_object ($result_m);
		printf ("Maestro a cargo: %s %s<br />\n", $maestro->Apellido, $maestro->Nombre);
		mysql_free_result ($result_m);
	} else {
		echo "Maestro a cargo: <b>Indefinido</b><br />\n";
	}
	printf ("Forma de evaluación: %s<br />\nFecha: %s<br />Hora: %s</p>\n", $datos_salon->Evaluacion, strftime ("%a %e %h %Y", $datos_salon->FechaHora), strftime ("%H:%M", $datos_salon->FechaHora));
	
	echo "<table border=\"1\">";
	
	echo "<thead><tr><th>No. Lista</th><th>Código</th><th>Alumno</th></tr></thead>";
	
	/* SELECT A.Alumno, AL.Nombre, AL.Apellido FROM Aplicadores as A INNER JOIN Alumnos AS AL ON A.Alumno = AL.Codigo ORDER BY AL.Apellido, AL.Nombre */
	$query = sprintf ("SELECT A.Alumno, AL.Nombre, AL.Apellido FROM Alumnos_Aplicadores as A INNER JOIN Alumnos AS AL ON A.Alumno = AL.Codigo WHERE A.Id = '%s' ORDER BY AL.Apellido, AL.Nombre", $_GET['salon']);
	
	$result = mysql_query ($query, $mysql_con);
	
	echo "<tbody>";
	$g = 0;
	while (($object = mysql_fetch_object ($result))) {
		$g++;
		printf ("<tr><td>%s</td><td>%s</td><td>%s %s</td></tr>", $g, $object->Alumno, $object->Apellido, $object->Nombre);
	}
	echo "</tbody>";
	mysql_free_result ($result);
	
	echo "</table>";
?>
</html>
</body>
