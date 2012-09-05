<!DOCTYPE html>
<html lang="%site_language%" <?=Tpl::$mhtml?>>
<head <?=Tpl::$mhead?>>
	<meta charset="%site_charset%" />
	
	<title><?=$site['name']?></title>
	<meta name="title" content="<?=$site['name']?>" />
	
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="lang" content="%site_language%" />
	
	<? if(!empty($site['site_favicon'])) { ?>
	<link rel="icon" href="%RESOURCES%/images/%site_favicon%" />
	<link rel="shortcut icon" href="%RESOURCES%/images/%site_favicon%" type="image/vnd.microsoft.icon" />
	<? } ?>
	
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
	
<?
echo Tpl::$metas, "\r\n";
echo Tpl::$stuff;
?>
	
	<? if($page['cache']) { ?>
	<meta http-equiv="expires" content="-1" />
	<meta http-equiv="cache-control" content="private max-age=0" />
	<? } ?>
	
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
	
	<?
	echo "<!-- CSS -->\r\n" . Tpl::$styles;

	if($site['site_bottom_javascript'] !== 'true')
		echo "	<!-- JavaScript -->\r\n" . Tpl::$js;

	if(!empty($site['site_header_javascript']))
		echo $site['site_header_javascript'];
	?>	
</head>
<body>