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

require('../Init.php');

/*
	## Registra tu aplicación en:
	
	- Facebook: 
	http://www.facebook.com/developers/

	- Twitter: 
	https://dev.twitter.com/apps

	- Google: 
	https://code.google.com/apis/console/

	- Steam:
	¡No es necesario!
	
	## Al registrar tu aplicación obtendrás los códigos necesarios para la función de abajo (Prepare):
*/

Social::Prepare(array(
	'facebook'	=> array(
		'appId'		=> '216527505105341',
		'secret'	=> ''
	),
	
	'twitter'	=> array(
		'key'		=> 'bwqgYQEzV9hkdCqe36SAFg',
		'secret'	=> ''
	),
	
	'google'	=> array(
		'clientId'	=> '283193504697.apps.googleusercontent.com',
		'secret'	=> '',
		'key'		=> ''
	)
));

// Conectar y mostrar información.
if($G['do'] == 'connect')
{
	$info = Social::Init($G['service']);
}

// Registrar como usuario nuevo (No visible en el demo)
if($G['do'] == 'register')
{
	Social::LoginOrNew($G['service']);
	Core::Redirect();
	
	exit;
}

$page['id'] = 'social';
$page['folder'] = 'demos';
$page['subheader'] = 'none';
?>