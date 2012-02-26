<?php
	require_once 'global-config.php';
	
	$mysql_con = null;
	
	function database_connect () {
		global $mysql_con, $cfg;
		/* Si la base de datos ya está abierta, no hacer nada */
		if (!is_null ($mysql_con)) return;
		
		$mysql_con = mysql_connect ($cfg['mysql_server'], $cfg['mysql_user'], $cfg['mysql_pass'], FALSE) or die ("Falló al conectar a la base de datos");
		mysql_select_db ($cfg['mysql_database'], $mysql_con);
		
		mysql_query("set names 'utf8'", $mysql_con);
	}
?>
