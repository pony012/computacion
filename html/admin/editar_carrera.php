<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['admin_carreras']) || $_SESSION['permisos']['admin_carreras'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	
	if (!isset ($_GET['clave']) || !preg_match ("/^([A-Za-z]){3}$/", $_GET['clave'])) {
		header ("Location: carreras.php");
		exit;
	}
	
	require_once '../mysql-con.php';
		
	$query = sprintf ("SELECT * FROM Carreras WHERE Clave='%s'", $_GET['clave']);
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) == 0) {
		header ("Location: carreras.php?e=noexiste");
		mysql_free_result ($result);
		mysql_close ($mysql_con);
		exit;
	}
	
	$object = mysql_fetch_object ($result);
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
		printf ("<p>Clave de la carrera: <input type=\"text\" name=\"clave\" id=\"clave\" maxlength=\"3\" readonly=\"readonly\" value=\"%s\" /></p>", $object->Clave);
		printf ("<p>Descripción de la carrera: <input type=\"text\" name=\"descripcion\" id=\"descripcion\" maxlength=\"99\" value=\"%s\" /></p>\n", $object->Descripcion);
	?>
	<input type="submit" value="Actualizar carrera" />
	</form>
</body>
</html>
