<?
#####################################################
## 					 BeatRock
#####################################################
## Framework avanzado de procesamiento para PHP.
#####################################################
## InfoSmart © 2012 Todos los derechos reservados.
## http://www.infosmart.mx/	
#####################################################
## http://beatrock.infosmart.mx/
#####################################################

// Acción ilegal.
if(!defined('BEATROCK'))
	exit;

## ------------------------------------------------------------
##           Información de versión de BeatRock.
## ------------------------------------------------------------
## Información acerca de la versión del Kernel y detalles
## acerca de su creaci?n.
## ------------------------------------------------------------

## ---------------------------------------------------------
## Nombre del Kernel.
## Si ha hecho modificaciones, cree su propio "Code Name".
## ---------------------------------------------------------

$Info['name'] = 'BeatRock';
$Info['code'] = 'Mentalist';

## ---------------------------------------------------------
## Versi?n del Kernel.
## ---------------------------------------------------------

$Info['mayor'] 		= '3';
$Info['minor'] 		= '0';
$Info['micro'] 		= '1';
$Info['revision'] 	= '030';

## ---------------------------------------------------------
## Fase del desarrollo.
## Alpha -> BETA -> PP -> RC -> Producción
## ---------------------------------------------------------

$Info['fase'] 		= 'BETA';
$Info['fase_ver'] 	= '3';

## ---------------------------------------------------------
## Fecha de creación.
## ---------------------------------------------------------

$Info['date'] 		= '28.12.2012';
$Info['date_hour'] 	= '03:40 PM';

## ---------------------------------------------------------
## Nombres.
## ---------------------------------------------------------

$Info['version'] 			= "$Info[mayor].$Info[minor].$Info[micro]";
$Info['version.name'] 		= "$Info[name] v$Info[version]";
$Info['version.code'] 		= "$Info[name] \"$Info[code]\" v$Info[version]";
$Info['version.revision'] 	= "$Info[version] Revisión:  $Info[revision]";
$Info['version.date'] 		= "$Info[version] - $Info[date] $Info[date_hour]";
$Info['version.fase'] 		= "$Info[fase] $Info[fase_ver]";
$Info['version.full'] 		= $Info['version.code'] . " $Info[fase]$Info[fase_ver] Revisi?n: $Info[revision] - $Info[date] $Info[date_hour]";

// Sea bueno y no modifique o elimine esta línea.
header('X-Powered-By: BeatRock v'.$Info['version'].': http://beatrock.infosmart.mx/');
?>