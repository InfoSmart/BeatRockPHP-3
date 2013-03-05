<?
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart © 2013 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

require '../Init.php';

## --------------------------------------------------
##                   Proxy Falso
## --------------------------------------------------
## Puede usar este archivo para mandar solicitudes
## AJAX Crossdomain. FakeProxy?url=http://www.infosmart.mx/
## --------------------------------------------------

# Permitir el uso de este Script por AJAX para cualquier dominio.
# Comente la siguiente linea si solo desea usarlo localmente.
Tpl::AllowCross('*');

# ¿No hay dirección?	
if( empty($R['url']) )
	exit;

# Información a enviar.
$post = $_REQUEST;

# Visitando sitio y devolviendo respuesta.
$request = new Curl($url);
echo $request->Post($post);
?>