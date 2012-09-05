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

// Acción ilegal.
if(!defined('BEATROCK'))
	exit;

## --------------------------------------------------
##        Funciones de cabecera
## --------------------------------------------------
## Utilice este archivo para definir la 
## implementación de recursos CSS/JS/Meta en
## su aplicación, utilice la variable $page[id]
## para separar recursos de páginas únicas.
## --------------------------------------------------
	
#####################################################
## IMPLEMENTACIÓN DE RECURSOS RECOMENDADOS.
#####################################################

// Agregando jQuery.
Tpl::addjQuery();

// Agregando el estilo predeterminado y el Kernel en JavaScript.
Tpl::myStyle('style', true);
Tpl::myScript('functions.kernel', true);

// Si queremos RSS...
if($site['site_rss'] == 'true')
	Tpl::addStuff('<link rel="alternate" type="application/rss+xml" title="%site_name%: RSS" href="'.ShortCuts($site['site_rss_path']).'" />');

#####################################################
## AGREGANDO ESTILOS SEGÚN PÁGINA
#####################################################

Tpl::myStyle('style.page');
//Tpl::myStyle('style.forms');
	
Tpl::myScript('functions.page');

#####################################################
## INICIALIZACIÓN
#####################################################

if(empty($page['image']) AND defined('LOGO'))
	$page['image'] = LOGO;

if(!empty($page['image']))
{
	Tpl::addMeta('og:image', 	$page['image'], 'property');
	Tpl::addMeta('image', 		$page['image'], 'itemprop');
}

$hlang = Lang::PrepareLive();
$hlang = json_encode($hlang);

Tpl::addMeta('name', $site['name'], 'itemprop');
Tpl::addMeta('description', $site['site_description'], 'itemprop');

#####################################################
## OPEN GRAPH
#####################################################

Tpl::addMeta('og:title', 		$site['name'], 'property');
Tpl::addMeta('og:site_name', 	SITE_NAME, 'property');
Tpl::addMeta('og:url', 			PATH_NOW, 'property');
Tpl::addMeta('og:description', 	$site['site_description'], 'property');
Tpl::addMeta('og:type', 		$site['site_type'], 'property');

if(!empty($site['site_locale']))
	Tpl::addMeta('og:locale', $site['site_locale'], 'property');

if(!empty($site['site_og']))
{
	$og = json_decode(utf8_encode($site['site_og']), true);

	foreach($og as $param => $value)
		Tpl::addMeta($param, Core::FixText(html_entity_decode($value)), 'property');
}

#####################################################
## OTROS
#####################################################

Tpl::addMoreHTML('itemscope');
Tpl::addMoreHead('prefix', 'og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#');
?>