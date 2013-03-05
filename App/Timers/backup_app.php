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
## Cronometro: Backup de la aplicación.
###############################################################
## Crea una copia de seguridad de los archivos de la aplicación
## ubicada en /Kernel/Backups/
###############################################################

Bit::Backup();
?>