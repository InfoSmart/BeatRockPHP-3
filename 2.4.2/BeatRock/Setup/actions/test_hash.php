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

define("NO_FOOTER", true);
require('../Init.php');

if(is_numeric($P['level']) AND !empty($P['hash']))
	echo Encrypte("BeatRock-InfoSmart-@", $P['level'], $P['hash']);
?>