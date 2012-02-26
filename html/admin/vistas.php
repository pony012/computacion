<?php
	require_once 'session_maestro.php';
	
	check_valid_session ();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<link rel="stylesheet" type="text/css" href="../css/theme.css" />
	<title><?php echo $cfg['nombre']; ?></title>
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes (); ?>
	<h1>Acciones para el sistema</h1>
	<ul>
	<li><a href="secciones.php">Grupos del departamento</a></li>
	<li><a href="materias.php">Materias del departamento</a></li>
	<li><a href="usuarios.php">Maestros del departamento</a></li>
	<?php
		printf ("<li><a href=\"ver_maestro.php?codigo=%s\">Mis grupos</a></li>", $_SESSION['codigo']);
		
		if (isset ($_SESSION['permisos']['admin_evaluaciones']) && $_SESSION['permisos']['admin_evaluaciones'] == 1) {
			echo "<li><a href=\"evaluaciones.php\">Formas de evaluación</a></li>";
		}
		if (isset ($_SESSION['permisos']['asignar_aplicadores']) && $_SESSION['permisos']['asignar_aplicadores'] == 1) {
			echo "<li><a href=\"aplicadores_general.php\">Gestionar salones de aplicacion de exámenes</a></li>";
		}
	?>
	<li><a href="carreras.php">Carreras</a></li>
	<li><a href="academias.php">Academias</a></li>
	</ul>
</body>
</html>
