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
 * @package 	Core
 * Contiene funciones de procesamiento de datos y utilidades.
 *
*/

# Acción ilegal.
if (!defined('BEATROCK') )
	exit;

/*
	Agradecimientos de aportación:

	Función: "Encriptación reversible" y desencriptación - http://www.emm-gfx.net/2008/11/encriptar-y-desencriptar-cadena-php/
*/

/**
 * Time()
 * Tipos de calculo.
 */
const 	UNIX 	= 1,
		MINUTES = 2,
		HOURS 	= 3,
		DAYS 	= 4;

/**
 * Valid()
 * Tipo de información a válidar
 */
const	EMAIL 			= 1,
		USERNAME 		= 2,
		CREDIT_CARD 	= 3,
		PASSWORD 		= 4,
		SUBDOMAIN 		= 5,
		DOMAIN 			= 6,
		IP 				= 7,
		URL 			= 8;

class Core
{
	/**
	 * Define una sesión con el prefijo de sesiones.
	 * @param string $key 		Llave
	 * @param string $value 	Valor. Si se deja vacio se retornará su valor actual (si la hay).
	 */
	static function SESSION($key, $value = '')
	{
		global $site;
		$prefix = ( !empty($site['session_alias']) ) ? $site['session_alias'] : $_SESSION[ROOT]['session_alias'];

		$_SESSION[ROOT]['session_alias'] = $prefix;

		if ( !empty($value) )
			$_SESSION[$prefix . $key] = $value;
		else
			return $_SESSION[$prefix . $key];
	}

	/**
	 * Elimina una sesión con el prefijo de sesiones.
	 * @param string $key Llave
	 */
	static function DELSESSION($key)
	{
		global $site;
		$prefix = ( !empty($site['session_alias']) ) ? $site['session_alias'] : $_SESSION[ROOT]['session_alias'];

		unset($_SESSION[$prefix . $key]);
	}

	/**
	 * Define una cookie con el prefijo de cookies.
	 * @param string  $key    	Llave
	 * @param string  $value    Valor. Si se deja vacio se retornará su valor actual (si la hay).
	 * @param string  $duration Duración en segundos.
	 * @param string  $path     Ruta donde será válida.
	 * @param string  $domain   Dominio donde será válida.
	 * @param boolean $secure   ¿Solo válida en conexiones HTTPS?
	 * @param boolean $imgod    Si esta en true no podrá ser usada/modificada por el navegador (JavaScript)
	 */
	static function COOKIE($key, $value = '', $duration = '', $path = '', $domain = '', $secure = false, $imgod = false)
	{
		global $site;
		$prefix = ( !empty($site['cookie_alias']) ) ? $site['cookie_alias'] : $_SESSION[ROOT]['cookie_alias'];

		$_SESSION[ROOT]['cookie_alias'] = $prefix;

		# No tiene duración, usar la especificada en la base de datos.
		if ( empty($duration) OR $duration < 10 )
			$duration = self::Time($site['cookie_duration'], 3);

		# No tiene ruta, usar la raiz (Todas las ubicaciones de la aplicación)
		if ( empty($path) )
			$path = '/';

		# No tiene dominio, usar la especificada en la base de datos.
		if ( empty($domain) )
			$domain = $site['cookie_domain'];

		# Crear cookie o devolver el valor.
		return ( !empty($value) ) ? setcookie($prefix . $key, $value, $duration, $path, $domain, $secure, $imgod) : $_COOKIE[$prefix . $key];
	}

	/**
	 * Elimina una cookie.
	 * @param string $key 	 Llave
	 */
	static function DELCOOKIE($key)
	{
		global $site;
		$prefix 	= ( !empty($site['cookie_alias']) ) ? $site['cookie_alias'] : $_SESSION[ROOT]['cookie_alias'];
		$duration 	= self::Time(5, 3, true);

		setcookie($prefix . $key, '', $duration);
		unset($_COOKIE[$prefix . $key]);
	}

	/**
	 * Guarda un objeto en Memcache o en $_SESSION
	 * @param string $key 	Llave
	 * @param string $value Valor. Si se deja vacio se retornará su valor actual (si la hay).
	 */
	static function CACHE($key, $value = '')
	{
		$mem = Mem::Connected();

		if ( !empty($value) )
		{
			if ( !$mem )
				self::SESSION($param, $value);
			else
				Mem::SetM($param, $value);
		}
		else
			return ( !$mem ) ? self::SESSION($param) : Mem::GetM($param);
	}

