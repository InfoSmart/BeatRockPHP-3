<?
/**
 * BeatRock
 *
 * Framework para el desarrollo de aplicaciones web.
 *
 * @author 		Iván Bravo <webmaster@infosmart.mx> @Kolesias123
 * @copyright 	InfoSmart 2013. Todos los derechos reservados.
 * @license 	http://creativecommons.org/licenses/by-sa/2.5/mx/  Creative Commons "Atribución-Licenciamiento Recíproco"
 * @link 		http://beatrock.infosmart.mx/
 *
*/

# Acción ilegal.
if ( !defined('BEATROCK') )
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
$Info['micro'] 		= '2';
$Info['revision'] 	= '001';

## ---------------------------------------------------------
## Fase del desarrollo.
## Alpha -> BETA -> PP -> RC -> Producción
## ---------------------------------------------------------

$Info['fase'] 		= 'BETA';
$Info['fase_ver'] 	= '5';

## ---------------------------------------------------------
## Fecha de creación.
## ---------------------------------------------------------

$Info['date'] 		= '05.03.2013';
$Info['date_hour'] 	= '11:00 AM';

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

# Sea bueno y no modifique o elimine esta línea.
header('X-Powered-By: BeatRock v'.$Info['version'].': http://beatrock.infosmart.mx/');
?>