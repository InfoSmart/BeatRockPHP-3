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

require '../Init.php';

if(!empty($P['username']) AND !empty($P['service']))
{
	$data = Social::GetInfo($P['service'], $P['username']);
}

$page['id']			= 'social_public';
$page['folder'] 	= 'demos';
$page['subheader'] 	= 'none';
?>