	/**
	 * Elimina un objeto en Memcache o en $_SESSION
	 * @param string $key Llave
	 */
	static function DELCACHE($key)
	{
		$mem = Mem::Ready();

		if ( !$mem )
			self::DELSESSION($param);
		else
			Mem::GetM($param);
	}

	/**
	 * Aumenta o disminuye una cantidad de tiempo al tiempo Unix actual.
	 * @param string  $value 	Valor
	 * @param integer $type 	Tipo de calculo (UNIX, MINUTES, HOURS, DAYS)
	 * @param boolean $take 	¿Disminuir en vez de Aumentar?
	 */
	static function Time($value = '', $type = MINUTES, $take = false)
	{
		if ( !is_numeric($value) OR $type < 1 OR $type > 3 )
			return false;

		switch ( $type )
		{
			case UNIX:
				$operation = $value;
			break;

			case MINUTES:
				$operation = ( $value * 60 );
			break;

			case HOURS:
				$operation = ( $value * 60 * 60 );
			break;

			case DAYS:
				$operation = ( $value * 24 * 60 * 60 );
			break;
		}

		return ( $take ) ? (time() - $operation) : (time() + $operation);
	}

	/**
	 * Devuelve true en números pares.
	 * @param string $i Número.
	 */
	static function IsTrue($i)
	{
		return $i % 2 == 0 ? true : false;
	}

	/**
	 * Comprime el código HTML.
	 * @param string $buffer Código HTML.
	 */
	static function Compress($buffer)
	{
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
		$buffer = preg_replace('/\<!--(.*?)\-->/is', '', $buffer);
		$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
		$buffer = str_replace('{ ', '{', $buffer);
		$buffer = str_replace(' }', '}', $buffer);
		$buffer = str_replace('; ', ';', $buffer);
		$buffer = str_replace(' {', '{', $buffer);
		$buffer = str_replace('} ', '}', $buffer);
		$buffer = str_replace(' ,', ',', $buffer);
		$buffer = str_replace(' ;', ';', $buffer);

		return $buffer;
	}

	/**
	 * Verifica que la información sea válida.
	 * @param string $value Valor
	 * @param integer $type   	Tipo de información (EMAIL, USERNAME, IP, CREDIT_CARD, URL, PASSWORD, SUBDOMAIN, DOMAIN)
	 * @return boolean 		Devuelve true en caso de que el valor tenga una estructura válida en caso contrario devolverá false.
	 */
	static function Valid($value, $type = EMAIL)
	{
		if ( empty($value) )
			return false;

		switch ( $type )
		{
			case EMAIL:
				$p = '^[^0-9][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,48}$/';
			break;

			case USERNAME:
				$p = '^[^0-9][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,48}$/';
			break;

			case IP:
				$p = '^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/';
			break;

			case CREDIT_CARD:
				$p = '^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/';
			break;

			case URL:
				$p = '^(http|https):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i';
			break;

			case PASSWORD:
				$p = '^[a-z+0-9]/i';
			break;

			case SUBDOMAIN:
				$p = '^[a-z]{3,10}$/i';
			break;

			case DOMAIN:
				$p = '^([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i';
			break;

			default:
				return false;
			break;
		}

		$valid = preg_match("/$p", $value);
		return ( !$valid ) ? false : true;
	}
	/**
	 * Redirecciona a una página local o externa.
	 * @param string  $url        Dirección web o página local.
	 * @param boolean $javascript ¿Usar JavaScript?
	 */
	static function Redirect($url = '', $javascript = false)
	{
		if ( empty($url) )
		{
			if ( $javascript )
				exit('<script>parent.document.location = "' . PATH . '"; document.location = "' . PATH . '";</script>');

			header('Location: ' . PATH);
			exit;
		}

		if ( !Core::Valid($url, 'url') AND !self::Contains($url, array('./', '//localhost'), true) )
			$url = PATH . $url;

		if ( $javascript )
			exit('<script>parent.document.location = "' . $url . '"; document.location = "' . $url . '";</script>');

		header('Location: ' . $url);
		exit;
	}

	/**
	 * Verifica si una cadena contiene malas palabras.
	 * @param string $str 	Cadena
	 * @return boolean 		Devuelve true en caso de encontrar una mala palabra en caso contrario devolverá false.
	 */
	static function StrBlocked($str)
	{
		# Esto no es una cadena.
		if ( !is_string($str) )
			return false;

		# Obtengamos las malas palabras.
		$query = Query('wordsfilter')->Select('word')->Run();

		while ( $row = Assoc($query) )
		{
			# Remplazamos esta mala palabra por un *
			$result = str_ireplace($row['word'], '*', $str);

			# La nueva cadena no coincide con la original ¡una mala palabra!
			if( $str !== $result )
				return true;
		}

		# Cadena limpia.
		return false;
	}

