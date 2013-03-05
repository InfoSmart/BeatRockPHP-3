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
 * @version 	3.0
 *
 * Código de preparación
 * Utilice este archivo para definir el código que será
 * ejecutado al inicio del Framework.
 *
*/

# Acción ilegal.
if( !defined('BEATROCK') )
	exit;

#############################################################
## MANTENIMIENTO
## Verificamos si el sitio esta en mantenimiento, si es así
## devolvemos la vista de mantenimiento y un error 503.
#############################################################

# Puedes evitar que una página muestre la vista de mantenimiento
# poniendo "$page['maintenance'] = false;" antes de "require Init.php"

if( $site['site_status'] !== 'open' AND $page['maintenance'] !== false )
{
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Cache-Control: no-cache');

	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');

	$maintenance = new View(KERNEL_VIEWS_BIT . 'Maintenance');
	exit($maintenance);
}

#############################################################
## DEFINICIONES GLOBALES
## Puede definir variables o ejecutar funciones de inicialización
## que podrían ser necesarios en toda la aplicación.
#############################################################

/*
	Aquí por ejemplo, preparamos la API de Facebook, Twitter, Google y VirusTotal

Social::Prepare(array(
	'facebook' 	=> array(
		'appId'		=> '216527505105341',
		'secret'	=> '24a1c74d40b401...'
	),

	'twitter' 	=> array(
		'key'		=> 'bwqgYQEzV9hkdCqe36SAFg',
		'secret'	=> 'jxkeSLaNRKy5....'
	),

	'google'	=> array(
		'clientId'	=> '408633464638.apps.googleusercontent.com',
		'secret'	=> 'PCeeony5bl...',
		'key'		=> 'AIzaSyAWppTs...'
	),

	'vt'		=> array(
		'apiKey'	=> '3471758cec85822c030b230...'
	)
));
*/

#############################################################
## CONFIGURACIÓN EXTRA
## Puede configurar aspectos globales del Kernel.
#############################################################

/*
	BBCode -> Core::BBCode(string, smilies)
	Desde aquí puedes editar los códigos BBCode.
*/

$kernel['bbcode_search'] = array(
	'/\[b\](.*?)\[\/b\]/is', 					// [b]Negrita[/b]
	'/\[i\](.*?)\[\/i\]/is', 					// [i]Italica[/i]
	'/\[u\](.*?)\[\/u\]/is', 					// [u]Tachado[/u]
	'/\[s\](.*?)\[\/s\]/is', 					// [s]Subrayado[/s]
	'/\[url\=(.*?)\](.*?)\[\/url\]/is', 		// [url=http://]Enlace[/url]
	'/\[color\=(.*?)\](.*?)\[\/color\]/is', 	// [color=red]Texto rojo[/color]
	'/\[size=small\](.*?)\[\/size\]/is', 		// [size=small]Texto de 9px[/size]
	'/\[size=large\](.*?)\[\/size\]/is',		// [size=large]Texto de 14px[/size]
	'/\[size\=(.*?)\](.*?)\[\/size\]/is', 		// [size=30]Texto de 30px[/size]
	'/\[code\](.*?)\[\/code\]/is', 				// [code]Código[/code]

	'/\[youtube\=(.*?)x(.*?)\](.*?)\[\/youtube\]/is', 	// [youtube=300x300]zxl8bwiRTp8[/youtube] -> https://www.youtube.com/embed/zxl8bwiRTp8
	'/\[vimeo\=(.*?)x(.*?)\](.*?)\[\/vimeo\]/is' 		// [vimeo=300x300]58566749[/vimeo] -> http://player.vimeo.com/video/58566749
);

$kernel['bbcode_replace'] = array(
	'<strong>$1</strong>',
	'<i>$1</i>',
	'<u>$1</u>',
	'<s>$1</s>',
	'<a href="$1">$2</a>',
	'<label style="color: $1;">$2</label>',
	'<label style="font-size: 9px;">$1</label>',
	'<label style="font-size: 14px;">$1</label>',
	'<label style="font-size: $1px;">$2</label>',
	'<pre>$1</pre>',

	'<iframe title="YouTube" width="$1" height="$2" src="https://www.youtube.com/embed/$3" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>',
	'<iframe title="Vimeo" width="$1" height="$2" src="http://player.vimeo.com/video/$3?badge=0" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>'
);

/*
	Smilies -> Core::Smilies(string, bbcode)
	Desde aquí puedes editar las imagenes usadas para los emoticones.
	Las imagenes se encuentran en: /App/BitRock/Emoticons/ (Obligatorio ser PNG)
*/

$kernel['emoticons'] = array(
	':D' 	=> 'awesomes',
	':)' 	=> 'happy',
	'D:' 	=> 'ohnoes',
	':0' 	=> 'ohnoes',
	':O' 	=> 'ohnoes',
	'OMG' 	=> 'ohnoes',
	':3' 	=> 'meow',
	'.___.' => 'huh',
	'._.' 	=> 'huh',
	':S' 	=> 'confused',
	':P' 	=> 'lick',
	'^^' 	=> 'laugh',
	':(' 	=> 'sad',
	';)' 	=> 'wink',
	':B' 	=> 'toofis',
	'jelly' => 'jelly',
	'jalea' => 'jelly'
);


#####################################################
## FUNCIONES GLOBALES
## Puedes crear funciones útiles desde aquí.
#####################################################
?>