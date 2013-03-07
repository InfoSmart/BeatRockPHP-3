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
##                   Emoticones
## --------------------------------------------------
## Este archivo será solicitado para los emoticones
## en caso de que use la función Core::Smilies
## --------------------------------------------------

# Ruta de la imagen.
$path = BIT . 'Emoticons' . DS . $G['e'] . '.png';

# Verificamos que la imagen exista.
if( empty($G['e']) OR !file_exists($path) )
	exit;

# Mostrar resultado como una imagen de tipo PNG.
Tpl::Image();

# Permitir el uso de este Script por AJAX para cualquier dominio.
# Comente la siguiente línea si solo desea usarlo localmente.
Tpl::AllowCross('*');

# Mostrar emoticon.
echo file_get_contents($path);
?>