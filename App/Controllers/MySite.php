<?
// Acción ilegal
if(!defined('BEATROCK'))
	exit;

## --------------------------------------------------
##        Controlador propio
## --------------------------------------------------
## En este archivo damos un ejemplo de los
## controladores propios.
## Solo cree el archivo de su controlador en esta
## carpeta y pongale a la class el mismo nombre.
## Una vez creado ya podrá usarlo sin hacer nada más.
## --------------------------------------------------

class MySite
{
	# MySite::HelloWorld()
	# Imprimir "Hola mundo"
	static function HelloWorld()
	{
		return 'Hola mundo';
	}

	/*
		Si, tenemos un problema...
		Al ser esta una palabra traducida "manualmente" con la función "_l"
		no se traducira si usamos el sistema de traducción en "tiempo real".

		Trataremos de ver si hay una solución pacifica para la próxima versión
		de BeatRock.
	*/

	# MySite::HelloWorldLang()
	# Imprimir "Hola mundo" en el idioma de la página.
	static function HelloWorldLang()
	{
		global $page;
		return _l('%helloworld%', 'global', $page['lang']);
	}
}
?>