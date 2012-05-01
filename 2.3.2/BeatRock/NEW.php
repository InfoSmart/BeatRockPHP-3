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

## -------------------------------------------
## ¡Lo nuevo en BeatRock v2.3.2!
## -------------------------------------------

require('Init.php');

/*

## -----------------------------------------------
## Función de DEBUG de Matrices.
## -----------------------------------------------

$data = Array("Prueba" => "LOL", "1" => "Woots");
_r($data);

## -----------------------------------------------
## Registro de usuario con foto de perfil.
## -----------------------------------------------

Users::NewUser("Kolesias123", "info", "Iván Bravo", "webmaster@infosmart.mx", time(), "http://resources.infosmart.mx/Logo.png", true);

## -----------------------------------------------
## Registro con foto del servicio "Gravatar".
## -----------------------------------------------

Users::NewUser("Kolesias123", "info", "Iván Bravo", "webmaster@infosmart.mx", time(), Array(
	"size" => "100",
	"default" => "mm"
), true);

## -----------------------------------------------
## Nuevas funciones de filtración.
## -----------------------------------------------

echo _f("OR 1 = '1' ;"); // FilterText()
echo _c("<div class='t'>Testing</div>"); // CleanText()

## -----------------------------------------------
## Filtro de matrices.
## -----------------------------------------------

$data = Array(
	"Prueba",
	"Valor :B",
	"' OR = 1"
);

$data = _f($data);
$datac = _c($data);

## -----------------------------------------------
## Direcciones web a links.
## -----------------------------------------------

$url = Core::ToURL("¡Hola! Visita www.infosmart.mx ;D");
// Resultado: ¡Hola! Visita <a href="http://www.infosmart.mx/" target="_blank">www.infosmart.mx</a> ;D

## -----------------------------------------------
## Uso de varias plantillas.
## -----------------------------------------------

$page['id'] = Array(
	"index", "index_footer", "index_final"
);

## -----------------------------------------------
## API para Facebook, Twitter y Google.
## -----------------------------------------------

Social::Prepare(Array(
	"facebook" => Array(
		"appId" => "216527505105341",
		"secret" => "24a1c74d..."
	)
)); -> Ajustar datos de conexión.

$info = Social::Init("facebook"); -> Obtener información.

Social::LoginOrNew("facebook") -> Iniciar sesión o registrarse con el servicio.
Social::Login("twitter") -> Iniciar sesión con el servicio.
Social::NewUser("facebook"); -> Registrarse con el servicio.
Social::PrepareFacebook() -> Código JavaScript de Facebook.

Visita "social.php" para más información.

*/
?>