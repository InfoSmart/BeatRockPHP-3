<?
#####################################################
## 					 BeatRock
#####################################################
## Framework avanzado de procesamiento para PHP.
#####################################################
## InfoSmart © 2012 Todos los derechos reservados.
## http://www.infosmart.mx/
#####################################################
## http://beatrock.infosmart.mx/
#####################################################

# Acción ilegal.
if( !defined('BEATROCK') )
	exit;

###############################################################
## Cronometro: Backup de la base de datos.
###############################################################
## Crea una copia de seguridad de la base de datos ubicada en
## /Kernel/Backups/
###############################################################

MySQL::Backup();
?>