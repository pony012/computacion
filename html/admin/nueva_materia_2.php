<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	if (!isset ($_SESSION['permisos']['crear_materias']) || $_SESSION['permisos']['crear_materias'] != 1) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		header ("Location: vistas.php");
		exit;
	}
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
	<script language="javascript" type="text/javascript">
		function validar () {
			var grupos = document.getElementsByName("grupo[]");
			var suma, n;
			var porcen;
			
			for (g = 0; g < grupos.length; g++) {
				porcen = document.getElementsByName("p_" + grupos[g].value + "[]");
				
				suma = 0;
				for (h = 0; h < porcen.length; h++) {
					n = parseInt (porcen[h].value);
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

			}
			
			return true;
		}
	</script>
</head>
<body>
	<h1>Nueva materia</h1>
	<form action="post_materia.php" method="POST" onsubmit="return validar ()" >
	<input type="hidden" name="modo" value="nuevo" />
	<?php
		printf ("<p>Clave de la materia: <input type=\"text\" name=\"clave\" id=\"clave\" value=\"%s\" readonly=\"readonly\" length=\"5\" /></p>\n", $_POST['clave']);
		printf ("<p>Descripción: <input type=\"text\" name=\"descripcion\" id=\"descripcion\" value=\"%s\" length=\"100\" /></p>\n", $_POST['descripcion']);
		
		echo "<h2>Asignar porcentajes:</h2>\n";
		
		require_once '../mysql-con.php';
		
		sort ($_POST['evals'], SORT_NUMERIC);
		
		$todas = array ();
		$descripciones = array ();
		/* Recuperar las formas de evaluación */
		$result = mysql_query ("SELECT Id, Grupo, Descripcion FROM Evaluaciones ORDER BY Grupo, Id");
		
		while (($object = mysql_fetch_object ($result))) {
			$todas[$object->Id] = $object->Grupo;
			$descripciones[$object->Id] = $object->Descripcion;
		}
		
		mysql_free_result ($result);
		
		$limpias = array ();
		
		foreach ($_POST['evals'] as $value) {
			if (!isset ($todas[$value])) continue; /* Una evaluacion que no existe */
			$grupo = $todas[$value];
			if (!isset ($limpias[$grupo])) $limpias[$grupo] = array ();
			$limpias[$grupo][$value] = $descripciones[$value];
		}
		
		unset ($todas);
		unset ($descripciones);
		
		foreach ($limpias as $key => $value) {
			$query = sprintf ("SELECT Descripcion FROM Grupos_Evaluaciones WHERE Id = '%s'", $key);
			$result = mysql_query ($query, $mysql_con);
			$object = mysql_fetch_object ($result);
			printf ("<h3>Para %s:</h3>", $object->Descripcion);
			mysql_free_result ($result);
			
			printf ("<input name=\"grupo[]\" type=\"hidden\" value=\"%s\" />\n", $key);
			$temp_por = (int) (100 / count ($value));
			foreach ($value as $id => $des) {
				printf ("<p>%s:<br /><input type=\"hidden\" name=\"eval_%s[]\" value=\"%s\" />\n", $des, $key, $id);
				printf ("Porcentaje: <input type=\"text\" name=\"p_%s[]\" value=\"%s%%\" /></p><hr />\n", $key, $temp_por);
			}
		}
	?>
	<input type="submit" value="Agregar materia" />
	</form>
</body>
</html>
