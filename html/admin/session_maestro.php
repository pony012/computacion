<?php
	define('ADMIN_PATH', dirname(__FILE__));
	define('ROOT_PATH', dirname(__FILE__).'/..');
	set_include_path (get_include_path()
		.PATH_SEPARATOR.ROOT_PATH
		.PATH_SEPARATOR.ADMIN_PATH
	);
	
	require_once 'global-config.php';
	require_once 'mysql-con.php';
	
	$session_name = $cfg['clave_departamento'] . "_" . $cfg['calendario'];
	session_name ($session_name);
	session_set_cookie_params ($cfg['session_timeout']);
	session_start ();
	
	function check_valid_session () {
		if (!isset ($_SESSION['auth_m']) || $_SESSION['auth_m'] != true) {
			header ("Location: login.php");
			exit;
		}
	}
	
	function new_session ($maestro) {
		global $mysql_con;
		
		/* Crear una nueva session */
		session_regenerate_id (true);
		
		$_SESSION['auth_m'] = true;
		
		$_SESSION['codigo'] = $maestro->Codigo;
		
		/* En caso de utilizar mysql para acceder a los permisos,
		 * no guardar los permisos en la sesion */
		
		database_connect ();
		
		$query = sprintf ("SELECT * FROM Permisos WHERE id = '%s'", $maestro->Permisos);
		
		$result = mysql_query ($query, $mysql_con);
		$permisos = mysql_fetch_assoc ($result);
		
		$_SESSION['permisos'] = $permisos;
		
		mysql_free_result ($result);
	}
	
	function has_permiso ($permiso) {
		/* Si los permisos se utilizan directamente desde la base de datos,
		 * Hacer la consulta aquí */
		if (!isset ($_SESSION[$permiso])) return FALSE;
		
		if ($_SESSION[$permiso] != 1) return FALSE;
		return TRUE;
	}
?>
