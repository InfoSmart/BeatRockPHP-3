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
## Cronometro: Mantenimiento
###############################################################
## Ejecuta los procesos necesarios para dar mantenimiento a
## la aplicación.
###############################################################

# Vaciamos los directorios...
Io::EmptyDir(array(
	BIT . 'Logs', 		# Logs
	BIT . 'Backups', 	# Archivos de recuperación
	BIT . 'Temp', 		# Archivos temporales
	BIT . 'Cache'		# Caché
));
?>