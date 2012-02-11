<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	if (!isset ($_SESSION['permisos']['admin_academias']) || $_SESSION['permisos']['admin_academias'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: academias.php");
		agrega_mensaje (3, "Privilegios insuficientes");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		if (!isset ($_GET['id'])) {
			header ("Location: academias.php");
			agrega_mensaje (3, "Peticion desconocida");
			exit;
		}
		
		$id_limpio = array ();
		$id_limpio[0] = strval (intval ($_GET['id']));
	} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if (!isset ($_POST['id']) || !is_array ($_POST['id'])) {
			header ("Location: academias.php");
			agrega_mensaje (3, "Peticion desconocida");
			exit;
		}
		
		$id_limpio = array ();
		
		foreach ($_POST['id'] as $value) {
			$id_limpio[] = strval (intval ($value));
		}
	} else {
		header ("Location: academias.php");
		exit;
	}
	
	/* Ya tengo todos los ID, ahora verificar que existan,
	 * si la lista se queda vacia, marcar error y regresar */
	foreach ($id_limpio as $index => $value) {
		$query = sprintf ("SELECT Id, Nombre AS Academia, Maestro FROM Academias WHERE Id = '%s'", $value);
		
		$result = mysql_query ($query, $mysql_con);
		
		/* Academia, ¿Existes? */
		if (mysql_num_rows ($result) == 0) { /* No existo, quitar de la lista */
			unset ($id_limpio[$index]);
		} else { /* Existo, ¿tengo presidente? */
			$academia = mysql_fetch_object ($result);
			if (is_null ($academia->Maestro)) { /* No, no tengo presidente, quitar de la lista con mensaje de error */
				agrega_mensaje (1, sprintf ("No se pueden establecer los permisos porque la academia %s no tiene presidente", $academia->Academia));
				unset ($id_limpio[$index]);
			} else { /* Sí tengo, recuperar todos los datos, y dejarlos en el arreglo */
				$query = sprintf ("SELECT A.Id, A.Nombre AS Academia, A.Subida, A.Materias, M.Nombre, M.Apellido FROM Academias AS A INNER JOIN Maestros AS M ON A.Maestro = M.Codigo WHERE A.Id = '%s'", $value);
				
				$sub_result = mysql_query ($query, $mysql_con);
				
				$id_limpio [$index] = mysql_fetch_object ($sub_result);
				mysql_free_result ($sub_result);
			}
			
		}
		
		mysql_free_result ($result);
	}
	
	if (count ($id_limpio) == 0) {
		header ("Location: academias.php");
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
</head>
<body><?php require_once 'mensajes.php'; mostrar_mensajes ();?>
	<h1>Permisos de academia</h1>
	<form method="post" action="post_permisos_academia.php" ><p>Permisos de academia para: <br />
	<?php
		foreach ($id_limpio as $academia) {
			printf ("Academia %s, Presidente: %s %s", $academia->Academia, $academia->Apellido, $academia->Nombre);
			printf ("<input type=\"hidden\" name=\"id[]\" value=\"%s\" /><br />\n", $academia->Id);
		}
	?></p>
	<p>Permisos:</p>
	<p><input type="checkbox" name="subida" id="subida" value="1" /><label for="subida">Subir calificaciones de esta academia (departamentales y métodos de evaluación varias)</label><br />
	<input type="checkbox" name="materias" id="materias" value="1" /><label for="materias">Modificar materias de la academia</label><br /></p>
	<?php if ($_SERVER['REQUEST_METHOD'] == 'GET') { /* En caso de que sea una única forma de evaluación, actualizar las casillas de verificación */
			echo "<script language=\"javascript\" type=\"text/javascript\">";
			if ($id_limpio[0]->Subida == 1) {
				echo "document.getElementById (\"subida\").checked = true;";
			}
			/* Si tiene edición de materias */
			if ($id_limpio[0]->Materias == 1) {
				echo "document.getElementById (\"materias\").checked = true;";
			}
			echo "</script>";
		} ?>
	<input type="submit" value="Actualizar permisos" />
	</form>
</body>
</html>
