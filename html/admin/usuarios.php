<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
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
<body>
	<h1>Maestros:</h1>
	<?php
		require_once "../mysql-con.php";
		
		/* Recuperar la cantidad total de filas */
		$result = mysql_query ("SELECT COUNT(*) AS TOTAL FROM Maestros", $mysql_con);
		$row = mysql_fetch_object ($result);
		$total = $row->TOTAL;
		mysql_free_result ($result);
		
		$offset = (isset ($_GET['off'])) ? $_GET['off'] : 0;
		settype ($offset, "integer");
		$cant = 30;
		$show = $cant;
		
		if ($offset >= $total) $offset = $total - $cant;
		if ($offset < 0) $offset = 0;
		if (($offset + $cant) >= $total) $show = $total - $offset;
		
		echo "<p>Mostrando registros del ". ($offset + 1) ." al ". ($offset + $show + 1) . "</p>";
		
		echo "<table border=\"1\">";
		
		/* Mostrar la cabecera */
		echo "<thead><tr><th>Código</th><th>Nombre</th>";
		if ($_SESSION['permisos']['aed_usuarios'] == 1) {
			echo "<th>Activo</th><th>Editar</th>";
		}
		echo "</tr></thead>\n";
		
		/* Empezar la consulta mysql */
		$query = "SELECT m.codigo, m.Apellido, m.nombre, s.activo FROM Maestros AS m INNER JOIN Sesiones_Maestros AS s ON m.Codigo = s.Codigo LIMIT ". $offset . ",". $cant;
		
		$result = mysql_query ($query, $mysql_con);
		
		echo "<tbody>";
		while (($object = mysql_fetch_object ($result))) {
			echo "<tr>";
			echo "<td>".$object->codigo."</td>";
			echo "<td>".$object->Apellido." ".$object->nombre."</td>";
			if ($_SESSION['permisos']['aed_usuarios'] == 1) {
				if ($object->activo == 1) {
					echo "<td><img src=\"../img/day.png\" /></td>";
				} else {
					echo "<td><img src=\"../img/night.png\" /></td>";
				}
			}
			echo "</tr>\n";
		}
		
		echo "</tbody>";
		echo "</table>\n";
		
		echo "<p>";
		
		$next = $offset + $show;
		$ultimo = $total - ($total % $cant);
		if ($next >= $total) $next = $ultimo;
		$prev = $offset - $cant;
		if ($prev < 0) $prev = 0;
		
		/* Mostrar las flechas de dezplamiento */
		if ($offset > 0) {
			echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?off=0\"><img class=\"icon\" src=\"../img/first.png\" /></a>\n";
			echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?off=".$prev."\"><img class=\"icon\" src=\"../img/prev.png\" /></a>\n";
		}
		if ($offset + $show < $total - 1) { 
			echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?off=".$next."\"><img class=\"icon\" src=\"../img/next.png\" /></a>\n";
			echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?off=".$ultimo."\"><img class=\"icon\" src=\"../img/last.png\" /></a>\n";
		}
		
		echo "</p>\n";
		
		if ($_SESSION['permisos']['aed_usuarios'] == 1) { ?>
	<ul>
	<li><a href="nuevo_usuario.php?t=u">Agregar un nuevo usuario</a></li>
	<li><a href="nuevo_usuario.php?t=m">Agregar nuevo maestro</a></li>
	</ul><?php } ?>
</body>
</html>
