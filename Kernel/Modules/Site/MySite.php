<?
if(!defined('BEATROCK'))
	exit;

class MySite
{
	static function HelloWorld()
	{
		return 'Hola mundo';
	}

	/*
		Si, tenemos un problema...
		Al ser esta una palabra traducida "manualmente" con la función "_l"
		no se traducira si usamos el sistema de traducción en "tiempo real".

		Trataremos de ver si hay una solución pacifica para la próxima versión
		de BeatRock ;)
	*/
	static function HelloWorldLang()
	{
		global $page;
		return _l('%helloworld%', 'global', $page['lang']);
	}
}
?>