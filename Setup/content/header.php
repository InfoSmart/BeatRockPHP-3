<?
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8" />

	<title>Instalación de BeatRock</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

	<link rel="icon" href="//resources.infosmart.mx/beatrock/images/favicon.ico" />
	<link rel="shortcut icon" href="//resources.infosmart.mx/beatrock/images/favicon.ico" type="image/vnd.microsoft.icon" />

	<meta name="publisher" content="InfoSmart." />
	<meta name="copyright" content="? 2012 InfoSmart. Todos los derechos reservados. http://beatrock.infosmart.mx/">

	<meta name="robots" content="noodp, nofollow, noindex" />

	<link href="<?=RESOURCES_INS?>/systemv2/css/style.css" rel="stylesheet" />
	<link href="<?=RESOURCES_INS?>/systemv2/setup/style.install.v3.css" rel="stylesheet" />

	<script>
	Path = "<? if(defined("PATH")) { echo PATH . '/Setup'; } else { echo '.'; } ?>",
	Resources_Sys = "<?=RESOURCES_INS?>/systemv2";
	</script>
	
	<script src="<?=RESOURCES_INS?>/systemv2/js/jquery.js"></script>
	<script src="<?=RESOURCES_INS?>/systemv2/setup/functions.kernel.js"></script>
	<script src="<?=RESOURCES_INS?>/systemv2/setup/functions.install.v3.js"></script>
</head>
<body>
	<div class="page" id="page">
		<header>
			<div class="wrapper">
				<hgroup>
					<h1>
						BeatRock
						<span>por InfoSmart</span>
					</h1>
					<h2>Solo... imagina una nueva web.</h2>
				</hgroup>

				<nav>
					<li data-page="index" <? if($page == 'index') { echo 'class="selected"'; } ?>>Introducción</li>
					<li data-page="step2" <? if($page == 'step2') { echo 'class="selected"'; } ?>>Configuración</li>
					<li data-page="step3" <? if($page == 'step3') { echo 'class="selected"'; } ?>>Servidor</li>
					<li data-page="step4" <? if($page == 'step4') { echo 'class="selected"'; } ?>>Aplicación</li>
					<li data-page="finish" <? if($page == 'finish') { echo 'class="selected"'; } ?>>Disfrute su nuevo poder</li>
				</nav>
			</div>
		</header>

		<div id="ajax-content">