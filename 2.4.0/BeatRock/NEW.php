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
## ¡Lo nuevo en BeatRock v2.4.0!
## -------------------------------------------

require('Init.php');

/*

## -----------------------------------------------
## Definición de parametro de traducción
## -----------------------------------------------

$page['lang_param'] = "||";

## -----------------------------------------------
## Definición de administradores de página en Facebook
## -----------------------------------------------

Social::Prepare(Array(
	'facebook' => Array(
		'appid' => '000111....',
		'secret' => '0000000...'
		'admins' => '0000001,000002',
	)
));

## -----------------------------------------------
## Agregar meta etiquetas Open Graph
## -----------------------------------------------

Social::addOG("imunchies", "create", "Prueba")

## -----------------------------------------------
## Agregar video/audio para reproducción en Facebook (Open Graph)
## -----------------------------------------------

Social::addVideo("http://example.com/movie.swf", "application/x-shockwave-flash", 400, 300);
Social::addAudio("http://example.com/sound.mp3", "audio/mpeg");

## -----------------------------------------------
## Publicar una acción.
## -----------------------------------------------

Social::PublishAction('imunchies', 'create', 'http://munchies.infosmart.mx/...');

## -----------------------------------------------
## Agregar atributos en las etiquetas <head> y <html>
## -----------------------------------------------

Tpl::addMoreHTML("itemscope");
Tpl::addMoreHead("prefix", "og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#");

## -----------------------------------------------
## Funciones MySQL recortadas
## -----------------------------------------------

Insert() -> query_insert()
Update() -> query_update()
Rows() -> query_rows()
Get() -> query_get() // Ya no es necesario el segundo parametro.
Assoc() -> MySQL::query_assoc()

num_rows() -> MySQL::num_rows()
fetch_assoc() -> MySQL::fetch_assoc()

## -----------------------------------------------
## Nuevas funciones para los servicios.
## -----------------------------------------------

Users::GetServices($hash)
Users::DeleteService($id)

## -----------------------------------------------
## Nueva forma "no estatica" de usar Media.
## -----------------------------------------------

$musica = new Media('audio', Array('controls', 'autoplay'));
$musica->Add('./MiMusica.mp3', 'audio/mpeg');
echo $musica->Show();

NOTA: La función Voice sigue siendo estatica:
echo Media::Voice('Hola');

## -----------------------------------------------
## Nuevo módulo "Html"
## -----------------------------------------------

$link = new Html('a');
$link->Set('src', 'http://beatrock.infosmart.mx/');
echo $link->Build();

--------------------------------------------------

$input = new Html('input', 'prueba');
$input->Set('name', 'prueba');
$input->Set('type', 'number');
$input->Set('placeholder', 'Escribe algo...')

Uso en plantilla:
%prueba%

## -----------------------------------------------
## Nuevo módulo "Mem" (Memcache)
## -----------------------------------------------

Mem::Set('prueba_cache', 'true');
echo Mem::Get('prueba_cache');
Mem::Delete('prueba_cache');

## -----------------------------------------------
## Función 'Core::CalculateTime' recortada.
## -----------------------------------------------

echo CalcTime('012021312323');

*/
?>