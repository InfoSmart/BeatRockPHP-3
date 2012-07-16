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
define('RESOURCES_INS', '//resources.infosmart.mx');
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<title>Instalación de BeatRock</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	
	<meta charset="iso-8859-15" />

	<link rel="icon" href="http://resources.infosmart.mx/beatrock/images/favicon.ico" />
	<link rel="shortcut icon" href="http://resources.infosmart.mx/beatrock/images/favicon.ico" type="image/vnd.microsoft.icon" />

	<meta name="publisher" content="InfoSmart." />
	<meta name="copyright" content="© 2012 InfoSmart. Todos los derechos reservados. http://beatrock.infosmart.mx/">
	
	<meta name="robots" content="noodp, nofollow, noindex" />
	
	<script>
	Path = "<?php if(defined("PATH")) { echo PATH . '/Setup'; } else { echo '.'; } ?>",
	Resources_Sys = "<?=RESOURCES_INS?>/systemv2";
	</script>
	
	<link href="<?=RESOURCES_INS?>/systemv2/css/style.css" rel="stylesheet" />
	<link href="<?=RESOURCES_INS?>/systemv2/setup/style.install.css" rel="stylesheet" />
	
	<script src="<?=RESOURCES_INS?>/systemv2/js/jquery.js"></script>
	<script src="<?=RESOURCES_INS?>/systemv2/setup/functions.kernel.js"></script>
	<script src="<?=RESOURCES_INS?>/systemv2/setup/functions.install.js"></script>
</head>
<body>
	<div class="page" id="page">
		<header>
			<div class="wrapper">
				<figure>
					<img src="<?=RESOURCES_INS?>/systemv2/setup/images/Logo.png" />
				</figure>
			
				<h1><?=$page['name']?></h1>
			<div>
		</header>

	<div class="wrapper">