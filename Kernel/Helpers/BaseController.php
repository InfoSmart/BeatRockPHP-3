<?
/**
 * BeatRock
 *
 * Framework para el desarrollo de aplicaciones web.
 *
 * @author 		Iván Bravo <webmaster@infosmart.mx> @Kolesias123
 * @copyright 	InfoSmart 2013. Todos los derechos reservados.
 * @license 	http://creativecommons.org/licenses/by-sa/2.5/mx/  Creative Commons "Atribución-Licenciamiento Recíproco"
 * @link 		http://beatrock.infosmart.mx/
 * @version 	3.0
 *
 * @package 	BaseController
 * Base para la creación de controladores.
 * TODO: Implementación incompleta.
 *
*/

# Acción ilegal
if( !defined('BEATROCK') )
	exit;

class BaseController extends BaseStatic
{
	/**
	 * Nombre de la vista.
	 * @var string
	 */
	static $id;
	/**
	 * Titulo de la página
	 * @var string
	 */
	static $name;
	/**
	 * Lenguaje de la vista.
	 * @var string
	 */
	static $lang;
	/**
	 * Secciones de traducción.
	 * @var array
	 */
	static $langSections;

	/**
	 * Llamada de un método inexistente.
	 * @param  string $name      	Nombre del método.
	 * @param  array $arguments 	Argumentos.
	 */
	static function __callStatic($name, $arguments)
	{
		# 404 NOT FOUND
		header('HTTP/1.1 404 Not Found');
		header('Status: 404 Not Found');
	}

	/**
	 * Prepara y procesa el controlador.
	 */
	static function Load()
	{
		global $page;

		if( empty(self::$id) )
			return;

		$page['id'] 			= self::$id;
		$page['name']			= self::$name;
		$page['lang']			= self::$lang;
		$page['lang_sections']	= self::$lang_sections;
	}
}
?>