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
 * @package 	BaseStatic
 * Base estatica para la creación de ayudantes.
 *
*/

# Acción ilegal
if( !defined('BEATROCK') )
	exit;

class BaseStatic
{
	/**
	 * Información de la clase hijo.
	 * @var object
	 */
	static $class;
	/**
	 * La clase hijo.
	 * @var object
	 */
	static $child;
	/**
	 * Nombre de la clase.
	 * @var string
	 */
	static $className;

	/**
	 * Información extra para un error.
	 * @var array
	 */
	static $errorOther = array();

	/**
	 * Lanzar error.
	 * @param string $code    Código del error
	 * @param string $message Mensaje
	 */
	static function Error($code, $message = '')
	{
		# Le comunicamos a Lang que usaremos cadenas de traducción de esta clase.
		Lang::SetSection('helper.' . strtolower(self::$className));

		# Obtenemos información de donde proviene el error.
		$backtrace 	= self::backtrace();
		$data 		= array_merge(self::$error_other, array('function' => $backtrace['function'], 'line' => $backtrace['line']));

		# Lanzamos el error.
		Bit::Status($message, self::$class->getFileName(), $data);
		Bit::LaunchError($code);

		return false;
	}

	/**
	 * Constructor artificial
	 * Este método será llamado por BeatRock, útil para simular
	 * un constructor no estatico.
	 */
	static function _construct()
	{
		# Establecemos la clase hijo.
		self::$child 		= get_called_class();
		# Obtenemos toda la información de la clase hijo.
		self::$class 		= new ReflectionClass(self::$child);
		# Establecemos el nombre de la clase hijo.
		self::$className 	= self::$class->getName();
	}

	/**
	 * Obtiene información del método que ha llamado.
	 * TODO: No funciona correctamente en algunos escenarios.
	 * @return array Información.
	 */
	static function backtrace()
	{
		$traces = debug_backtrace();

		return array(
			'file' 		=> $traces[3]['file'],
			'line' 		=> $traces[3]['line'],
			'function' 	=> $traces[3]['function']
		);
	}
}
?>