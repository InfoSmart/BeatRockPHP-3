<?
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart © 2013 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

require 'Init.php';

# El mapa del sitio esta desactivado.
if( $site['site_sitemap'] !== 'true' )
	exit;

# Obtenemos la información de site_maps
Site::Get('maps');

# En text/xml
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<? while( $row = Assoc() ) { ?>
	<url>
		<loc><?=PATH?>/<?=_c($row['page'])?></loc>
		<? if( !empty($row['lastmod']) ) { ?><lastmod><?=_c($row['lastmod'])?></lastmod><? } ?>
		<? if( !empty($row['priority']) ) { ?><priority><?=$row['priority']?></priority><? } ?>
	</url>
	<? } ?>
</urlset>