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
 * @package 	Codes
 * Controla la información de los códigos de error.
 *
*/

# Acción ilegal.
if ( !defined('BEATROCK') )
	exit;

class Codes
{
	/**
	 * Lista de códigos
	 * @var array
	 */
	static $codes = array();

	/**
	 * Constructor
	 */
	function __construct()
	{
		# Ruta de la carpeta de lenguaje a usar.
		$folder = LANGUAGES . LANG;

		# Este lenguaje no existe, usar español.
		if ( !is_dir($folder) )
			$folder = LANGUAGES . 'es';

		# ¿Español no existe?, Vale, usemos Inglés.
		if ( !is_dir($folder) )
			$folder = LANGUAGES . 'en';

		# Definitivamente solo quieres tu lenguaje pero si no existe ¿que hacer?
		if ( !is_dir($folder) )
		{
			# Evitamos la caché y reportamos código 503
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Cache-Control: no-cache');
			header('HTTP/1.1 503 Service Temporarily Unavailable');
			header('Status: 503 Service Temporarily Unavailable');

			# Mostrar el error de forma grotesca, si, tipo IE 6.
			echo '<strong style="color: red">';
			echo 'ERROR', '<br />';
			echo 'No se ha podido encontrar la carpeta de lenguajes en: ' . LANGUAGES;
			echo '</strong>';

			exit;
		}

		# Cargamos las traducciones.
		self::$codes = Core::LoadJSON($folder . DS . 'Codes.json');
	}

	/**
	 * Obtiene la información de un código de error.
	 * @param string $code Código.
	 */
	static function GetInfo($code)
	{
		$result = _c( self::$codes[$code] );

		# ¿Un código inválido? Usar "desconocido"
		if ( empty($result['title']) )
			$result = _c( self::$codes['unknow'] );

		$result['code'] = $code;
		return $result;
	}
}
?>