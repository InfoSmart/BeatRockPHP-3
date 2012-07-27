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

require 'Init.php';

// Al parecer no queremos mapa del sitio.
if($site['site_sitemap'] !== 'true')
	exit;

// Obteniendo la información de mapa.
$maps['get'] = Site::Get('maps');

// Enviando cabecera de documento XML.
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="ISO-8859-15"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<? while($row = fetch_assoc($maps['get'])) { ?>
	<url>
		<loc><?=PATH?>/<?=_c($row['page'])?></loc>
		<? if(!empty($row['lastmod'])) { ?><lastmod><?=_c($row['lastmod'])?></lastmod><? } ?>
		<? if(!empty($row['priority'])) { ?><priority><?=$row['priority']?></priority><? } ?>
	</url>
	<? } ?>
</urlset>