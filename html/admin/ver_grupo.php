<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	/* Validar la clave la materia */
	if (!isset ($_GET['nrc']) || !preg_match ("/^([0-9]){1,5}$/", $_GET['nrc'])) {
		header ("Location: vistas.php?e=nrcerr");
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
	
	$grupo = mysql_fetch_object ($result);
	
	if ($grupo->Codigo != $_SESSION['codigo'] && (!isset ($_SESSION['permisos']['grupos_globales']) || $_SESSION['permisos']['grupos_globales'] != 1)) {
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
	<link rel="stylesheet" media="all" type="text/css" href="../css/smoothness/jquery-ui-1.8.16.custom.css" />
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
	<script language="javascript" type="text/javascript">
		$(document).ready(function(){
			$('#cal_tabs').tabs ();
		});
	</script>
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
</head>
<body>
	<h1>Grupo</h1>
	<?php
		printf ("<p>Materia: <a href=\"ver_materia.php?clave=%s\">%s - %s</a></p>", $grupo->Clave, $grupo->Clave, $grupo->Descripcion);
		printf ("<p>Maestro: <a href=\"ver_maestro.php?codigo=%s\">%s %s</a></p>", $grupo->Codigo, $grupo->Nombre, $grupo->Apellido);
		printf ("<p>Seccion: %s</p>", $grupo->Seccion);
		
		require_once '../mysql-con.php';
		
		$query = sprintf ("SELECT E.Id, E.Descripcion, E.Exclusiva, UNIX_TIMESTAMP (E.Apertura) AS Apertura, UNIX_TIMESTAMP (E.Cierre) AS Cierre FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave='%s' ORDER BY E.Id", $grupo->Clave);
		
		$result = mysql_query ($query, $mysql_con);
		
		if ($_SESSION['codigo'] == $grupo->Codigo) {
			echo "<p>Subida de calificaciones:</p><p>";
			
			while (($object = mysql_fetch_object ($result))) {
				if ($object->Cierre - $object->Apertura == 0) continue; /* Esta forma de evaluación está deshabilitada */
				$now = time ();
				if ($object->Exclusiva == 1 && $now >= $object->Apertura && $now < $object->Cierre) {
					printf ("Para <a href=\"ningun_lugar.php\">%s</a><br />", $object->Descripcion);
				}
			}
			echo "</p>";
		}/* No es el maestro, o no hay permiso de subida */
		
		echo "<div id=\"cal_tabs\"><ul>\n";
		
		mysql_data_seek ($result, 0);
		
		$extra = false; $ordinario = false;
		while (($object = mysql_fetch_object ($result))) {
			if ($object->Id == 0) {
				$extra = true;
				continue;
			}
			if ($object->Id > 0) {
				$ordinario = true;
				break;
			}
		}
		
		mysql_free_result ($result);
		
		if ($ordinario) echo "<li><a href=\"#ordinario\">Ordinario</a></li>";
		if ($extra) echo "<li><a href=\"#extra\">Extraordinario</a></li>";
		
		echo "</ul>";
		
		if ($ordinario) {
			echo "<div id=\"ordinario\">";
			
			echo "<table border=\"1\">";
			
			echo "<thead><tr><th>No. Lista</th><th>Código</th><th>Alumno</th>";
			
			$query = sprintf ("SELECT E.Descripcion FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave='%s' AND E.Id > 0 ORDER BY E.Id", $grupo->Clave);
			
			$result = mysql_query ($query, $mysql_con);
			
			while (($object = mysql_fetch_object ($result))) {
				printf ("<th>%s</th>", $object->Descripcion);
			}
			
			mysql_free_result ($result);
			echo "</tr></thead>\n<tbody>";
			
			/* Primero recuperar los alumnos */
			$query = sprintf ("SELECT G.Alumno, A.Nombre, A.Apellido FROM Grupos AS G INNER JOIN Alumnos AS A ON G.Alumno = A.Codigo WHERE Nrc='%s' ORDER BY A.Apellido, A.Nombre", $_GET['nrc']);
			
			$result = mysql_query ($query, $mysql_con);
			
			$g = 0;
			
			while (($alumno = mysql_fetch_object ($result))) {
				$g++;
				printf ("<tr><td>%s</td><td>%s</td><td>%s %s</td>", $g, $alumno->Alumno, $alumno->Apellido, $alumno->Nombre);
				
				/* Ahora sí, recuperar las calificaciones */
				$query = sprintf ("SELECT Valor FROM Calificaciones WHERE Alumno = '%s' AND Nrc = '%s' AND Tipo > 0 ORDER BY Tipo", $alumno->Alumno, $_GET['nrc']);
				
				$cal_result = mysql_query ($query, $mysql_con);
				
				while (($cal = mysql_fetch_object ($cal_result))) {
					if (is_null ($cal->Valor)) {
						echo "<td>--</td>";
					} else { /* FIXME: El valor de los NP es -1 */
						printf ("<td>%s</td>", $cal->Valor);
					}
				}
				
				mysql_free_result ($cal_result);
				
				echo "</tr>";
			}
			mysql_free_result ($result);
			
			echo "</tbody></table></div>\n";
		}
		
		if ($extra) {
			echo "<div id=\"extra\">";
			
			echo "<table border=\"1\">";
			
			echo "<thead><tr><th>No. Lista</th><th>Código</th><th>Alumno</th><th>Extraordinario</th></tr></thead>\n<tbody>";
			
			/* Primero recuperar los alumnos */
			$query = sprintf ("SELECT G.Alumno, A.Nombre, A.Apellido FROM Grupos AS G INNER JOIN Alumnos AS A ON G.Alumno = A.Codigo WHERE Nrc='%s' ORDER BY A.Apellido, A.Nombre", $_GET['nrc']);
			
			$result = mysql_query ($query, $mysql_con);
			
			$g = 0;
			
			while (($alumno = mysql_fetch_object ($result))) {
				$g++;
				printf ("<tr><td>%s</td><td>%s</td><td>%s %s</td>", $g, $alumno->Alumno, $alumno->Apellido, $alumno->Nombre);
				
				/* Ahora sí, recuperar las calificaciones */
				$query = sprintf ("SELECT Valor FROM Calificaciones WHERE Alumno = '%s' AND Nrc = '%s' AND Tipo = 0 ORDER BY Tipo", $alumno->Alumno, $_GET['nrc']);
				
				$cal_result = mysql_query ($query, $mysql_con);
				
				while (($cal = mysql_fetch_object ($cal_result))) {
					if (is_null ($cal->Valor)) {
						echo "<td>--</td>";
					} else { /* FIXME: El valor de los NP es -1 */
						printf ("<td>%s</td>", $cal->Valor);
					}
				}
				
				mysql_free_result ($cal_result);
				
				echo "</tr>";
			}
			mysql_free_result ($result);
			
			echo "</tbody></table></div>\n";
		}
		
		echo "</div>"; /* Tabs */
	?>
</body>
</html>
