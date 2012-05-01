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
## IMPLEMENTACIÓN DE RECURSOS RECOMENDADO.
#####################################################

// Agregando jQuery.
Tpl::addjQuery();

Tpl::myStyle('style', true);
Tpl::myScript('functions.kernel', true);

// Si queremos RSS...
if($site['site_rss'] == "true")
	Tpl::addStuff('<link rel="alternate" type="application/rss+xml" title="%site_name%: RSS" href="%PATH%/rss" />');

#####################################################
## AGREGANDO ESTILOS SEGÚN PÁGINA
#####################################################

if($page['admin'])
{
	Tpl::myStyle('style.admin', true);
	Tpl::myScript('functions.admin', true);	
}
else
{
	Tpl::myStyle('style.page');
	//Tpl::myStyle('style.forms');
	
	//Tpl::myScript('functions.page');
}

#####################################################
## OTROS
#####################################################

Tpl::addMoreHTML("itemscope");
Tpl::addMoreHead("prefix", "og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#");
?>