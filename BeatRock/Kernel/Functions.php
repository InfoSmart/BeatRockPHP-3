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

// Acción ilegal.
if(!defined('BEATROCK'))
	exit;

## --------------------------------------------------
##        Funciones externas y globales
## --------------------------------------------------
## Utilice este archivo para definir funciones
## independientes del Kernel, también
## para definir procesos que se deban repetir
## en toda la aplicación.
## --------------------------------------------------

#####################################################
## ADMINISTRACIÓN	
#####################################################

// Si estamos o queremos visitar la administración.
if($page['admin'])
{
	/*
	¡No olvide descomentar esta línea!
	if(!LOG_IN OR $my['rank'] < 7)
		Core::Redirect();
	*/
	
	$page['folder'] 	= 'admin';
	$page['subheader'] 	= 'Admin.SubHeader';
	$page['compress'] 	= false;
}

#####################################################
## DEFINICIONES GLOBALES
#####################################################

#####################################################
## FUNCIONES GLOBALES
#####################################################
?>