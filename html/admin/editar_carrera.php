<?php
	require_once 'session_maestro.php';
	check_valid_session ();
	
	require_once 'mensajes.php';
	
	if (!has_permiso ('admin_carreras')) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		agrega_mensaje (3, "Privilegios insuficientes");
		exit;
	}
	
	if (!isset ($_GET['clave']) || !preg_match ("/^([A-Za-z]){5}$/", $_GET['clave'])) {
		header ("Location: carreras.php");
		exit;
	}
	
	$clave_carrera = strval ($_GET['clave']);
	
	database_connect ();
		
	$query = sprintf ("SELECT * FROM Carreras WHERE Clave='%s'", $clave_carrera);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: carreras.php");
		agrega_mensaje (3, "Carrera desconocida");
		mysql_free_result ($result);
		exit;
	}
	
	$carrera = mysql_fetch_object ($result);
	mysql_free_result ($result);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<link rel="stylesheet" type="text/css" href="../css/theme.css" />
	<title><?php echo $cfg['nombre']; ?></title>
	<script language="javascript" type="text/javascript">
		// <![CDATA[
		function validar () {
			var desc = document.getElementById ("descripcion").value;
			
			if (desc == null || desc == "") {
				alert ("Descripción vacia");
				return false;
			}
			
			return true;
		}
		// ]]>
	</script>
</head>
<body>
	<h1>Editar Carrera</h1>
	<form action="post_carrera.php" method="post" onsubmit="return validar ()">
	<input type="hidden" name="modo" value="editar" />
	<?php
		printf ("<p>Clave de la carrera: <input type=\"text\" name=\"clave\" id=\"clave\" maxlength=\"5\" readonly=\"readonly\" value=\"%s\" /></p>", $carrera->Clave);
		printf ("<p>Descripción de la carrera: <input type=\"text\" name=\"descripcion\" id=\"descripcion\" maxlength=\"99\" value=\"%s\" /></p>\n", $carrera->Descripcion);
	?>
	<input type="submit" value="Actualizar carrera" />
	</form>
</body>
</html>
