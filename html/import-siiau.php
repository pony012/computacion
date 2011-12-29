<?php
	function arreglar_n ($cadena) {
		return str_replace ("~", "ñ", $cadena);
	}
	
	function agregar_materia (&$materias, $clave, $descripcion) {
		$clave = strtoupper ($clave);
		
		if (isset ($materias [$clave])) return;
		
		$materias [$clave] = ucwords (strtolower (arreglar_n ($descripcion)));
	}
	
	function agregar_maestro (&$maestros, $linea) {
		$explote = explode (" ", $linea);
		
		$n = count ($explote);
		
		$codigo = trim ($explote [($n - 1)], "()");
		settype ($codigo, "string");
		if (isset ($maestros [$codigo])) {
			unset ($explote);
			return $codigo;
		}
		
		/* Separar los campos */
		if ($n == 3) {
			/* Sólo un nombre y código */
			$nombre = $explote[0];
			$apellido = $explote[1];
		} else if ($n == 4) {
			$nombre = $explote[0];
			$apellido = $explote[1] . " " . $explote[2];
		} else if ($n == 5) {
			/* Lo normal */
			$nombre = $explote[0] . " " . $explote[1];
			$apellido = $explote[2] . " " . $explote[3];
		} else if ($n == 6) {
			$nombre = $explote[0] . " " . $explote[1] . " " . $explote[2];
			$apellido = $explote[3] . " " . $explote[4];
		} else if ($n == 7) {
			$nombre = $explote[0] . " " . $explote[1] . " " . $explote[2];
			$apellido = $explote[3] . " " . $explote[4] . " " . $explote[5];
		}
		
		$nombre = ucwords (strtolower (arreglar_n ($nombre)));
		$apellido = ucwords (strtolower (arreglar_n ($apellido)));
		
		$maestros [$codigo] = array (0 => $nombre, 1 => $apellido);
		
		unset ($explote);
		return $codigo;
	}
	
	function agregar_alumno (&$alumnos, $codigo, $linea, $carrera) {
		settype ($codigo, "string");
		if (isset ($alumnos [$codigo])) return;
		
		$explote = explode (",", $linea);
		
		$apellido = trim (ucwords (strtolower (arreglar_n ($explote[0]))));
		$nombre = trim (ucwords (strtolower (arreglar_n ($explote[1]))));
		$carrera = trim (strtoupper ($carrera));
		
		$alumnos [$codigo] = array (0 => $nombre, 1 => $apellido, 2 => $carrera);
	}
	
	function agregar_seccion (&$secciones, $nrc, $materia, $seccion, $maestro) {
		settype ($nrc, "string");
		if (isset ($secciones [$nrc])) return;
		
		$secciones [$nrc] = array (0 => $materia, 1 => $seccion, 2 => $maestro);
	}
	
	if (($archivo = fopen ($argv[1], "r")) !== FALSE) {
		require_once 'mysql-con.php';
		
		/* Verificar que exista el Departamental 1 */
		$result = mysql_query ("SELECT * FROM Evaluaciones WHERE Id = 1", $mysql_con);
		
		if (mysql_num_rows ($result) == 0) {
			mysql_query ("INSERT INTO Evaluaciones VALUES (1, 'Departamental 1')", $mysql_con);
		}
		
		mysql_free_result ($result);
		
		$materias = array ();
		$alumnos = array ();
		$maestros = array ();
		$secciones = array ();
		
		/* Primera pasada, llenar los arreglos */
		while (($linea = fgetcsv ($archivo, 400, ",", "\"")) !== FALSE) {
			$no_campos = count ($linea);
			
			if ($no_campos < 20) {
				continue;
			}
			
			agregar_materia ($materias, $linea[1], $linea[2]);
			$codigo_del_maestro = agregar_maestro ($maestros, $linea[16]);
			agregar_alumno ($alumnos, $linea[17], $linea[18], $linea[19]);
			agregar_seccion ($secciones, $linea[0], $linea[1], $linea[3], $codigo_del_maestro);
		}
		
		
		/* Crear todo */
		$query_mat = "INSERT INTO Materias (Clave, Descripcion) VALUES ";
		
		
		foreach ($materias as $clave => $descripcion) {
			$query_mat = $query_mat . sprintf ("('%s', '%s'),", $clave, $descripcion);
			
			$query_sel = sprintf ("SELECT Clave FROM Porcentajes WHERE Clave = '%s' AND Tipo > 0", $clave);
			$result = mysql_query ($query_sel, $mysql_con);
			
			if (mysql_num_rows ($result) == 0) {
				if (!isset ($query_por)) $query_por = "INSERT INTO Porcentajes VALUES ";
				$query_por = $query_por . sprintf ("('%s', 1, 100),", $clave);
			}
			
			mysql_free_result ($result);
		}
		
		$query_mat = substr_replace ($query_mat, ";", -1);
		if (isset ($query_por)) $query_por = substr_replace ($query_por, ";", -1);
		
		/* Ejecutar los query extendidos */
		mysql_query ($query_mat, $mysql_con);
		if (isset ($query_por)) mysql_query ($query_por, $mysql_con);
		
		/* Ahora los maestros */
		$query_maestros = "INSERT INTO Maestros (Codigo, Nombre, Apellido, Correo, Flag) VALUES ";
		
		foreach ($maestros as $codigo => $value) {
			$query_maestros = $query_maestros . sprintf ("('%s', '%s', '%s', '', 0),", $codigo, $value[0], $value[1]);
			
			$query_sel = sprintf ("SELECT Codigo FROM Sesiones_Maestros WHERE Codigo = '%s'", $codigo);
			
			$result = mysql_query ($query_sel, $mysql_con);
			if (mysql_num_rows ($result) == 0) {
				/* Este maestro no tiene asociada una session
				 * Insertar el permiso, y agregarlo a la sesiones de los maestros */
				mysql_query ("INSERT INTO Permisos (id) VALUES (NULL)", $mysql_con);
				$last_id = mysql_insert_id ($mysql_con);
				
				if (!isset ($query_sesiones_m)) $query_sesiones_m = "INSERT INTO Sesiones_Maestros (Codigo, Pass, Permisos, Activo) VALUES ";
				
				$query_sesiones_m = $query_sesiones_m . sprintf ("('%s', MD5('12345'), %s, 1),", $codigo, $last_id);
			}
			mysql_free_result ($result);
		}
		
		$query_maestros = substr_replace ($query_maestros,  ";", -1);
		if (isset ($query_sesiones_m)) $query_sesiones_m = substr_replace ($query_sesiones_m, ";", -1);
		
		/* Ejecutar los querys */
		mysql_query ($query_maestros, $mysql_con);
		if (isset ($query_sesiones_m)) mysql_query ($query_sesiones_m, $mysql_con);
		
		/* Ahora los alumnos */
		$query_alumnos = "INSERT INTO Alumnos (Codigo, Carrera, Nombre, Apellido, Flag) VALUES ";
		$query_sesiones_al = "INSERT INTO Sesiones_Alumnos (Codigo, Pass, Permisos, Activo) VALUES ";
		
		foreach ($alumnos as $codigo => $value) {
			$query_alumnos = $query_alumnos . sprintf ("('%s', '%s', '%s', '%s', 0),", $codigo, $value[2], $value[0], $value[1]);
			$query_sesiones_al = $query_sesiones_al . sprintf ("('%s', MD5('12345'), 0, 1),", $codigo);
		}
		
		$query_alumnos = substr_replace ($query_alumnos, ";", -1);
		$query_sesiones_al = substr_replace ($query_sesiones_al, ";", -1);
		
		/* Ejecutar los querys */
		mysql_query ($query_alumnos, $mysql_con);
		mysql_query ($query_sesiones_al, $mysql_con);
		
		/* Crear los grupos correspondientes */
		$query_secciones = "INSERT INTO Secciones (Nrc, Materia, Seccion, Maestro) VALUES ";
		
		foreach ($secciones as $nrc => $value) {
			$query_secciones = $query_secciones . sprintf ("('%s', '%s', '%s', '%s'),", $nrc, $value[0], $value[1], $value[2]);
		}
		
		$query_secciones = substr_replace ($query_secciones, " ", -1) . " ON DUPLICATE KEY UPDATE Maestro=VALUES(Maestro)";
		
		mysql_query ($query_secciones, $mysql_con);
		
		unset ($alumnos);
		unset ($maestros);
		unset ($secciones);
		
		$evals = array ();
		
		/* Recuperar todas las formas de evaluacion */
		foreach ($materias as $clave => $descripcion) {
			$query_sel = sprintf ("SELECT Tipo FROM Porcentajes WHERE Clave = '%s'", $clave);
			$result = mysql_query ($query_sel);
			
			$evals [$clave] = array ();
			
			while (($object = mysql_fetch_object ($result))) {
				$evals [$clave][] = $object->Tipo;
			}
			
			mysql_free_result ($result);
		}
		
		unset ($materias);
		
		/* Rebobinar el archivo para empezar a insertar alumnos en los grupos */
		rewind ($archivo);
		
		mysql_query ("TRUNCATE TABLE Grupos", $mysql_con);
		mysql_query ("TRUNCATE TABLE Calificaciones", $mysql_con);
		
		while (($linea = fgetcsv ($archivo, 400, ",", "\"")) !== FALSE) {
			$no_campos = count ($linea);
			
			if ($no_campos < 20) continue;
			
			$query = sprintf ("INSERT INTO Grupos (Alumno, Nrc) VALUES ('%s', '%s');", $linea[17], $linea[0]);
			mysql_query ($query, $mysql_con);
			
			$query_evals = "INSERT INTO Calificaciones (Alumno, Nrc, Tipo, Valor) VALUES ";
			
			foreach ($evals[strtoupper ($linea[1])] as $value) {
				$query_evals = $query_evals . sprintf ("('%s', '%s', '%s', NULL),", $linea[17], $linea[0], $value);
			}
			
			$query_evals = substr_replace ($query_evals, ";", -1);
			
			mysql_query ($query_evals);
		}
		
		mysql_close ($mysql_con);
		fclose ($archivo);
	} else {
		/* No se pudo abrir el archivo */
		echo "Fallo al abrir el archivo";
	}
?>
