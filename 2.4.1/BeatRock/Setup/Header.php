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

if(!defined("BEATROCK"))
	exit;
	
// Recursos de la instalación.
define("RESOURCES_INS", "//resources.infosmart.mx");
?>
<!DOCTYPE html>
<html>
<head>
	<title>Instalación de BeatRock</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-15" />
	<meta http-equiv="content-language" content="es" />

	<meta name="publisher" content="InfoSmart." />
	<meta name="copyright" content="© 2012 InfoSmart. Desarrollado con BeatRock">
	
	<meta name="robots" content="noodp, nofollow, noindex" />
	
	<script>
	var Site = "<?php if(defined("PATH")) { echo PATH; } else { echo '.'; } ?>";
	var Path = "<?php if(defined("PATH")) { echo PATH; } else { echo '.'; } ?>";
	var Resources_Sys = "<?=RESOURCES_INS?>/system";
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
			
			<h1><?=$page['name']?></h1>
		</header>
		
		<?php if(!empty($error)) { ?>
		<div class="box-error block">
			Han ocurrido uno o varios problemas:
			<?php foreach($error as $e) { echo "<li>$e</li>"; } ?>
		</div>
		<?php } ?>
	