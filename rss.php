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

# El RSS esta desactivado.
if( $site['site_rss'] !== 'true' )
{
	header('HTTP/1.1 404 Not Found');
	header('Status: 404 Not Found');

	exit;
}

# Obtenemos las primeras 10 noticias.
Site::Get('news', 10);

# RSS en text/xml
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0">
	<channel>
		<title><?=SITE_NAME?></title>
		<description><?=html_entity_decode($site['site_description'])?></description>
		<link><?=PATH?></link>
	
		<?
		while( $row = Assoc() )
		{
			if( !is_numeric($row['date']) )
				$row['date'] = strtotime($row['date']);
		?>
		<item>
			<title><?=html_entity_decode($row['title'])?></title>
			<description><?=($row['sub_content'])?></description>
			<pubDate><?=date('r', $row['date'])?></pubDate>
			<!-- 
			Descomentar en caso de tener una página de visualización de las noticias
			<link><?=PATH?>/news?id=<?=$row['id']?></link>
			-->
		</item>
		<? } ?>
	</channel>
</rss>