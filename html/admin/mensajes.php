<?php
	require_once 'session_maestro.php';
	
	function mostrar_mensajes () {
		if (!isset ($_SESSION['mensajes'])) return;
		foreach ($_SESSION['mensajes'] as $index => $m) {
			switch ($m['tipo']) {
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
			
			printf ("<p>%s</p></div>", $m['cadena']);
			unset ($_SESSION['mensajes'][$index]);
		}
		
		unset ($_SESSION['mensajes']);
	}
	function agrega_mensaje ($tipo, $cadena) {
		if (!isset ($_SESSION['mensajes'])) $_SESSION['mensajes'] = array ();
		
		$_SESSION['mensajes'][] = array ('tipo' => $tipo, 'cadena' => $cadena);
	}
?>