	/**
	 * Pasa la cadena por un filtro de malas palabras.
	 * @param string $str     Cadena
	 * @param string $replace Reemplazo para las malas palabras.
	 * @return string 			Devuelve la cadena filtrada.
	 */
	static function FilterBlocked($str, $replace = '****')
	{
		# Esto no es una cadena.
		if( !is_string($str) )
			return $str;

		$query = Query('wordsfilter')->Select('word')->Run();

		# Reemplazamos las malas palabras.
		while( $row = Assoc($query) )
			$str = str_ireplace($row['word'], $replace, $str);

		return $str;
	}

	/**
	 * Filtra una cadena contra:
	 * SQL Inyection, XSS Inyection y Caracteres inválidos.
	 * @param string $str   Cadena o Array con las cadenas.
	 * @param boolean $html ¿Aplicar filtro XSS?
	 * @param string  $from Codificación original.
	 * @param string  $to   Codificación deseada.
	 */
	static function Filter($str, $html = true, $from = '', $to = '')
	{
		# Usar la codificación del servidor como codificación deseada.
		if ( empty($to) )
			$to = CHARSET;

		# Es una matriz, filtramos varias cadenas a la vez.
		if( is_array($str) )
		{
			# ¿Más de 50 valores?
			# Debido a que esta función es usada para filtrar toda la matriz de $_POST
			# con esto evitamos "posibles sobrecargas" con personas que envian información masiva
			# mediante $_POST (Gente sin nada que hacer)
			if( count($str) > 50 )
				return;

			$final = array();

			# Filtramos cada cadena.
			foreach ( $str as $key => $value )
				$final[$key] = self::Filter($value, $html, $from, $to);

			# Devolvemos array con las cadenas filtradas.
			return $final;
		}

		# Esto no es una cadena o
		# no hemos establecido una conexión con el servidor SQL.
		if ( !is_string($str) OR !SQL::Connected() )
			return $str;

		# Si la cadena tiene caracteres UTF-8, esa es su codificación de origen.
		# FIXME: La función IsUTF8 no funciona como debería.
		if ( self::IsUTF8($str) )
			$from = 'UTF-8';

		$str = stripslashes(trim($str));

		# Anti XSS Inyection
		if ( $html )
			$str = htmlentities($str, ENT_QUOTES | ENT_SUBSTITUTE, $from, false);

		# Usamos la función de filtrado de SQL Inyection dependiendo del tipo de servidor.
		$str = SQL::Escape($str);
		# Un pequeño fix personal.
		$str = str_ireplace('&amp;', '&', $str);

		# Convertir la codificación de la cadena a otra. (UTF-8 a ISO-8859?)
		if( !empty($from) AND $from !== $to )
			$str = iconv($from, $to . '//TRANSLIT//IGNORE', $str);

		return nl2br($str);
	}

	/**
	 * Filtra una cadena contra:
	 * XSS Inyection y Caracteres inválidos.
	 * @param string $str   Cadena o Array con las cadenas.
	 * @param string  $from Codificación original.
	 * @param string  $to   Codificación deseada.
	 */
	static function Clean($str, $from = '', $to = '')
	{
		# Usar la codificación del servidor como codificación deseada.
		if ( empty($to) )
			$to = CHARSET;

		# Es una matriz, filtramos varias cadenas a la vez.
		if ( is_array($str) )
		{
			# ¿Más de 50 valores?
			# Debido a que esta función es usada para filtrar toda la matriz de $_GET
			# con esto evitamos "posibles sobrecargas" con personas que envian información masiva
			# mediante $_GET (Gente sin nada que hacer)
			if ( count($str) > 50 )
				return;

			$final = array();

			# Filtramos cada cadena.
			foreach ( $str as $key => $value )
				$final[$key] = self::Clean($value, $from, $to);

			# Devolvemos array con las cadenas filtradas.
			return $final;
		}

		# Esto no es una cadena
		if ( !is_string($str) )
			return $str;

		# Si la cadena tiene caracteres UTF-8, esa es su codificación de origen.
		# FIXME: La función IsUTF8 no funciona como debería.
		if ( self::IsUTF8($str) )
			$from = 'UTF-8';

		$str = trim($str);
		$str = htmlentities($str, ENT_COMPAT | ENT_SUBSTITUTE, $from, false);
		# Un pequeño fix personal.
		$str = str_replace('&amp;', '&', $str);

		# Convertir la codificación de la cadena a otra. (UTF-8 a ISO-8859?)
		if( !empty($from) AND $from !== $to )
			$str = iconv($from, $to . '//TRANSLIT//IGNORE', $str);

		return nl2br($str);
	}

