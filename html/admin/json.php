<?php
	session_start ();
	
	/* TODO: ¿Permitir autenticación por $_GET? */
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) exit;
	
	if (!isset ($_GET['modo'])) exit;
	
	if ($_GET['modo'] == 'evals') {
		if (!isset ($_GET['materia'])) exit;
		if (isset ($_GET['exclusiva']) && $_GET['exclusiva'] == 1) $exclu = 1;
		else $exclu = 0;
		
		$mate = strtoupper ($_GET['materia']);
		
		require_once '../mysql-con.php';
		$query = sprintf ("SELECT P.Tipo, E.Descripcion FROM Porcentajes AS P INNER JOIN Evaluaciones AS E ON P.Tipo = E.Id WHERE P.Clave='%s' AND E.Exclusiva=%s", $mate, $exclu);
		$result = mysql_query ($query, $mysql_con);
		
		$json = array ();
		
		while (($object = mysql_fetch_object ($result))) {
			$json[] = $object;
		}
		
		$json_string = json_encode ($json);
		
		printf ($json_string);
		exit;
	}
?>
