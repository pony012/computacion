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
	
	if (!isset ($_POST['modo']) || ($_POST['modo'] != 'nuevo' && $_POST['modo'] != 'editar')) {
		header ("Location: materias.php");
		exit;
	}
	
	if (count ($_POST['evals']) != count ($_POST['porcentajes']) || count ($_POST['evals']) <= 0) {
		header ("Location: materias.php?e=eval");
		exit;
	}
	
	require_once '../mysql-con.php';
	
	/* Recuperar las formas de evaluación */
	$result = mysql_query ("SELECT * FROM Evaluaciones", $mysql_con);
	while (($row = mysql_fetch_row ($result)) != FALSE) $ar[$row[0]] = $row[1];
	mysql_free_result ($result);
	
	$suma = 0;
	for ($g = 0; $g < count ($_POST['evals']); $g++) {
		settype ($_POST['evals'][$g], 'integer');
		settype ($_POST['porcentajes'][$g], 'integer');
		
		if ($_POST['porcentajes'][$g] <= 0) {
			header ("Location: materias.php?e=neg");
			exit;
		}
		
		if (!isset ($ar[$_POST['evals'][$g]])) {
			unset ($_POST['evals'][$g]);
			unset ($_POST['porcentajes'][$g]);
			continue;
		}
		$suma += $_POST['porcentajes'][$g];
		unset ($ar[$_POST['evals'][$g]]);
	}
	
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
	
	$_POST['clave'] = strtoupper ($_POST['clave']);
	
	if ($_POST['modo'] == 'nuevo') {
		/* INSERT INTO `computacion`.`Materias` (`Clave`, `Descripcion`) VALUES ('as123', 'sadfgh'); */
		$query = sprintf ("INSERT INTO Materias (Clave, Descripcion) VALUES ('%s', '%s');", $_POST['clave'], mysql_real_escape_string ($_POST['descripcion']));
		
		$result = mysql_query ($query, $mysql_con);
	
		if (!$result) {
			header ("Location: materias.php?e=desconocido");
			exit;
		}
		
		/* Ahora insertar los porcentajes de evaluación */
		/* INSERT INTO `computacion`.`Porcentajes` (`Clave`, `Tipo`, `Ponderacion`) VALUES ('as123', '1', '60'), ('as123', '2', '40'); */
		for ($g = 0; $g < count ($_POST['evals']); $g++) {
			$query = sprintf ("INSERT INTO Porcentajes (Clave, Tipo, Ponderacion) VALUES ('%s', '%s', '%s');", $_POST['clave'], $_POST['evals'][$g], $_POST['porcentajes'][$g]);
			
			$result = mysql_query ($query, $mysql_con);
		}
		
		header ("Location: materias.php?a=ok");
		exit;
	} else if ($_POST['modo'] == 'editar') {
		echo "Actualizar\n";
		exit;
		$query = sprintf ("UPDATE Materias SET Descripcion='%s', Depa1='%s', Depa2='%s', Puntos='%s', Porcentaje_Depa1=%s, Porcentaje_Depa2=%s, Porcentaje_Puntos=%s WHERE Clave='%s'", mysql_real_escape_string ($_POST['descripcion']), $tiene_depa1, $tiene_depa2, $tiene_puntos, $n_1, $n_2, $n_p, mysql_real_escape_string ($_POST['clave']));
		
		$result = mysql_query ($query, $mysql_con);
		
		if (!$result) {
			header ("Location: materias.php?a=desconocido");
		} else {
			header ("Location: materias.php?a=ok");
		}
	}
	
	mysql_close ($mysql_con);
	exit;
?>