	/**
	 * Elimina las HTML ENTITIES de una cadena.
	 * @param string $str Cadena
	 */
	static function CleanENT($str)
	{
		# Esto no es una cadena.
		if ( !is_string($str) )
			return $str;

		if( substr_count($str, '&') && substr_count($str, ';') )
		{
			$amp_pos 	= strpos($str, '&');
			$semi_pos 	= strpos($str, ';');

			if ( $semi_pos > $amp_pos )
			{
				$tmp = substr($str, 0, $amp_pos);
				$tmp = $tmp . substr($str, $semi_pos + 1, strlen($str));
				$str = $tmp;

				if ( substr_count($str, '&') && substr_count($str, ';') )
					$str = self::CleanENT($tmp);
			}
		}

		return nl2br($str);
	}

	/**
	 * Repara la codificación de una cadena.
	 * @param string  $str  Cadena
	 * @param string  $from Codificación original.
	 * @param string  $to   Codificación deseada.
	 * @param boolean $html ¿Aplicar filtro XSS? (Más efectivo en esta función)
	 */
	static function FixText($str, $from = 'UTF-8', $to = 'ISO-8859-15', $html = false)
	{
		# Es una matriz, filtramos varias cadenas a la vez.
		if ( is_array($str) )
		{
			$final = array();

			# Filtramos cada cadena.
			foreach ( $str as $key => $value )
				$final[$key] = self::FixText($value, $from, $to, $html);

			# Devolvemos array con las cadenas filtradas.
			return $final;
		}

		$str = trim($str);
		$str = ( $html ) ? htmlentities($str, ENT_COMPAT | ENT_SUBSTITUTE, $from, false) : iconv($from, $to . '//TRANSLIT//IGNORE', $str);

		return nl2br($str);
	}

	// Codifica una cadena en UTF-8
	// Lo mismo que la función utf8_encode pero compatible con Array's.
	// - $str: Cadena o Array de cadenas.
	// - $noencode: ¿No codificar si ya estamos en UTF-8?
	static function UTF8Encode($str, $noencode = true)
	{
		if($noencode AND strtoupper(CHARSET) == 'UTF-8')
			return $str;

		if(!is_array($str))
			return utf8_encode($str);

		$final = array();

		foreach($str as $param => $value)
			$final[$param] = self::UTF8Encode($value);

		return $final;
	}

	// Descodifica una cadena en UTF-8.
	// Lo mismo que la función utf8_decode pero compatible con Array's.
	// - $str: Cadena o Array de cadenas.
	static function UTF8Decode($str)
	{
		if(!is_array($str))
			return utf8_decode($str);

		$final = array();

		foreach($str as $param => $value)
			$final[$param] = self::UTF8Decode($value);

		return $final;
	}

	// Limpiar cadena para uso especial.
	// - $str: Cadena a limpiar.
	// - $lower (Bool): ¿Convertir a minusculas?
	// - $spaces (Bool): ¿Quitar espacios?
	static function CleanString($str, $lower = true, $spaces = true)
	{
		if(!is_string($str))
			return $str;

		$str = trim($str);
		$str = preg_replace('/\s\s+/',' ', preg_replace('/[^A-Za-z0-9-]/', ' ', $str));

		if($lower)
			$str = strtolower($str);

		$str = ($spaces) ? str_replace(' ', '-', $str) : str_replace(' ', '', $str);
		return nl2br($str);
	}

	// Verificar si una cadena contiene ciertas palabras.
	// - $str: Cadena.
	// - $words: Palabra o Array de palabras a verificar existencia.
	// - $lower (Bool): ¿Convertir todo a minusculas?
	static function Contains($str, $words, $lower = false)
	{
		if(!is_string($str))
			return false;

		if(!is_string($words) AND !is_array($words))
			return false;

		if($lower)
			$str = strtolower($str);

		if(!is_array($words))
		{
			if($lower)
				$words = strtolower($words);

			if(is_numeric(@strpos($str, $words)))
				return true;
		}
		else
		{
			foreach($words as $word)
			{
				if($lower)
					$word = strtolower($word);

				if(is_numeric(@strpos($str, $word)))
					return true;
			}
		}

		return false;
	}

