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

if(!empty($G['url']))
{
	Core::Photo();
	echo Gd::SnapshotWeb($G['url']);
	exit;
}

$page['id'] = "web_photo";
$page['folder'] = "demos";
$page['subheader'] = "none";
?>