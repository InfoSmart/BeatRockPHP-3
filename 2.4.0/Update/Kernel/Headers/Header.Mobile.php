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
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html lang="%site_language%" <?=Tpl::$mhtml?>>
<head <?=Tpl::$mhead?>>
	<title><?=$site['name']?></title>
	<meta name="title" content="<?=$site['name']?>" />
	
	<meta charset="%site_charset%" />
	<meta name="lang" content="%site_language%" />	
	
	<?php if(!empty($site['site_favicon'])) { ?>
	<link rel="icon" href="%RESOURCES%/images/%site_favicon%" />
	<link rel="shortcut icon" href="%RESOURCES%/images/%site_favicon%" type="image/vnd.microsoft.icon" />
	<link rel="apple-touch-icon" href="%RESOURCES%/images/%site_favicon%" />
	<?php } ?>
	
	<meta name="application-name" content="%SITE_NAME%" />
	<meta name="application-url" content="%PATH%" />
	
	<meta name="description" content="%site_description%" />	
	<meta name="build" content="%SITE_NAME% - Versión: %site_version% - Última revisión: %site_revision%" />
	
	<meta name="author" content="%site_author%" />
	<meta name="publisher" content="%site_publisher%" />
	<meta name="copyright" content="© <?=$date['Y']?> %site_publisher%" />
	
<?php
echo Tpl::$metas, "\r\n";
echo Tpl::$stuff;
?>
	
	<?php if($page['cache']) { ?>
	<meta http-equiv="expires" content="-1" />
	<meta http-equiv="cache-control" content="private max-age=0" />
	<?php } ?>
		
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
	echo "<!-- Estilos -->\r\n" . Tpl::$styles;

	if($site['site_bottom_javascript'] !== "true")
		echo "<!-- JavaScript -->\r\n" . Tpl::$js;
	?>	
</head>
<body>
	<div class="page" id="page">