	// Encontrar la palabra del diccionario más similar a la cadena especificada.
	// Perfecto para hacer: ¿No quiso decir ...?
	// Metodo: Menor numero de procesos a realizar (insertar, reemplazar y eliminar) para transfomar $str a una cadena del diccionario.
	// - $str: Cadena.
	// - $dic (Array): Diccionario de palabras a encontrar similitud.
	// - $debug (Bool): ¿Retonar Array con más detalles?
	static function DoMean($str, $dic, $debug = false)
	{
		if(!is_string($str) OR !is_array($dic))
			return false;

		$l = 0;
		$r = array();

		foreach($dic as $word)
		{
			$i = levenshtein($str, $word);

			if($i == 0)
				return '';

			if($i < $l OR $l == 0)
			{
				$l = $i;

				$r['word'] 		= $str;
				$r['mean'] 		= $word;
				$r['similar'] 	= $l;
				similar_text($str, $word, $r['porcent']);
				$r['porcent']	= round($r['porcent']);
			}

			_r($r);
		}

		return ($debug) ? $r : $r['mean'];
	}

	// Encontrar la palabra del diccionario más similar a la cadena especificada.
	// Perfecto para hacer: ¿No quiso decir ...?
	// Metodo: Porcentaje de similitud entre $str y una cadena del diccionario.
	// - $str: Cadena.
	// - $dic (Array): Diccionario de palabras a encontrar similitud.
	// - $debug (Bool): ¿Retonar Array con más detalles?
	static function YouMean($str, $dic, $debug = false)
	{
		if(!is_string($str) OR !is_array($dic))
			return false;

		$l = 0;
		$r = array();

		foreach($dic as $word)
		{
			similar_text($str, $word, $i);

			if($i == 100)
				return '';

			if($i > $l)
			{
				$l = $i;

				$r['word'] 		= $str;
				$r['mean'] 		= $word;
				$r['porcent'] 	= $l;
			}
		}

		return ($debug) ? $r : $r['mean'];
	}

	// Cortar una cadena a la mitad.
	// - $str: Cadena a cortar.
	// - $strip: ¿Quitar las etiquetas HTML?
	// - $w: Numero de veces a recortar.
	static function Cut($str, $strip = true, $w = 2)
	{
		if(!is_string($str))
			return $str;

		if($strip)
			$str = strip_tags($str);

		$n 		= strlen($str);

		$s 		= 0;
		$c 		= false;

		while(!$c)
		{
			++$s;
			$new = round($n / $w);

			if($new > 5)
				$c = true;
			else
				++$w;

			if($s >= 20)
				return $str;
		}

		return substr($str, 0, $new);
	}

	// Convertir una cadena con códigos BBCode.
	// Cambie los códigos BBCODE desde /App/Setup.php
	// - $str: Cadena a convertir.
	// - $smilies (Bool): ¿Incluir emoticones?
	static function BBCode($str, $smilies = false)
	{
		if(!is_string($str))
			return $str;

		global $kernel;

		$str = _c($str);
		$str = preg_replace($kernel['bbcode_search'], $kernel['bbcode_replace'], $str);

		if($smilies)
			$str = self::Smilies($str);

		return $str;
	}

	// Convertir una cadena con emoticones.
	// Cambie los emoticones desde /App/Setup.php
	// - $str: Cadena a convertir.
	// - $bbcode (Bool): ¿Incluir conversión de códigos BBC?
	static function Smilies($str, $bbcode = false)
	{
		if(!is_string($str))
			return $str;

		global $kernel;

		foreach($kernel['emoticons'] as $e => $i)
			$str = str_replace($e, '<img src="' . PATH .  '/Kernel/Emoticon.php?e=' . $i . '" alt="' . $e . '" title="' . $e . '" />', $str);

		if($bbcode)
			$str = self::BBCode($str);

		return $str;
	}

