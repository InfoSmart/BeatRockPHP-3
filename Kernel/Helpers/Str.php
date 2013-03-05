<?
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart © 2013 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

# Acción ilegal
if( !defined('BEATROCK') )
	exit;

###############################################################
## Ayudante Str
###############################################################
## Permite la creación de cadenas inteligentes.
###############################################################
## Ejemplos:
##
## $manzanas = __('Me gustan las manazanas');
## $manzanas->upper(); // ME GUSTAN LAS MANZANAS
## $manzanas->undo()->replace('manzanas', 'peras'); // Me gustan las peras
## $manzanas->split(1); // Devuelve un array
###############################################################

class Str
{
	public $str 		= '';		# Cadena
	public $undo 		= '';		# Cadena antes del último cambio.
	public $original 	= '';		# Cadena original.

	public $length;					# Longitud de la cadena.
	public $md5;					# MD5 de la cadena.
	public $sha1;					# SHA1 de la cadena.

	private $first 		= false;	# Permite establecer si la cadena será el primer argumento a establecer.

	###############################################################
	## Constructor
	## - $str: Cadena.
	###############################################################
	function __construct($str)
	{
		# Ajustamos la cadena.
		$this->str 		= $str;
		$this->original = $str;

		# Actualizamos su longitud, md5, sha1, etc...
		$this->update();

		return $this;
	}

	###############################################################
	## Pasa la cadena por algún tipo de codificación.
	## - $type: Tipo.
	## 		- utf8: 		utf8_encode
	##		- base64: 		base64_encode
	## 		- htmlentities: htmlentities
	###############################################################
	function encode($type)
	{
		$this->undo = $this->str;

		switch( $type )
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

	###############################################################
	## Pasa la cadena por algún tipo de descodificación.
	## - $type: Tipo.
	## 		- utf8: 		utf8_encode
	##		- base64: 		base64_encode
	## 		- htmlentities: htmlentities
	###############################################################
	function decode($type)
	{
		$this->undo = $this->str;

		switch( $type )
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

	function valid($type = EMAIL)
	{
		return Core::Valid($this->str, $type);
	}

	###############################################################
	## Obtiene la longitud de la cadena.
	###############################################################
	function length()
	{
		return strlen($this->str);
	}

	###############################################################
	## Actualiza información de acceso fácil de la cadena.
	###############################################################
	function update()
	{
		$this->length 	= $this->length();
		$this->md5 		= md5($this->str);
		$this->sha1 	= sha1($this->str);

		return $this;
	}

	###############################################################
	## Devuelve la cadena a su estado anterior.
	###############################################################
	function undo()
	{
		$this->str = $this->undo;
		return $this;
	}

	###############################################################
	## Llamado de alguna función.
	## Decide que función trata de aplicarse a la cadena y devuelve
	## el resultado.
	###############################################################
	function __call($name, $arguments)
	{
		$this->undo = $this->str;
		$result 	= false;

		# No hay argumentos, la cadena siempre es requerida.
		if( empty($arguments) )
			$arguments = array($this->str);
		else
		{
			# Un intento fallido ha ocurrido, quizá la función
			# a llamar precisa que la cadena se encuentre en el primer argumento.
			if( $this->first )
				array_unshift($arguments, $this->str);

			# Poner la cadena en el último argumento, siempre funciona.
			else
				$arguments[] = $this->str;
		}

		# La función enrealidad se llama str_<funcion> (str_replace, str_split, etc...)
		if( function_exists('str_' . $name) )
			$result = call_user_func_array('str_' . $name, $arguments);

		# La función enrealidad se llama str<funcion> (strpos, strlen, etc...)
		else if( function_exists('str' . $name) )
			$result = call_user_func_array('str' . $name, $arguments);

		# La función enrealidad se llama strto<funcion> (strtolower, strtoupper, etc...)
		else if( function_exists('strto' . $name) )
			$result = call_user_func_array('strto' . $name, $arguments);

		# La función se llama así mismo, veamos si esto funciona...
		else if( function_exists($name) )
			$result = call_user_func_array($name, $arguments);

		# El resultado es un array, devolverlo.
		if( is_array($result) )
		{
			$this->first 	= false;
			return $result;
		}

		# El resultado ha fallado (¿un error con la función?)
		else if( !is_string($result) )
		{
			# Ni siquiera poner la cadena como primer argumento ha funcionado...
			# evitamos un blucle infinito.
			if( $this->first )
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

	###############################################################
	## Devolver resultado en una solicitud de tipo string.
	###############################################################
	function __toString()
	{
		return ( is_string($this->str) ) ? $this->str : $this->original;
	}
}
?>