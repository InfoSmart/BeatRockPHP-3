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

#####################################################
## META ETIQUETAS RECOMENDADAS	
#####################################################

if(empty($page['image']) AND defined(LOGO))
	$page['image'] = LOGO;

Tpl::addMeta('og:title', 		$site['name'], 'property');
Tpl::addMeta('og:site_name', 	SITE_NAME, 'property');
Tpl::addMeta('og:url', 			PATH_NOW, 'property');
Tpl::addMeta('og:description', 	$site['site_description'], 'property');
Tpl::addMeta('og:type', 		$site['site_type'], 'property');

if(!empty($page['image']))
{
	Tpl::addMeta('og:image', 	$page['image'], 'property');
	Tpl::addMeta('image', 		$page['image'], 'itemprop');
}

if(!empty($site['site_locale']))
	Tpl::addMeta('og:locale', $site['site_locale'], 'property');

if(!empty($site['site_og']))
{
	$og = json_decode($site['site_og'], true);

	foreach($og as $param => $value)
		Tpl::addMeta($param, html_entity_decode($value), 'property');
}

$hlang = Lang::PrepareLive();
$hlang = json_encode($hlang);

Tpl::addMeta('name', $site['name'], 'itemprop');
Tpl::addMeta('description', $site['site_description'], 'itemprop');
?>
<!DOCTYPE html>
<html lang="%site_language%" <?=Tpl::$mhtml?>>
<head <?=Tpl::$mhead?>>
	<meta charset="%site_charset%" />
	<title><?=$site['name']?></title>
	<meta name="title" content="<?=$site['name']?>" />
	
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="lang" content="%site_language%" />
	
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
	
	<meta name="build" content="%SITE_NAME% - Versión: %site_version% - Revisión: %site_revision%" />
	
	<meta name="author" content="%PATH%/humans.txt" />
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
	Site 			= "%PATH%",
	Path 			= "%PATH%",
	Path_NS 		= "%PATH_NS%",
	Path_SSL 		= "%PATH_SSL%",
	Path_Now 		= "%PATH_NOW%",
	Resources 		= "%RESOURCES%",
	Resources_Sys 	= "%RESOURCES_SYS%",
	Page 			= "<?=$page['id']?>",
	Page_Name 		= "<?=$site['name']?>",
	Site_Name 		= "%SITE_NAME%",
	My_Id 			= "<?=MY_ID?>",
	My_Lang 		= "<?=LANG?>";
	Lang 			= <?=$hlang?>;
<?=Tpl::$vars?>
	</script>
	
	<?php
	echo "<!-- Estilos -->\r\n" . Tpl::$styles;

	if($site['site_bottom_javascript'] !== "true")
		echo "	<!-- JavaScript -->\r\n" . Tpl::$js;

	if(!empty($site['site_header_javascript']))
		echo $site['site_header_javascript'];
	?>	
</head>
<body>
	<div class="page" id="page">