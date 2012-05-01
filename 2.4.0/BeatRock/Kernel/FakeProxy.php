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

## --------------------------------------------------
##                   Proxy Falso
## --------------------------------------------------
## Puede usar este archivo para mandar solicitudes
## AJAX Crossdomain, la url debe estar codificada
## con BASE64 y en el parametro "toURL" POST/GET.
## --------------------------------------------------

// Permitir el uso de este Script por AJAX para cualquier dominio.
// Comente la siguiente línea si solo desea usarlo localmente.
Core::AllowCross("*");

// Dirección a visitar codificada en Base64.
$url = base64_decode($RC['toUrl']);

// ¿No hay dirección?	
if(empty($url))
	exit;

// Información a enviar.
$post = $_REQUEST;

// Visitando sitio y devolviendo respuesta.
Curl::Init($url);
echo Curl::Post($post);
?>