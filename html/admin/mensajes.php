<?php
	if (isset ($_SESSION['mensaje'])) {
		switch ($_SESSION['m_tipo']) {
			case 0:
				echo "<div class=\"info\">";
				break;
			case 1:
				echo "<div class=\"advertencia\">";
				break;
			case 2:
				echo "<div class=\"pregunta\">";
				break;
			case 3:
				echo "<div class=\"error\">";
				break;
		}
		
		echo "<p>";
		switch ($_SESSION['m_klass']) {
			case 'unknown':
				echo "Ha ocurrido un error desconocido";
				break;
			case 'm_wrong':
			case 's_wrong':
				echo "Se han introducido valores incorrectos";
				break;
			case 'm_clave':
			case 's_materia':
				echo "Se ha especificado una clave de materia incorrecta";
				break;
			case 'm_r_uso':
				echo "La materia no puede ser eliminada, se encuentra en uso";
				break;
			case 'm_n_ok':
				echo "La materia fué creada exitosamente";
				break;
			case 'm_a_ok':
				echo "La materia fué actualizada correctamente";
				break;
			case 'n_js':
				echo "Su solicitud no puede ser procesada, por favor intente otra vez";
				break;
			case 'm_r_ok':
				echo "La materia ha sido eliminada con éxito";
				break;
			case 's_maestro':
				echo "El maestro especificado no existe";
				break;
			case 's_ok':
				echo "La sección fué creada exitosamente";
				break;
			case 's_nrc':
				echo "El nrc especificado no es válido";
				break;
			case 's_r_uso':
				echo "El grupo está en uso, no puede ser eliminado";
				break;
			case 's_r_ok':
				echo "El grupo ha sido eliminado";
				break;
			case 's_r_no':
				echo "Falló la eliminación del grupo";
				break;
			case 's_a_ok':
				echo "El grupo fué actualizado correctamente";
				break;
		}
		
		echo "</p></div>";
		
		unset ($_SESSION['mensaje']);
		unset ($_SESSION['m_tipo']);
		unset ($_SESSION['m_klass']);
	}
?>
