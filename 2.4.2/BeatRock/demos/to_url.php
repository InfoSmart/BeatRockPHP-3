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

if(!empty($P['str']))
	$str = Core::ToURL($P['str']);

$page['id'] = "to_url";
$page['folder'] = "demos";
$page['subheader'] = "none";
?>