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
	 * Verifica si una cadena contiene malas palabras.
	 * @return boolean 		Devuelve true en caso de encontrar una mala palabra en caso contrario devolverá false.
	 */
	function blocked()
	{
		return Core::StrBlocked($this->str);
	}

	/**
	 * Pasa la cadena por un filtro de malas palabras.
	 * @param string $replace 	Reemplazo para las malas palabras.
	 * @return string 			Devuelve la cadena filtrada.
	 */
	function filterBlocked($replace = '****')
	{
		$this->undo = $this->str;
		$this->str 	= Core::FilterBlocked($this->str, $replace);

		$this->update();
		return $this;
	}

	/**
	 * Aplicar filtro anti SQL Inyection a una cadena.
	 */
	function escape()
	{
		$this->undo = $this->str;
		$this->str 	= SQL::Escape($this->str);

		$this->update();
		return $this;
	}

	/**
	 * Filtra una cadena contra:
	 * SQL Inyection, XSS Inyection y Caracteres inválidos.
	 * @param boolean $html ¿Aplicar filtro XSS?
	 * @param string  $from Codificación original.
	 * @param string  $to   Codificación deseada.
	 */
	function filter($html = true, $from = '', $to = '')
	{
		$this->undo = $this->str;
		$this->str 	= Core::Filter($this->str, $html, $from, $to);

		$this->update();
		return $this;
	}

	/**
	 * Filtra una cadena contra:
	 * XSS Inyection y Caracteres inválidos.
	 * @param string  $from Codificación original.
	 * @param string  $to   Codificación deseada.
	 */
	function clean($from = '', $to = '')
	{
		$this->undo = $this->str;
		$this->str 	= Core::Clean($this->str, $from, $to);

		$this->update();
		return $this;
	}

	/**
	 * Repara la codificación de una cadena.
	 * @param string  $from Codificación original.
	 * @param string  $to   Codificación deseada.
	 * @param boolean $html ¿Aplicar filtro XSS? (Más efectivo en esta función)
	 * @return string Cadena con la codificación arreglada.
	 */
	function fix($from = 'UTF-8', $to = 'ISO-8859-15', $html = false)
	{
		$this->undo = $this->str;
		$this->str 	= Core::FixText($this->str, $from, $to);

		$this->update();
		return $this;
	}

	/**
	 * Formatea una cadena para su uso en direcciones web.
	 * @param boolean $lower  ¿Convertir a minusculas?
	 * @param boolean $spaces ¿Convertir espacios en - en vez de eliminarlos?
	 * @return string La cadena para ser usada en una dirección web.
	 */
	function formatToUrl($lower = true, $spaces = true)
	{
		$this->undo = $this->str;
		$this->str 	= Core::FormatToUrl($this->str, $lower, $spaces);

		$this->update();
		return $this;
	}

	/**
	 * Encuentra si una cadena contiene las palabras indicadas.
	 * @param string  $words Palabra o array de palabras a encontrar.
	 * @param boolean $lower ¿Convertir a minusculas?
	 */
	function contains($words, $lower = false)
	{
		return Core::Contains($this->str, $words, $lower);
	}

	/**
	 * Encuentra en el diccionario la palabra más similar a la indicada.
	 * Perfecto para crear un "¿No quiso decir... ?"
	 * Método de busqueda: Menor numero de procesos a realizar (insertar, reemplazar, eliminar) para transformar $str a una palabra del diccionario.
	 * @param array  $dic   	Diccionario
	 * @param boolean $debug 	¿Devolver más información?
	 * @return array La palabra más similar o false si la palabra se encontraba en el diccionario.
	 */
	function doMean($dic, $debug = false)
	{
		return Core::DoMean($this->str, $dic, $debug);
	}

	/**
	 * Encuentra en el diccionario la palabra más similar a la indicada.
	 * Perfecto para crear un "¿No quiso decir... ?"
	 * Método de busqueda: Calcula el porcentaje de similitud entre $str y la cadena del diccionario.
	 * @param array  $dic   	Diccionario
	 * @param boolean $debug 	¿Devolver más información?
	 * @return array La palabra más similar o false si la palabra se encontraba en el diccionario.
	 */
	function youMean($dic, $debug = false)
	{
		return Core::YouMean($this->str, $dic, $debug);
	}

	/**
	 * Corta una cadena a la mitad.
	 * @param integer $w     Número de veces a recortar
	 * @param boolean $strip ¿Quitar etiquetas HTML?
	 */
	function cut($strip = true, $w = 2)
	{
		$this->undo = $this->str;
		$this->str 	= Core::Cut($this->str, $strip, $w);

		$this->update();
		return $this;
	}

	/**
	 * Convierte una cadena con códigos BBCode.
	 * @param boolean $smilies ¿Convertir emoticones?
	 */
	function bbcode($smilies = false)
	{
		$this->undo = $this->str;
		$this->str 	= Core::BBCode($this->str, $smilies);

		$this->update();
		return $this;
	}

	/**
	 * Convierte una cadena con emoticones.
	 * @param boolean $bbcode ¿Convertir códigos bbc?
	 */
	function smilies($bbcode = false)
	{
		$this->undo = $this->str;
		$this->str 	= Core::Smilies($this->str, $bbcode);

		$this->update();
		return $this;
	}

	/**
	 * Encripta una cadena.
	 * @param integer $level Nivel de encriptación.
	 * @return string Cadena codificada.
	 */
	function encrypt($level = 0)
	{
		$this->undo = $this->str;
		$this->str 	= Core::Encrypt($this->str, $level);

		$this->update();
		return $this;
	}

	/**
	 * Traduce una cadena usando el servicio de traducción de Microsoft.
	 * @param string $from Lenguaje original.
	 * @param [type] $to   Lenguaje deseado.
	 * @param [type] $id   ID de la aplicación de desarrolladores.
	 */
	function translate($from = 'en', $to = LANG, $id = C9A399184CB7790D220EF5E812D7BFF636488705)
	{
		$this->undo = $this->str;
		$this->str 	= Core::Translate($this->str, $from, $to, $id);

		$this->update();
		return $this;
	}

	/**
	 * Convierte las direcciones web de una cadena en enlaces.
	 */
	function toURL()
	{
		$this->undo = $this->str;
		$this->str 	= Core::ToURL($this->str);

		$this->update();
		return $this;
	}

	/**
	 * Filtra los acentos de una cadena.
	 * @return string Cadena sin acentos.
	 */
	function filterAccents()
	{
		$this->undo = $this->str;
		$this->str 	= Core::FilterAccents($this->str);

		$this->update();
		return $this;
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