<?php
/*
# Begin License Block GPL
*/

	$cfg = array ();
	
	/* El nombre del sitio, algo como:
	 * Departamento de computación, o departamento de matemáticas
	 */
	$cfg['nombre'] = 'Departamento de computación';
	
	/* La clave del departamento
	 * Algo como:
	 * dcc - Ciencias computacionales
	 * depel - Electrónica
	 *
	 * No debe exceder de 5 letras
	 */
	$cfg['clave_departamento'] = 'dcc';
	
	/* Calendario de la base de datos */
	$cfg['calendario'] = '2012A';
	
	/* Datos de la conexión a la base de datos */
	/* Servidor mysql */
	$cfg['mysql_server'] = "localhost";
	
	/* Usuario para conexión de la base de datos */
	$cfg['mysql_user'] = "iccuc634_comp";
	
	/* Contraseña para el usuario */
	$cfg['mysql_pass'] = "computacion";
	
	/* Base de datos a cuál conectarse */
	$cfg['mysql_database'] = "iccuc634_comp";
	
	/* Tiempo de vida de la sessión, en segundos */
	$cfg['session_timeout'] = 900; /* 15 minutos */
	
	return $cfg;
?>
