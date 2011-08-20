<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="Félix Arreola Rodríguez" />
	<title><?php
	require_once '../global-config.php'; # Debería ser Require 'global-config.php'
	echo $cfg['nombre'];
	?></title>
	<script language="javascript" src="../scripts/md5.js" type="text/javascript"></script>
	<script language="javascript" type="text/javascript">
	function validar () {
		var x = document.getElementById("user").value
		var y = document.getElementById("pass").value
		
		if (x == null || x == "" || y == null || y == "") return false;

		// En caso contrario, encriptar la contraseña
		var m = MD5 (y);
		document.getElementById("md5").value = m;
		document.getElementById("pass").value = null;
		
		return true;
	}
	</script>
</head>
<body>
<h1>Ingresar al sistema</h1>
	<form action="auth.php" method="post" onsubmit="return validar()">
		<table>
			<tr>
			<td>Código</td>
			<td><input type="text" maxlength="7" name="user" id="user" /></td>
			</tr>
			<tr>
			<td>Contraseña</td>
			<td><input type="password" name="pass" id="pass" />
				<input type="hidden" name="md5" id="md5" /></td>
			</tr>
			<tr>
			<td align="right" colspan="2"><input type="submit" value="Ingresar" /></td>
			</tr>
		</table>
	</form>
</body>
</html>
