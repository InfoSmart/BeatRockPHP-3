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

// Acción ilegal.
if(!defined('BEATROCK'))
	exit;

## --------------------------------------------------
##        Procesos de preparación
## --------------------------------------------------
## Utilice este archivo para definir procesos,
## condiciones, variables y demás que serán iniciadas
## al inicio del Framework.
## --------------------------------------------------

#############################################################
## ¡EN MANTENIMIENTO!
#############################################################

if($site['site_status'] !== 'open' AND $page['maintenance'] !== false)
{
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Cache-Control: no-cache');

	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');
	
	exit(Tpl::Process(KERNEL_TPL_BIT . 'Maintenance'));
}

#############################################################
## DEFINICIONES GLOBALES
#############################################################

/*
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
#############################################################

/*
	BBCode -> Core::BBCode(string, smilies)
	Desde aquí puedes editar los códigos BB.
*/

$kernel['bbcode_search'] = array(
	'/\[b\](.*?)\[\/b\]/is', 
	'/\[i\](.*?)\[\/i\]/is', 
	'/\[u\](.*?)\[\/u\]/is', 
	'/\[s\](.*?)\[\/s\]/is', 
	'/\[url\=(.*?)\](.*?)\[\/url\]/is', 
	'/\[color\=(.*?)\](.*?)\[\/color\]/is', 
	'/\[size=small\](.*?)\[\/size\]/is', 
	'/\[size=large\](.*?)\[\/size\]/is', 
	'/\[size\=(.*?)\](.*?)\[\/size\]/is', 
	'/\[code\](.*?)\[\/code\]/is',
		
	'/\[youtube\=(.*?)x(.*?)\](.*?)\[\/youtube\]/is',
	'/\[vimeo\=(.*?)x(.*?)\](.*?)\[\/vimeo\]/is'
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
	Las imagenes se encuentran en: /Kernel/BitRock/Emoticons/ (Obligatorio ser PNG)
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
#####################################################
?>