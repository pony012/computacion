<?php
	$mysql_user = "iccuc634_comp";
	$mysql_pass = "computacion";
	$mysql_server = "localhost";
	$mysql_database = "iccuc634_comp";

	$mysql_con = mysql_connect ($mysql_server, $mysql_user, $mysql_pass, FALSE) or die ("Falló al conectar a la base de datos");
	mysql_select_db  ($mysql_database, $mysql_con);
	
	mysql_query("set names 'utf8'", $mysql_con);
?>
