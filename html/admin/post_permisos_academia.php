<?
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	require_once 'mensajes.php';
	
	header ("Location: academias.php");
	
	if (!isset ($_SESSION['permisos']['admin_academias']) || $_SESSION['permisos']['admin_academias'] != 1) {
		/* Privilegios insuficientes */
		agrega_mensaje (3, "Privilegios insuficientes");
		exit;
	}
	
	/* Verficar la cantidad de ids que recibo */
	if (!isset ($_POST['id']) || !is_array ($_POST['id'])) {
		agrega_mensaje (3, "Error desconocido");
		exit;
	}
	
	$id_limpio = array ();
	
	foreach ($_POST['id'] as $value) {
		$id_limpio[] = strval (intval ($value));
	}
	
	require_once '../mysql-con.php';
	
	foreach ($id_limpio as $index => $value) {
		$query = sprintf ("SELECT Id, Maestro FROM Academias WHERE Id = '%s'", $value);
		
		$result = mysql_query ($query, $mysql_con);
		
		/* Academia, ¿Existes? */
		if (mysql_num_rows ($result) == 0) { /* No existo, quitar de la lista */
			unset ($id_limpio[$index]);
		} else { /* Existo, ¿tengo presidente? */
			$academia = mysql_fetch_object ($result);
			if (is_null ($academia->Maestro)) { /* No, no tengo presidente, quitar de la lista con mensaje de error */
				agrega_mensaje (1, sprintf ("No se pueden establecer los permisos porque la academia %s no tiene presidente", $academia->Academia));
				unset ($id_limpio[$index]);
			}/* Sí tengo, recuperar todos los datos, y dejarlos en el arreglo */
		}
		mysql_free_result ($result);
	}
	
	if (count ($id_limpio) == 0) {
		exit;
	}
	
	if (isset ($_POST['materias']) && $_POST['materias'] == "1") $permiso_materia = "1";
	if (isset ($_POST['subida']) && $_POST['subida'] == "1") $permiso_subida = "1";
	
	$query = sprintf ("UPDATE Academias SET Materias = '%s', Subida = '%s' WHERE ", $permiso_materia, $permiso_subida);
	
	foreach ($id_limpio as $value) {
		$query = $query . sprintf ("Id = '%s' OR ", $value);
	}
	
	$query = substr_replace ($query, ";", -3);
	
	mysql_query ($query, $mysql_con);
	
	agrega_mensaje (0, "Permisos actualizados");
?>
