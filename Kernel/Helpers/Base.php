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
 * @package 	Base
 * Base no estatica para la creación de ayudantes.
 *
*/

# Acción ilegal
if( !defined('BEATROCK') )
	exit;

class Base
{
	/**
	 * Información de la clase hijo.
	 * @var object
	 */
	public $class;
	/**
	 * La clase hijo.
	 * @var object
	 */
	public $child;
	/**
	 * Nombre de la clase.
	 * @var string
	 */
	public $className;

	/**
	 * Información extra para un error.
	 * @var array
	 */
	public $errorOther = array();

	/**
	 * Lanzar error.
	 * @param string $code    Código del error
	 * @param string $message Mensaje
	 */
	function Error($code, $message = '')
	{
		# Le comunicamos a Lang que usaremos cadenas de traducción de esta clase.
		Lang::SetSection('helper.' . strtolower($this->className));

		# Obtenemos información de donde proviene el error.
		$backtrace 	= $this->backtrace();
		$data 		= array_merge($this->error_other, array('function' => $backtrace['function'], 'line' => $backtrace['line']));

		# Lanzamos el error.
		Bit::Status($message, $this->class->getFileName(), $data);
		Bit::LaunchError($code);

		return false;
	}

	/**
	 * Constructor
	 * @param class $child Clase hijo.
	 */
	function __construct($child)
	{
		# Establecemos la clase hijo.
		$this->child 		= $child;
		# Obtenemos toda la información de la clase hijo.
		$this->class 		= new ReflectionClass($child);
		# Establecemos el nombre de la clase hijo.
		$this->className 	= $this->class->getName();
	}

	/**
	 * Obtiene información del método que ha llamado.
	 * TODO: No funciona correctamente en algunos escenarios.
	 * @return array Información.
	 */
	function backtrace()
	{
		$traces = debug_backtrace();

		return array(
			'file' 		=> $traces[1]['file'],
			'line' 		=> $traces[1]['line'],
			'function' 	=> $traces[2]['function']
		);
	}
}
?>