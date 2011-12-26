<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['admin_evaluaciones']) || $_SESSION['permisos']['admin_evaluaciones'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	
	if (!isset ($_GET['m']) || ($_GET['m'] != 'e' && $_GET['m'] != 'n')) {
		header ("Location: evaluaciones.php");
		exit;
	}
	
	require_once "../mysql-con.php";
	
	if ($_GET['m'] == 'e') {
		if (!isset ($_GET['id']) || $_GET['id'] < 1) {
			header ("Location: evaluaciones.php");
			exit;
		} else {
			settype ($_GET['id'], 'integer');
			$query = sprintf ("SELECT * FROM Evaluaciones WHERE Id='%s' LIMIT 1", $_GET['id']);
			
			$result = mysql_query ($query, $mysql_con);
			
			if (mysql_num_rows ($result) == 0) {
				header ("Location: evaluaciones.php?e=noexiste");
				exit;
			}
			
			$object = mysql_fetch_object ($result);
			mysql_free_result ($result);
		}
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
</head>
<body>
	<?php if ($_GET['m'] == 'n') {
		echo "<h1>Nueva forma de evaluación</h1>";
		
		echo "<form action=\"post_eval.php\" method=\"POST\" ><input type=\"hidden\" name=\"modo\" value=\"nuevo\" />";
		
		echo "<p>Ingrese el nombre de la forma de evaluación:<input type=\"text\" name=\"descripcion\" /></p>";
		
		echo "<input type=\"submit\" value=\"Nueva\" /></form>";
	} else {
		echo "<h1>Editar forma de evaluación</h1>";
		
		echo "<form action=\"post_eval.php\" method=\"POST\" ><input type=\"hidden\" name=\"modo\" value=\"editar\" />";
		printf ("<input type=\"hidden\" name=\"id\" value=\"%s\" />", $_GET['id']);
		printf ("<p>Nombre de la forma de evaluación:<input type=\"text\" name=\"descripcion\" value=\"%s\" /></p>", $object->Descripcion);
		
		echo "<input type=\"submit\" value=\"Actualizar\" /></form>";
	} ?>
</body>
</html>