	// Encriptar una cadena.
	// - $str: Cadena a encriptar.
	// - $level: Nivel de encriptación.
	static function Encrypt($str, $level = 0)
	{
		if(!is_string($str))
			return $str;

		global $config;
		$sec = $config['security'];

		if($level == 0)
			$level = $sec['level'];

		// Nivel 1: MD5
		if($level == 1)
			$str = md5($str . $sec['hash']);

		// Nivel 2: SHA1
		if($level == 2)
			$str = sha1($str . $sec['hash']);

		// Nivel 3: SHA256 con SHA1
		if($level == 3)
		{
			$s = hash_init('sha256', HASH_HMAC, $sec['hash']);
			hash_update($s, sha1($str));
			hash_update($s, $sec['hash']);
			$str = hash_final($s);
		}

		// Nivel 4: SHA256 con SHA1 y MD5
		if($level == 4)
		{
			$s = hash_init('sha256', HASH_HMAC, $sec['hash']);
			hash_update($s, sha1($str));
			hash_update($s, $sec['hash']);
			$str = hash_final($s);
			$str = md5($sec['hash'] . $str);
		}

		// Nivel 5: Codificación reversible.
		if($level == 5)
		{
			$result = '';

			for($i = 0; $i < strlen($str); $i++)
			{
				$char = substr($str, $i, 1);
				$keychar = substr($sec['hash'], ($i % strlen($sec['hash'])) -1, 1);
				$char = chr(ord($char) + ord($keychar));
				$result .= $char;
			}

			$str = base64_encode($result);
		}

		return $str;
	}

	// Desencriptar una cadena encriptada con el Nivel 5.
	// - $str: Cadena a desencriptar.
	static function Decrypt($str)
	{
		if(!is_string($str))
			return $str;

		global $config;
		$sec = $config['security'];

		$result = '';
		$str 	= base64_decode($str);

		for($i = 0; $i < strlen($str); $i++)
		{
			$char 		= substr($str, $i, 1);
			$keychar 	= substr($sec['hash'], ($i % strlen($sec['hash']))-1, 1);
			$char 		= chr(ord($char) - ord($keychar));
			$result 	.= $char;
		}

		return $result;
	}

	// Generar una cadena al azar.
	// - $length (Int): Numero de caracteres.
	// - $letters (Bool): ¿Incluir letras?
	// - $numbers (Bool): ¿Incluir numeros?
	// - $other (Bool): ¿Incuir otros caracteres?
	static function Random($length, $letters = true, $numbers = true, $other = false)
	{
		if(!is_numeric($length))
			return;

		$result = '';
		$poss = '';
		$i = 0;

		if($letters)
			$poss .= 'abcdefghijklmnopqrstuvwxyz';

		if($numbers)
			$poss .= '0123456789';

		if($other)
			$poss .= 'ABCDEFHIJKL@%&^*/(){}-_';

		$poss = str_split($poss, 1);

		for($i = 1; $i < $length; ++$i)
		{
			mt_srand((double)microtime() * 1000000);

			$num 		= mt_rand(1, count($poss));
			$result 	.= $poss[$num - 1];
		}

		return $result;
	}

	// Ocultar un error.
	static function HideError()
	{
		global $config;

		if(!$config['errors']['hidden'])
			return;

		if(!is_numeric($_SESSION['beatrock']['hidden']))
			$_SESSION['beatrock']['hidden'] = 0;

		++$_SESSION['beatrock']['hidden'];

		if($_SESSION['beatrock']['hidden'] < 5)
			exit("<META HTTP-EQUIV='refresh' CONTENT='0; URL=$PHP_SELF'>");

		else if($_SESSION['beatrock']['hidden'] < 10)
			self::Redirect(PATH);

		else
			unset($_SESSION['beatrock']['hidden']);
	}

	// Selecionar un dato al azar de los especificados.
	// - $options (Array): Datos.
	static function SelectRandom($options)
	{
		if(!is_array($options))
			return false;

		$i = 0;
		$m = rand(2, 9);

		while($i <= $m)
		{
			foreach($options as $option)
			{
				$i++;

				if($i == $m)
				{
					if(!empty($option))
						return $option;
					else
						$i--;
				}
			}
		}

		return false;
	}

	// Obtener el dominio de una dirección web.
	// - $url: Dirección web.
	static function GetDomain($url)
	{
		$bits = explode('/', $url);

		if ($bits[0]=='http:' || $bits[0]=='https:')
			$url= $bits[2];
		else
			$url= $bits[0];

		unset($bits);

		$bits = explode('.', $url);
		$idz = count($bits);
		$idz -= 3;

		if (strlen($bits[($idz+2)])==2)
			$url = $bits[$idz] . '.' . $bits[($idz+1)] . '.' . $bits[($idz+2)];
		else if (strlen($bits[($idz+2)])==0)
			$url=$bits[($idz)] . '.' . $bits[($idz+1)];
		else
			$url=$bits[($idz+1)] . '.' . $bits[($idz+2)];

		return $url;
	}

