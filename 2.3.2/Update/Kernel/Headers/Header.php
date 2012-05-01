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
?>
<!DOCTYPE html>
<html lang="%site_language%">
<head>
	<title><?=$site['name']?></title>
	<meta name="title" content="<?=$site['name']?>" />
	
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	
	<meta http-equiv="content-type" content="text/html; charset=%site_charset%" />
	<meta http-equiv="content-language" content="%site_language%" />
	
	
	<?php if(!empty($site['site_favicon'])) { ?>
	<link rel="icon" href="%RESOURCES%/images/%site_favicon%" />
	<link rel="shortcut icon" href="%RESOURCES%/images/%site_favicon%" type="image/vnd.microsoft.icon" />
	<?php } ?>
	
	<meta name="application-name" content="%SITE_NAME%" />
	<meta name="application-url" content="%PATH%" />
	
	<meta name="msapplication-tooltip" content="%SITE_NAME%" />
	<meta name="msapplication-starturl" content="%PATH%" />
	
	<meta name="description" content="%site_description%" />
	<meta name="keywords" content="%site_keywords%" />
	
	<meta name="build" content="%SITE_NAME% - Versión: %site_version% - Última revisión: %site_revision%" />
	
	<meta name="author" content="%site_author%" />
	<meta name="publisher" content="%site_publisher%" />
	<meta name="copyright" content="© <?=$date['Y']?> %site_publisher%" />
	
	<?php
	echo Tpl::$metas;
	echo Tpl::$stuff;
	?>
	
	<?php if($page['cache']) { ?>
	<meta http-equiv="expires" content="-1" />
	<meta http-equiv="cache-control" content="private max-age=0" />
	<?php } ?>
	
	<?php if($page['og'] !== false) { ?>	
	<meta property="og:title" content="<?=$site['name']?>" />
	<meta property="og:site_name" content="%SITE_NAME%" />
	<meta property="og:url" content="%PATH%" />
	<meta property="og:description" content="%site_description%" />
	<?php if(!empty($site['site_logo'])) { ?><meta property="og:image" content="%LOGO%" /><?php } ?>
	<?php } ?>
	
	<meta name="robots" content="noodp" />	
	<link rel="canonical" href="%PATH%/" />
	
	<script>
	var Site = "%PATH%",
	Path = "%PATH%",
	Path_NS = "%PATH_NS%",
	Path_SSL = "%PATH_SSL%",
	Path_Now = "%PATH_NOW%",
	Resources = "%RESOURCES%",
	Resources_Sys = "%RESOURCES_SYS%",
	Page = "<?=$page['id']?>",
	Page_Name = "<?=$site['name']?>",
	Site_Name = "%SITE_NAME%",
	My_Id = "<?=MY_ID?>";
	
	<?=Tpl::$vars?>
	</script>
	
	<?php
	echo '<!-- Estilos -->' . Tpl::$styles;
	
	if($site['site_bottom_javascript'] !== "true")
		echo '<!-- JavaScript -->' . Tpl::$js;
	?>
	
</head>
<body>
	<div class="page" id="page">