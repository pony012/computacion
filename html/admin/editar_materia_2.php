<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['crear_materias']) || $_SESSION['permisos']['crear_materias'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	
	/* Validar la clave la materia */
	if (isset ($_GET['clave'])) {
		header ("Location: editar_materia.php?clave=" . $_GET['clave']);
		exit;
	}
	
	/* Si no llegamos por post de la página anterior, regresar a las materias */
	if (!isset ($_POST['clave'])) {
		header ("Location: materias.php");
		exit;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
	<script language="javascript" type="text/javascript">
		function validar () {
			var porcen = document.getElementsByName("porcentajes[]");
			var suma = 0, n;
			
			for (g = 0; g < porcen.length; g++) {
				n = parseInt (porcen[g].value);
				if (n <= 0 || isNaN (n)) {
					/* Mandar mensaje de error */
					alert ("Porcentaje no válido");
					return false;
				}
				
				suma += n;
			}
			
			if (suma != 100) {
				alert ("Suma de porcentajes no válido");
				return false;
			}
			
			return true;
		}
	</script>
</head>
<body>
	<h1>Editar materia</h1>
	<form action="post_materia.php" method="POST" onsubmit="return validar ()" >
	<input type="hidden" name="modo" value="editar" />
	<?php
		printf ("<p>Clave de la materia: <input type=\"text\" name=\"clave\" id=\"clave\" value=\"%s\" readonly=\"readonly\" length=\"5\" /></p>\n", $_POST['clave']);
		printf ("<p>Descripción: <input type=\"text\" name=\"descripcion\" id=\"descripcion\" value=\"%s\" length=\"100\" /></p>\n", $_POST['descripcion']);
		
		echo "<h2>Asignar porcentajes:</h2>\n";
		
		sort ($_POST['evals'], SORT_NUMERIC);
		
		require_once '../mysql-con.php';
		
		/* Recuperar las formas de evaluación */
		$result = mysql_query ("SELECT * FROM Evaluaciones WHERE id > 0", $mysql_con);
		$ev_total = mysql_num_rows ($result);
		while (($row = mysql_fetch_row ($result)) != FALSE) $ev[$row[0]] = $row[1];
		mysql_free_result ($result);
		
		if ($_POST['evals'][0] == 0) {
			/* Significa que esta materia lleva extraordinario */
			echo "<p>La materia tiene extraordinario<input type=\"hidden\" name=\"tiene_extra\" value=\"1\" /><hr /></p>";
			unset ($_POST['evals'][0]);
		}
		
		foreach ($_POST['evals'] as $g) {
			if (!isset ($ev[$g])) continue;
			printf ("<p>%s:<br /><input type=\"hidden\" name=\"evals[]\" value=\"%s\" />Porcentaje: <input type=\"text\" value=\"0%%\" name=\"porcentajes[]\" /><hr /></p>\n", $ev[$g], $g);
			unset ($ev[$g]);
		}
	?>
	<input type="submit" value="Modificar materia" />
	</form>
</body>
</html>
