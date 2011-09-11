<?php
	session_start ();
	
	/* Variables que recibimos por $POST:
	 # user -> nombre de usuario
	 # md5 -> la contraseña
	 */
	
	/* Verificar que haya datos POST */
	if (!isset ($_POST['user']) || !isset ($_POST['md5'])) {
		header ("Location: login.php");
		exit;
	}
	
	require_once "../mysql-con.php";
	
	/* Sanitizado de variables */
	filter_input (INPUT_POST, 'user', FILTER_SANITIZE_NUMBER_INT);
	
	$query = sprintf ("SELECT s.codigo, s.permisos, m.nombre FROM Sesiones_Maestros AS s INNER JOIN Maestros AS m ON s.codigo = m.codigo WHERE s.codigo='%s' AND s.pass='%s' AND s.activo=1 LIMIT 1", mysql_real_escape_string ($_POST['user']), mysql_real_escape_string ($_POST['md5']));
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) > 0) {
		$user = mysql_fetch_object ($result);
		
		/* Empezar a rellenar datos de la sesion */
		$_SESSION['auth'] = 1;
		$_SESSION['nombre'] = $user->nombre;
		$_SESSION['codigo'] = $user->codigo;
		
		mysql_free_result ($result);
		
		/* Ahora recuperar la tabla de permisos */
		$query = sprintf ("SELECT p.* FROM Permisos AS p INNER JOIN Sesiones_Maestros AS s ON p.id = s.permisos WHERE s.permisos='%s' LIMIT 1", $user->permisos);
		$result = mysql_query ($query, $mysql_con);
		$object = (array)mysql_fetch_object ($result);
		
		$_SESSION['permisos'] = array ();
		foreach($object as $key => $valor) $_SESSION['permisos'][$key] = $valor;
		mysql_free_result ($result);
		
		mysql_close ($mysql_con);
		
		header ("Location: vistas.php");
		exit;
	}
	
	mysql_free_result ($result);
	mysql_close ($mysql_con);
		
	header ("Location: login.php");
	exit
?>
