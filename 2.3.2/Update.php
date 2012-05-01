<?php
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart © 2012 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

require('Init.php');

if($G['do'] == "update")
{
	$database = file_get_contents("http://beatrock.infosmart.mx/releases/PHP/Updates_2.3.1-2.3.2.sql");
	$database = str_replace("{DB_ALIAS}", DB_ALIAS, $database);
	
	$db = explode(";", $database);
	$qs = Array();
	
	foreach($db as $query)
	{
		$query = trim($query);
			
		if(empty($query))
			continue;
			
		$qs[] = $query;
		
		BitRock::$ignore = true;
		mysql_query($query) or $eq = $query;
	}
	
	if(empty($eq))
	{
		$complete = true;
		$audio = Media::Voice("Gracias por actualizar.", "es", "");
	}
	else
		$error = true;
}
else
{
	$audio = Media::Voice("Asistente de actualización", "es", "");
}

// Recursos de la instalación.
define("RESOURCES_INS", "//resources.infosmart.mx");
?>
<!DOCTYPE html>
<html>
<head>
	<title>Actualización de BeatRock</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-15" />
	<meta http-equiv="content-language" content="es" />

	<meta name="publisher" content="InfoSmart." />
	<meta name="copyright" content="© 2012 InfoSmart. Desarrollado con BeatRock">
	
	<meta name="robots" content="noodp, nofollow, noindex" />
	
	<script>
	var Site = "<?php if(defined("PATH")) { echo PATH; } else { echo '.'; } ?>";
	var Resources_Sys = "<?=RESOURCES_INS?>";
	</script>
	
	<link href="<?=RESOURCES_INS?>/system/css/style.css" rel="stylesheet" />
	<link href="<?=RESOURCES_INS?>/system/setup/style.install.css" rel="stylesheet" />
	
	<script src="<?=RESOURCES_INS?>/system/js/jquery.js"></script>
	<script src="<?=RESOURCES_INS?>/system/js/functions.kernel.js"></script>
	<script src="<?=RESOURCES_INS?>/system/setup/functions.install.js"></script>
</head>
<body>
	<div class="page" id="page">
		<header>
			<figure>
				<img src="<?=RESOURCES_INS?>/system/setup/Logo.png" />
			</figure>
			
			<h1>BeatRock 2.3.1 -> 2.3.2</h1>
		</header>
		
		<div class="content">
			<?php if($complete == true) { ?>
			<h2>¡Gracias por actualizar BeatRock!</h2>
			
			<p>
				La actualización se ha realizado correctamente, ahora proceda a eliminar este archivo.
			</p>
			
			<p>
				<b>Cambios realizados:</b>
			</p>
			
			<p>
				<?php foreach($qs as $q) { ?>
				<label style="font-family: Consolas; font-size: 12px;"><?=$q?></label><br /><br />
				<?php } ?>
			</p>
			<?php } else if($error == true) { ?>
			<h2>¡Uy! Ocurrio un error</h2>
			
			<p>
				Ha ocurrido un error al intentar ejecutar '<?=$eq?>'. Intentelo de nuevo.
			</p>
			
			<?php } else { ?>
			<p>
				Bienvenido al Asistente de actualización de BeatRock, el asistente actualizará de forma automatica su base de datos para aceptar la nueva versión de BeatRock. Tenga en cuenta que es necesario una conexión a Internet.
			</p>
			
			<p>
				Por ahora tendrá que reemplazar cuidadosamente los archivos de BeatRock para actualizarlo complementamente, visite el <a href="http://beatrock.infosmart.mx/f/">foro</a>, seguro encontrará un tema sobre como hacerlo.
			</p>
			
			<p>
				Al proceder se creará un Backup de aplicación con base de datos, por la cual si surge algún error podrá restaurar su aplicación con el Backup, aún así sugerimos crear un Backup de forma manual.
			</p>
			
			<p class="center">
				<a href="<?=PATH?>/Update.php?do=update" class="ibtn">Actualizar base de datos</a>
			</p>
			<?php } ?>
		</div>
	</div>
	
	<?=$audio?>
</body>
</html>