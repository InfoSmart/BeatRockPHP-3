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

// Al parecer no queremos mapa del sitio.
if($site['site_sitemap'] !== "true")
	exit;

// Obteniendo la información de mapa.
$maps['get'] = Site::Get("maps");

// Enviando cabecera de documento XML.
header("Content-type: text/xml");
echo '<?xml version="1.0" encoding="ISO-8859-15"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<?php while($row = mysql_fetch_assoc($maps['get'])) { ?>
	<url>
		<loc><?php echo PATH; ?>/<?php echo CleanText($row['page']); ?></loc>
		<?php if(!empty($row['lastmod'])) { ?><lastmod><?php echo CleanText($row['lastmod']); ?></lastmod><?php } ?>
		<?php if(!empty($row['priority'])) { ?><priority><?php echo $row['priority']; ?></priority><?php } ?>
	</url>
	<?php } ?>
</urlset>