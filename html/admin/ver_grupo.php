<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	/* Validar la clave la materia */
	if (!isset ($_GET['nrc']) || !preg_match ("/^([0-9]){5}$/", $_GET['nrc'])) {
		header ("Location: usuarios.php?e=codigo");
		exit;
	}
	
	require_once '../mysql-con.php';
	/* SELECT * FROM Secciones AS Sec INNER JOIN Materias AS M ON Sec.Materia = M.Clave INNER JOIN Maestros AS Mas ON Sec.Maestro = Mas.Codigo WHERE Sec.Nrc='2006' */
	$query = sprintf ("SELECT Sec.Nrc, Sec.Seccion, M.Clave, M.Descripcion, Mas.Codigo, Mas.Nombre, Mas.Apellido FROM Secciones AS Sec INNER JOIN Materias AS M ON Sec.Materia = M.Clave INNER JOIN Maestros AS Mas ON Sec.Maestro = Mas.Codigo WHERE Sec.Nrc='%s'", $_GET['nrc']);
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: vistas.php?e=noexiste");
		mysql_free_result ($result);
		mysql_close ($mysql_con);
		exit;
	}
	
	$object = mysql_fetch_object ($result);
	
	if ($object->Codigo != $_SESSION['codigo'] && (!isset ($_SESSION['permisos']['grupos_globales']) || $_SESSION['permisos']['grupos_globales'] != 1)) {
		/* No puedes ver el grupo porque no tienes permisos globales */
		header ("Location: vistas.php?e=noaccess");
		mysql_free_result ($result);
		mysql_close ($mysql_con);
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
	<h1>Grupo</h1>
	<?php
		printf ("<p>Materia: <a href=\"ver_materia.php?clave=%s\">%s - %s</a></p>", $object->Clave, $object->Clave, $object->Descripcion);
		printf ("<p>Maestro: <a href=\"ver_maestro.php?codigo=%s\">%s %s</a></p>", $object->Codigo, $object->Nombre, $object->Apellido);
		printf ("<p>Seccion: %s</p>", $object->Seccion);
		
		echo "<table border=\"1\">";
		
		echo "<thead><tr><th>No. Lista</th><th>Código</th><th>Alumno</th>";
		
		require_once '../mysql-con.php';
		
		$query = sprintf ("SELECT E.Id, E.Descripcion FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave='%s' ORDER BY E.Id", $object->Clave);
		$result = mysql_query ($query, $mysql_con);
		
		$extra = 0;
		while (($object2 = mysql_fetch_object ($result))) {
			if ($object2->Id == 0) {
				$extra = 1;
				continue;
			}
			printf ("<th>%s</th>", $object2->Descripcion);
		}
		
		if ($extra == 1) echo "<th>Extraordinario</th>";
		mysql_free_result ($result);
		
		echo "</tr></thead>\n<tbody>";
		
		/* SELECT * FROM Calificaciones AS C INNER JOIN Alumnos AS Al ON C.Alumno = Al.Codigo WHERE C.Nrc = '02006' ORDER BY Al.Apellido */
		$query = sprintf ("SELECT G.Alumno, A.Nombre, A.Apellido FROM Grupos AS G INNER JOIN Alumnos AS A ON G.Alumno = A.Codigo WHERE Nrc='%s' ORDER BY A.Apellido", $_GET['nrc']);
		$result = mysql_query ($query, $mysql_con);
		
		$g = 1;
		while (($alumno = mysql_fetch_object ($result))) {
			printf ("<tr><td>%s</td><td>%s</td><td>%s %s</td>", $g, $alumno->Alumno, $alumno->Apellido, $alumno->Nombre);
			
			$sub_query = sprintf ("SELECT C.Tipo, C.Valor FROM Calificaciones AS C WHERE C.Alumno='%s' AND C.Nrc = '%s' ORDER BY C.Tipo", $alumno->Alumno, $_GET['nrc']);
			$sub_result = mysql_query ($sub_query, $mysql_con);
			
			$extra = 0;
			while (($cal = mysql_fetch_row ($sub_result))) {
				if ($cal[0] == '0') { /* FIXME: Ugly Fix for extra */
					$extra = 1;
					$extra_cal = $cal[1];
					continue;
				}
				
				if (is_null ($cal[1])) {
					echo "<td>--</td>";
				} else {
					printf ("<td>%s</td>", $cal[1]);
				}
			}
			
			if ($extra == 1) {
				if (is_null ($extra_cal)) {
					echo "<td>--</td>";
				} else {
					printf ("<td>%s</td>", $extra_cal);
				}
			}
			
			mysql_free_result ($sub_result);
			
			echo "</tr>\n";
			$g++;
		}
		
		mysql_free_result ($result);
		
		mysql_close ($mysql_con);
		echo "</tbody></table>";
		
	?>
</body>
</html>
