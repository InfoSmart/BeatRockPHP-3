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

if(!empty($_FILES['image']))
{
	$image = $_FILES['image'];

	Tpl::Image();
	echo Gd::Filter($image['tmp_name'], $P['filter']);

	exit;
}


$page['id']			= 'image_filter';
$page['folder'] 	= 'demos';
$page['subheader'] 	= 'none';
?>