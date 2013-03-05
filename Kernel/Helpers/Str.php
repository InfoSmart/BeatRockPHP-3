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
 * @package 	Str
 * Permite la creación de cadenas inteligentes.
 *
*/

# Acción ilegal
if ( !defined('BEATROCK') )
	exit;

class Str
{
	/**
	 * Cadena
	 * @var string
	 */
	public $str 		= '';
	/**
	 * Cadena antes del último cambio.
	 * @var string
	 */
	public $undo 		= '';
	/**
	 * Cadena original.
	 * @var string
	 */
	public $original 	= '';

	/**
	 * Longitud de la cadena.
	 * @var [type]
	 */
	public $length;
	/**
	 * MD5 de la cadena.
	 * @var [type]
	 */
	public $md5;
	/**
	 * SHA1 de la cadena.
	 * @var [type]
	 */
	public $sha1;

	/**
	 * Permite establecer si la cadena será el primer
	 * argumento en la ejecución de un método de PHP.
	 * @var boolean
	 */
	private $first 		= false;

	/**
	 * Constructor
	 * @param string $str Cadena
	 * @return Str
	 */
	function __construct($str)
	{
		# Ajustamos la cadena.
		$this->str 		= $str;
		$this->original = $str;

		# Actualizamos su longitud, md5, sha1, etc...
		$this->update();

		return $this;
	}

	/**
	 * Pasa la cadena por algún tipo de codificación.
	 * @param  string $type Tipo. (utf8, base64, htmlentities)
	 * @return Str
	 */
	function encode($type)
	{
		$this->undo = $this->str;

		switch ( $type )
		{
			case 'utf8':
				$this->str = utf8_encode($this->str);
			break;

			case 'base64':
				$this->str = base64_encode($this->str);
			break;

			case 'htmlentities':
				$this->str = htmlentities($this->str, ENT_QUOTES | ENT_SUBSTITUTE);
			break;
		}

		$this->update();
		return $this;
	}

	/**
	 * Pasa la cadena por algún tipo de descodifición.
	 * @param  string $type Tipo. (utf8, base64, htmlentities)
	 * @return Str
	 */
	function decode($type)
	{
		$this->undo = $this->str;

		switch ( $type )
		{
			case 'utf8':
				$this->str = utf8_decode($this->str);
			break;

			case 'base64':
				$this->str = base64_decode($this->str);
			break;

			case 'htmlentities':
				$this->str = html_entity_decode($this->str);
			break;
		}

		$this->update();
		return $this;
	}

	/**
	 * Verifica que la cadena sea válida.
	 * @param integer $type   	Tipo de cadena (EMAIL, USERNAME, IP, CREDIT_CARD, URL, PASSWORD, SUBDOMAIN, DOMAIN)
	 * @return boolean 			Devuelve true en caso de que el valor tenga una estructura válida en caso contrario devolverá false.
	 */
	function valid($type = EMAIL)
	{
		return Core::Valid($this->str, $type);
	}

	/**
	 * Obtiene la longitud de la cadena.
	 * @return integer Longitud.
	 */
	function length()
	{
		return strlen($this->str);
	}

	/**
	 * Actualiza la información de la cadena.
	 * @return Str
	 */
	function update()
	{
		$this->length 	= $this->length();
		$this->md5 		= md5($this->str);
		$this->sha1 	= sha1($this->str);

		return $this;
	}

	/**
	 * Devuelve la cadena a su valor anterior.
	 * @return Str
	 */
	function undo()
	{
		$this->str = $this->undo;
		return $this;
	}

	/**
	 * Decide que metodo (PHP) aplicar sobre la cadena.
	 * @param  [type] $name      Nombre del metodo.
	 * @param  [type] $arguments Argumentos.
	 * @return string Valor o clase Str.
	 */
	function __call($name, $arguments)
	{
		$this->undo = $this->str;
		$result 	= false;

		# No hay argumentos, la cadena siempre es requerida.
		if ( empty($arguments) )
			$arguments = array($this->str);
		else
		{
			# Un intento fallido ha ocurrido, quizá la función
			# a llamar precisa que la cadena se encuentre en el primer argumento.
			if ( $this->first )
				array_unshift($arguments, $this->str);

			# Poner la cadena en el último argumento, siempre funciona.
			else
				$arguments[] = $this->str;
		}

		# La función enrealidad se llama str_<funcion> (str_replace, str_split, etc...)
		if ( function_exists('str_' . $name) )
			$result = call_user_func_array('str_' . $name, $arguments);

		# La función enrealidad se llama str<funcion> (strpos, strlen, etc...)
		else if ( function_exists('str' . $name) )
			$result = call_user_func_array('str' . $name, $arguments);

		# La función enrealidad se llama strto<funcion> (strtolower, strtoupper, etc...)
		else if ( function_exists('strto' . $name) )
			$result = call_user_func_array('strto' . $name, $arguments);

		# La función se llama así mismo, veamos si esto funciona...
		else if ( function_exists($name) )
			$result = call_user_func_array($name, $arguments);

		# El resultado es un array, devolverlo.
		if ( is_array($result) )
		{
			$this->first 	= false;
			return $result;
		}

		# El resultado ha fallado (¿un error con la función?)
		else if ( !is_string($result) )
		{
			# Ni siquiera poner la cadena como primer argumento ha funcionado...
			# evitamos un blucle infinito.
			if ( $this->first )
			{
				$this->first = false;
				return $this;
			}

			# Quitamos la cadena del último argumento.
			array_pop($arguments);

			# Intentamos ahora poniendo la cadena en primer argumento.
			$this->first 	= true;
			$result 		= $this->__call($name, $arguments);

			# Actualizar y devolver.
			$this->update();
			return $result;
		}

		# Actualizar y devolver.
		$this->first 	= false;
		$this->str 		= $result;
		$this->update();

		return $this;
	}

	/**
	 * Devuelve la cadena.
	 * @return string Cadena
	 */
	function __toString()
	{
		return ( is_string($this->str) ) ? $this->str : $this->original;
	}
}
?>