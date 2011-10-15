<?php
	session_start ();
	
	/* Primero verificar una sesión válida */
	if (!isset ($_SESSION['auth']) || $_SESSION['auth'] != 1) {
		/* Tenemos un intento de acceso inválido */
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_SESSION['permisos']['crear_materias']) || $_SESSION['permisos']['crear_materias'] != 1) {
		/* Privilegios insuficientes */
		header ("Location: vistas.php");
		exit;
	}
	
	$tiene_depa1 = isset ($_POST['depa1']) ? $_POST['depa1'] : "0";
	$tiene_depa2 = isset ($_POST['depa2']) ? $_POST['depa2'] : "0";
	$tiene_puntos = isset ($_POST['puntos']) ? $_POST['puntos'] : "0";
	
	$n_1 = isset ($_POST['porcentaje_depa1']) ? $_POST['porcentaje_depa1'] : 0;
	$n_2 = isset ($_POST['porcentaje_depa2']) ? $_POST['porcentaje_depa2'] : 0;
	$n_p = isset ($_POST['porcentaje_puntos']) ? $_POST['porcentaje_puntos'] : 0;
	
	settype ($n_1, "integer");
	settype ($n_2, "integer");
	settype ($n_p, "integer");
	
	if ($tiene_depa1 != "1" && $tiene_depa2 != "1" && $tiene_puntos != "1") {
		/* Si no hay marcada ninguna forma de evaluación,
		 * regresar un error */
		header ("Location: materias.php?e=eval");
		exit;
	}
	
	if ($n_1 < 0 || $n_2 < 0 || $n_p < 0) {
		/* No números negativos */
		header ("Location: materias.php?e=neg");
		exit;
	}
	
	$suma = $n_1 + $n_2 + $n_p;
	
	if ($suma != 100) {
		/* Suma incorrecta */
		header ("Location: materias.php?e=suma");
		exit;
	}
	
	/* Validar la clave la materia */
	if (!preg_match ("/^([A-Za-z]){2}([0-9]){3}$/", $_POST['clave'])) {
		header ("Location: materias.php?e=clave");
		exit;
	}
	
	/* TODO: Convertir la materia a mayúsculas */
	
	require '../mysql-con.php';
	/*INSERT INTO `computacion`.`Materias` (`Clave`, `Descripcion`, `Depa1`, `Depa2`, `Puntos`, `Porcentaje_Depa1`, `Porcentaje_Depa2`, `Porcentaje_Puntos`) VALUES ('cc123', 'dgfhjk', '1', '0', '1', '23', NULL, '77');*/
	$query = "INSERT INTO Materias (Clave, Descripcion, Depa1, Depa2, Puntos, Porcentaje_Depa1, Porcentaje_Depa2, Porcentaje_Puntos) ";
	$query = sprintf ("%s VALUES ('%s', '%s', '%s', '%s', '%s'", $query, mysql_real_escape_string ($_POST['clave']), mysql_real_escape_string ($_POST['descripcion']), (($tiene_depa1 == "1") ? "1" : "0"), (($tiene_depa2 == "1") ? "1" : "0"), (($tiene_puntos == "1") ? "1" : "0"));
	
	$query = sprintf ("%s, %s, %s, %s);", $query, ($n_1 == 0) ? "NULL" : "'".$n_1."'", ($n_2 == 0) ? "NULL" : "'".$n_2."'", ($n_p == 0) ? "NULL" : "'".$n_p."'");
	
	$result = mysql_query ($query, $mysql_con);
	
	if (mysql_affected_rows() <= 0) {
		header ("Location: materias.php?e=desconocido");
	} else {
		header ("Location: materias.php?m=ok");
	}
	
	mysql_close ($mysql_con);
	exit;
?>