	// Obtener el host de una dirección web.
	// - $url: Dirección web.
	static function GetHost($url)
	{
		$parseUrl = parse_url(trim($url));

		if($parseUrl['host'])
			$result = trim($parseUrl['host']);
		else
		{
			$exp = explode('/', $parseUrl['path'], 2);
			$result = array_shift($ep);
		}

		return $result;
	}

	// Obtener la página de una dirección web.
	// - $url: Dirección web.
	static function GetPage($url)
	{
		$r  = "^(?:(?P<scheme>\w+)://)?";
        $r .= "(?:(?P<login>\w+):(?P<pass>\w+)@)?";
        $r .= "(?P<host>(?:(?P<subdomain>[-\w\.]+)\.)?" . "(?P<domain>[-\w]+\.(?P<extension>\w+)))";
        $r .= "(?::(?P<port>\d+))?";
        $r .= "(?P<path>[\w/]*/(?P<file>\w+(?:\.\w+)?)?)?";
        $r .= "(?:\?(?P<arg>[\w=&]+))?";
        $r .= "(?:#(?P<anchor>\w+))?";
        $r = "!$r!";

        preg_match ($r, $url, $out);

		if(!empty($out['file']))
			return $out['file'];
		else if(!empty($out['path']) AND $out['path'] !== "/")
			return $out['path'];
		else
			return '';
	}

	// Traducir una cadena.
	// Usando el servicio de traducción de Microsoft.
	// - $str: Cadena.
	// - $from: Lenguaje original.
	// - $to: Lenguaje a traducir.
	// - $id: ID de aplicación de desarrolladores "Microsoft".
	static function Translate($str, $from = 'en', $to = LANG, $id = C9A399184CB7790D220EF5E812D7BFF636488705)
	{
		if(empty($id) OR empty($str))
			return $str;

		global $site;

		if(empty($to))
			$to = $site['site_language'];

		$sstr = md5($str);
		$data = self::CACHE('translate_' . $sstr);

		if(!empty($data))
			return $data;

		$str = strip_tags($str);
		$str = _l($str);
		$str = rawurlencode($str);

		$url = "http://api.microsofttranslator.com/v2/Http.svc/Translate?appId=$id&text=$str&from=$from&to=$to";

		$data = strip_tags(Io::Read($url));
		$data = ucfirst($data);

		self::CACHE('translate_' . $sstr, $data);
		return $data;
	}

	// Transforma las direcciones en links encontradas en la cadena.
	// - $str: Cadena.
	static function ToURL($str)
	{
		$str = html_entity_decode($str);
		$str = preg_replace('/(http:\/\/|https:\/\/|www.)([A-Z0-9][A-Z0-9_-]*(?:\.[-A-Z0-9+&@#\/%=~_|?]*)+)?(\d+)?\/?/is', '<a href="${1}${2}" target="_blank">${2}</a>', $str);
		$str = str_ireplace('href="www.', 'href="http://www.', $str);

		return $str;
	}

	// Carga un archivo JSON y devuelve el array.
	// Esta función también "limpia" los comentarios de tipo /* */
	// - $file: Ruta/Dirección web del archivo JSON.
	static function LoadJSON($file)
	{
		if(!file_exists($file))
			return false;

		$data 	= file_get_contents($file);
		$data 	= preg_replace('/\/\*(.*?)\*\//is', '', $data);
		$data 	= json_decode($data, true);

		$error 	= json_last_error();

		return (!empty($error)) ? $error : $data;
	}

	// Filtra los acentos de una cadena.
	// - $str: Cadena.
	static function FilterAccents($str)
	{
		$str = htmlentities($str, ENT_QUOTES, 'UTF-8');

		$patron = array(
			'/&agrave;/' => 'a',
			'/&egrave;/' => 'e',
			'/&igrave;/' => 'i',
			'/&ograve;/' => 'o',
			'/&ugrave;/' => 'u',

			'/&aacute;/' => 'a',
			'/&eacute;/' => 'e',
			'/&iacute;/' => 'i',
			'/&oacute;/' => 'o',
			'/&uacute;/' => 'u',

			'/&acirc;/' => 'a',
			'/&ecirc;/' => 'e',
			'/&icirc;/' => 'i',
			'/&ocirc;/' => 'o',
			'/&ucirc;/' => 'u',

			'/&atilde;/' => 'a',
			'/&etilde;/' => 'e',
			'/&itilde;/' => 'i',
			'/&otilde;/' => 'o',
			'/&utilde;/' => 'u',

			'/&auml;/' => 'a',
			'/&euml;/' => 'e',
			'/&iuml;/' => 'i',
			'/&ouml;/' => 'o',
			'/&uuml;/' => 'u',

			'/&auml;/' => 'a',
			'/&euml;/' => 'e',
			'/&iuml;/' => 'i',
			'/&ouml;/' => 'o',
			'/&uuml;/' => 'u',

			'/&aring;/' => 'a',
			'/&ntilde;/' => 'n',
 		);

		$str = preg_replace(array_values($patron),array_keys($patron), $str);
		return $str;
	}

