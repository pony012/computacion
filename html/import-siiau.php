<?php
	function mostrar_ayuda () {
		echo "Importador de datos de siiau \n";
		
		echo "Uso:\n";
		echo $argv[0] + " [nombre de archivo]";
		
		exit;
	}
	
	function arreglar_n ($cadena) {
		return str_replace ("~", "ñ", $cadena);
	}
	
	function agregar_materia ($mysql_con, $clave, $descripcion) {
		$clave = strtoupper ($clave);
		
		$query = sprintf ("SELECT * FROM Materias WHERE Clave='%s' LIMIT 1", $clave);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (mysql_num_rows ($result) == 0) {
			/* Hay que agregar esta materia */
			$descripcion = ucwords (strtolower (arreglar_n ($descripcion)));
			
			$query = sprintf ("INSERT INTO Materias VALUES ('%s', '%s')", $clave, $descripcion);
			
			mysql_query ($query, $mysql_con);
			
			/* Agregarle una forma de evaluación */
			$query = sprintf ("INSERT INTO Porcentajes VALUES ('%s', 1, 100)", $clave);
			mysql_query ($query, $mysql_con);
		}
		
		mysql_free_result ($result);
		unset ($query);
	}
	
	function crear_maestro ($mysql_con, $maestro) {
		/* Primero separar el nombre del código */
		/* NANCY MICHELLE TORRES VILLANUEVA (2906961) */
		$explote = explode (" ", $maestro);
		
		$n = count ($explote);
		if ($n == 3) {
			/* Sólo un nombre y código */
			$nombre = $explote[0];
			$apellido = $explote[1];
			$codigo = trim ($explote[2], "()");
		} else if ($n == 4) {
			$nombre = $explote[0];
			$apellido = $explote[1] . " " . $explote[2];
			$codigo = trim ($explote[3], "()");
		} else if ($n == 5) {
			/* Lo normal */
			$nombre = $explote[0] . " " . $explote[1];
			$apellido = $explote[2] . " " . $explote[3];
			$codigo = trim ($explote[4], "()");
		} else if ($n == 6) {
			$nombre = $explote[0] . " " . $explote[1] . " " . $explote[2];
			$apellido = $explote[3] . " " . $explote[4];
			$codigo = trim ($explote[5], "()");
		} else if ($n == 7) {
			$nombre = $explote[0] . " " . $explote[1] . " " . $explote[2];
			$apellido = $explote[3] . " " . $explote[4] . " " . $explote[5];
			$codigo = trim ($explote[6], "()");
		}
		
		unset ($explote);
		
		$query = sprintf ("SELECT * FROM Maestros WHERE Codigo='%s'", $codigo);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (mysql_num_rows ($result) == 0) {
			$nombre = ucwords (strtolower (arreglar_n ($nombre)));
			$apellido = ucwords (strtolower (arreglar_n ($apellido)));
			
			/* Hay que agregar al maestro */
			$query = sprintf ("INSERT INTO Maestros (Codigo, Nombre, Apellido, Correo, Flag) VALUES ('%s', '%s', '%s', '', 0)", $codigo, $nombre, $apellido);
			$m1 = mysql_query ($query, $mysql_con);
			
			$query = "INSERT INTO Permisos (id) VALUES (NULL)";
			
			$m2 = mysql_query ($query, $mysql_con);
			$ultimo_permiso = mysql_insert_id ($mysql_con);
			
			$query = sprintf ("INSERT INTO Sesiones_Maestros (Codigo, Pass, Permisos, Activo) VALUES ('%s', MD5('12345'), %s, 1)", $codigo, $ultimo_permiso);
			
			$m3 = mysql_query ($query, $mysql_con);
		}
		mysql_free_result ($result);
		return $codigo;
	}
	
	function crear_alumno ($mysql_con, $codigo, $alumno, $carrera) {
		$explote = explode (",", $alumno);
		
		$query = sprintf ("SELECT * FROM Alumnos WHERE Codigo='%s'", $codigo);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (mysql_num_rows ($result) == 0) {
			$apellidos = trim (ucwords (strtolower (arreglar_n ($explote[0]))));
			$nombre = trim (ucwords (strtolower (arreglar_n ($explote[1]))));
			$carrera = trim (strtoupper ($carrera));
			
			/* hay que agregar el alumno */
			
			$query = sprintf ("INSERT INTO Alumnos (Codigo, Carrera, Nombre, Apellido, Flag) VALUES ('%s', '%s', '%s', '%s', 0)", $codigo, $carrera, $nombre, $apellidos);
			
			$m1 = mysql_query ($query, $mysql_con);
			
			$query = sprintf ("INSERT INTO Sesiones_Alumnos (Codigo, Pass, Permisos, Activo) VALUES ('%s', MD5('12345'), 0, 1)", $codigo);
			
			$m2 = mysql_query ($query, $mysql_con);
		}
		
		mysql_free_result ($result);
	}
	
	function crear_seccion ($mysql_con, $nrc, $materia, $seccion, $maestro) {
		$seccion = strtoupper ($seccion);
		
		$query = sprintf ("SELECT * FROM Secciones WHERE Nrc='%s'", $nrc);
		$result = mysql_query ($query, $mysql_con);
		
		if (mysql_num_rows ($result) == 0) {
			$query = sprintf ("INSERT INTO Secciones (Nrc, Materia, Maestro, Seccion) VALUES ('%s', '%s', '%s', '%s')", $nrc, $materia, $maestro, $seccion);
			
			$m1 = mysql_query ($query, $mysql_con);
		}
		
		mysql_free_result ($result);
	}
	
	function agregar_grupo ($mysql_con, $nrc, $alumno) {
		$query = sprintf ("SELECT * FROM Grupos WHERE Alumno='%s' AND Nrc='%s'", $alumno, $nrc);
		
		$result = mysql_query ($query, $mysql_con);
		
		if (mysql_num_rows ($result) == 0) {
			$query = sprintf ("INSERT INTO Grupos (Alumno, Nrc, Promedio) VALUES ('%s', '%s', 0)", $alumno, $nrc);
			
			$m1 = mysql_query ($query, $mysql_con);
		}
		
		mysql_free_result ($result);
	}
	
	if ($argc < 2) {
		mostrar_ayuda ();
	}
	
	/* NRC,CLAVE,MATERIA,SEC,CRED,DEPTO,INI,FIN,L,M,I,J,V,S,EDIF,AULA,PROFESOR,COD_AL,ALUMNO,CAR_AL */
	/* 0  ,1    ,2      ,3  ,4   ,5    ,6  ,7  ,8, , , , , ,    ,    ,16      ,17    ,18    ,19     */
	
	$g = 0;
	if (($archivo = fopen ($argv[1], "r")) !== FALSE) {
		require_once 'mysql-con.php';
		
		/* Verificar que exista el Departamental 1 */
		$result = mysql_query ("SELECT * FROM Evaluaciones WHERE Id = 1", $mysql_con);
		
		if (mysql_num_rows ($result) == 0) {
			mysql_query ("INSERT INTO Evaluaciones VALUES (1, 'Departamental 1')", $mysql_con);
		}
		
		mysql_free_result ($result);
		
		while (($linea = fgetcsv ($archivo, 400, ",", "\"")) !== FALSE) {
			$no_campos = count ($linea);
			
			$g++;
			
			if ($no_campos < 20) {
				echo "Advertencia, la linea " . $g . " tiene menos de 20 campos\n";
				continue;
			}
			
			agregar_materia ($mysql_con, $linea[1], $linea[2]);
			$codigo_del_maestro = crear_maestro ($mysql_con, $linea[16]);
			crear_alumno ($mysql_con, $linea[17], $linea[18], $linea[19]);
			crear_seccion ($mysql_con, $linea[0], $linea[1], $linea[3], $codigo_del_maestro);
			agregar_grupo ($mysql_con, $linea[0], $linea[17]);
		}
	} else {
		/* No se pudo abrir el archivo */
		echo "Fallo al abrir el archivo";
		exit;
	}
	
	fclose ($archivo);
?>
