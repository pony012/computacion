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
	
	if (!preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_GET['materia'])) {
		header ("Location: aplicadores.php?e=clave");
		exit;
	}
	
	settype ($_GET['tipo'], 'integer');
	
	require_once '../mysql-con.php';
	
	$lugar = mysql_real_escape_string ($_GET['salon']);
	
	/* Select Materia FROM Aplicadores WHERE Materia = '%s' AND Tipo = '%s' AND Salon = '%s' LIMIT 1 */
	$query = sprintf ("SELECT Materia FROM Aplicadores WHERE Materia = '%s' AND Tipo = '%s' AND Salon = '%s' LIMIT 1", $_GET['materia'], $_GET['tipo'], $lugar);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		mysql_free_result ($result);
		mysql_close ($mysql_con);
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
	$query = sprintf ("SELECT A.Materia, M.Descripcion, A.Maestro, MAS.Nombre, MAS.Apellido, A.Tipo, E.Descripcion AS Evaluacion, UNIX_TIMESTAMP (A.FechaHora) AS FechaHora, A.Salon FROM Aplicadores AS A INNER JOIN Materias AS M ON A.Materia = M.Clave INNER JOIN Maestros AS MAS ON A.Maestro = MAS.Codigo INNER JOIN Evaluaciones AS E ON A.Tipo = E.Id WHERE A.Materia = '%s' AND A.Tipo = '%s' AND A.Salon = '%s' LIMIT 1", $_GET['materia'], $_GET['tipo'], $lugar);
	
	$result = mysql_query ($query, $mysql_con);
	
	$datos_salon = mysql_fetch_object ($result);
	mysql_free_result ($result);
	
	printf ("<p>Materia: %s %s<br />\nMaestro a cargo: %s %s<br />\nForma de evaluación: %s<br />\nFecha: %s<br />Hora: %s</p>\n", $datos_salon->Materia, $datos_salon->Descripcion, $datos_salon->Apellido, $datos_salon->Nombre, $datos_salon->Evaluacion, strftime ("%a %e %h %Y", $datos_salon->FechaHora), strftime ("%H:%M", $datos_salon->FechaHora));
?>
</html>
</body>