	// Agrega $append1 al principio y $append2 al final de cada valor.
	// - $array (Array): El array con los datos.
	// - $append1: Cadena que se agregara al principio.
	// - $append2: Cadena que se agregara al final.
	static function SplitArray($array, $append1 = '', $append2 = '<br />')
	{
		if(!is_array($array))
			return false;

		$result = array();

		foreach($array as $param => $value)
			$result[$param] .= $append1 . $value . $append2;

		return $result;
	}

	static function Xml2Array($xml)
	{
		$result = array();

		foreach($xml as $element)
		{
			$tag 	= $element->getName();
			$e 		= get_object_vars($element);

			if(!empty($e))
				$result[$tag] = ($element instanceof SimpleXMLElement) ? self::Xml2Array($element) : $e;
			else
				$result[$tag] = trim($element);
		}

		return $result;
	}

	// Obtener el uso de memoria en bytes por el proceso de Apache.
	// El proceso se llama "httpd"
	static function memory_usage()
    {
    	if(!function_exists('exec'))
    		return false;

        if(substr(PHP_OS, 0, 3) == 'WIN')
        {
            $output = array();
            exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output);

            return preg_replace('/[\D]/', '', $output[5]) * 1024;
        }
		else
        {
            $pid = getmypid();
            exec("ps -eo%mem,rss,pid | grep $pid", $output);
            $output = explode('  ', $output[0]);

            return $output[1] * 1024;
        }
    }

	// Obtener el uso de la carga media del sistema.
	// La carga del CPU.
	static function sys_load()
	{
        if(substr(PHP_OS, 0, 3) == 'WIN')
        {
        	if(!extension_loaded('com_dotnet'))
        		return 0;

			$wmi = new COM('WinMgmts:\\\\.');
			$cpus = $wmi->InstancesOf('Win32_Processor');
			$load = 0;

			foreach($cpus as $c)
				$load += $c->LoadPercentage;

			return $load;
		}
		else
        {
			$load = sys_getloadavg();
			return $load[0];
		}
	}

	// Convertir una cadena a un dato bool (true o false).
	// - $str: Cadena.
	static function Bool($str)
	{
		if(is_bool($var))
			return $var;
		else if($var === NULL || $var === 'NULL' || $var === 'null')
			return false;
		else if(is_string($var))
		{
			$var = trim($var);

			if($var == 'false')
				return false;
			else if($var == 'true')
				return true;
			else if($var == 'no')
				return false;
			else if($var == 'yes')
				return true;
			else if($var == 'off')
				return false;
			else if($var == 'on')
				return true;
			else if($var == '')
				return false;
			else if(ctype_digit($var))
			{
				if((int) $var)
					return true;
				else
					return false;
			}
			else
				return true;
		}
		else if(ctype_digit((string) $var))
		{
			if((int) $var)
				return true;
			else
			return false;
		}
		else if(is_array($var))
		{
			if(count($var))
				return true;
			else
				return false;
		}
		else if(is_object($var))
			return true;
		else
			return true;
	}

	// Identificar si la cadena es de codificación UTF-8.
	// - $str: Cadena.
	static function IsUTF8($str)
	{
		$len = strlen($str);

		for($i = 0; $i < $len; $i++)
		{
			$c = ord($str[$i]);

			if ($c > 128)
			{
				if(($c > 247))
					return false;
				else if($c > 239)
					$bytes = 4;
				else if($c > 223)
					$bytes = 3;
				else if($c > 191)
					$bytes = 2;
				else
					return false;

				if(($i + $bytes) > $len)
					return false;

				while ($bytes > 1)
				{
					$i++;
					$b = ord($str[$i]);

					if($b < 128 || $b > 191)
						return false;

					$bytes--;
				}
			}
		}

		return true;
	}
}
?>