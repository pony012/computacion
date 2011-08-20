<?php
	session_start ();
	
	/* Variables que recibimos por $POST:
	 # user -> nombre de usuario
	 # md5 -> la contraseña
	 */
	
	require_once "../mysql-con.php";
	
	$query = sprintf ("SELECT s.codigo, s.permisos, m.nombre FROM Sesiones_Maestros AS s INNER JOIN Maestros AS m ON s.codigo = m.codigo WHERE s.codigo='%s' AND s.pass='%s' AND s.activo=1 LIMIT 1", mysql_real_escape_string ($_POST['user']), mysql_real_escape_string ($_POST['md5']));
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_num_rows ($result) > 0) {
		$object = mysql_fetch_object ($result);
		
		/* Empezar a rellenar datos de la sesion */
		$_SESSION['auth'] = 1;
		$_SESSION['nombre'] = $object->nombre;
		$_SESSION['codigo'] = $object->codigo;
		
		mysql_free_result ($result);
		
		/* TODO: Consultar la tabla de permisos y subirla a $Session */
		
		mysql_close ($mysql_con);
		
		header ("Location: Correcto.php");
		exit;
	}
	
	mysql_free_result ($result);
	mysql_close ($mysql_con);
		
	header ("Location: login.php");
	exit
?>
