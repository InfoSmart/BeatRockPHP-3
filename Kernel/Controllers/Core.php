<?
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart © 2012 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

// Acción ilegal.
if(!defined('BEATROCK'))
	exit;
	
/*
	Agradecimientos de aportación:

	Función: "Encriptación reversible" y desencriptación - http://www.emm-gfx.net/2008/11/encriptar-y-desencriptar-cadena-php/
*/

class Core
{
	// Definir una sesión.
	// - $param: Parametro/Nombre.
	// - $value: Valor, si se deja vacio se retornara el valor actual.
	static function SESSION($param, $value = '')
	{
		global $site;		
		$prefix = (!empty($site['session_alias'])) ? $site['session_alias'] : $_SESSION[ROOT]['session_alias'];
		
		$_SESSION[ROOT]['session_alias'] = $prefix;
			
		if(!empty($value))
			$_SESSION[$prefix . $param] = $value;
		else
			return $_SESSION[$prefix . $param];
	}
	
	// Eliminar una sesión.
	// - $param: Parametro/Nombre.
	static function DELSESSION($param)
	{
		global $site;
		$prefix = (!empty($site['session_alias'])) ? $site['session_alias'] : $_SESSION[ROOT]['session_alias'];
		
		unset($_SESSION[$prefix . $param]);
	}
	
	// Definir una cookie.
	// - $param: Parametro/Nombre.
	// - $value: Valor, si se deja vacio se retornara el valor actual.
	// - $duration: Duración en segundos.
	// - $path: Ubicación donde podrá ser válida.
	// - $domain: Dominio donde podrá ser válida.
	// - $secure (Bool): ¿Solo válida para HTTPS?
	// - $imgod (Bool): Si se activa, el navegador web no podrá acceder a la cookie. (Como por ejemplo en JavaScript)
	static function COOKIE($param, $value = '', $duration = '', $path = '', $domain = '', $secure = false, $imgod = false)
	{
		global $site;
		$prefix = (!empty($site['cookie_alias'])) ? $site['cookie_alias'] : $_SESSION[ROOT]['cookie_alias'];
		
		$_SESSION[ROOT]['cookie_alias'] = $prefix;
		
		if(empty($duration) OR $duration < 10)
			$duration = self::Time($site['cookie_duration'], 3);
			
		if(empty($path))
			$path = '/';
			
		if(empty($domain))
			$domain = $site['cookie_domain'];

		return !empty($value) ? setcookie($prefix . $param, $value, $duration, $path, $domain, $secure, $imgod) : $_COOKIE[$a . $param];
	}
	
	// Eliminar una cookie.
	// - $param: Parametro/Nombre.
	// - $path: Ubicación donde es válida.
	// - $domain: Dominio donde es válida.
	static function DELCOOKIE($param, $path = '', $domain = '')
	{
		global $site;
		$prefix = (!empty($site['cookie_alias'])) ? $site['cookie_alias'] : $_SESSION[ROOT]['cookie_alias'];
		
		if(empty($path))
			$path = '/';
			
		if(empty($domain))
			$domain = $site['cookie_domain'];
			
		$duration = self::Time(5, 3, true);
		
		setcookie($prefix . $param, '', $duration, $path, $domain);
		unset($_COOKIE[$prefix . $param]);
	}

	// Definir una sesión. (Según se adecua la situación)
	// - $param: Parametro/Nombre.
	// - $value: Valor, si se deja vacio se retornara el valor actual.
	static function CACHE($param, $value = '')
	{
		$mem = Mem::Ready();
			
		if(!empty($value))
		{
			if(!$mem)
				self::SESSION($param, $value);
			else
				Mem::SetM($param, $value);
		}
		else
			return (!$mem) ? self::SESSION($param) : Mem::GetM($param);
	}

	// Eliminar una sesión. (Según se adecua la situación)
	// - $param: Parametro/Nombre.
	static function DELCACHE($param)
	{
		$mem = Mem::Ready();
			
		if(!$mem)
			self::DELSESSION($param);
		else
			Mem::GetM($param);
	}
	
	// Realizar operación con el tiempo Unix actual especificado.
	// Sumar o Restar tiempo de forma más sencilla.
	// - $t (Int): Valor de la operación.
	// - $a (Int): Tipo de calculo. (1 - Tiempo Unix, 2 - Minutos, 3 - Horas, 4 - Días)
	// - $m (Bool): ¿Restar?
	static function Time($t = '', $a = 2, $m = false)
	{
		if(!is_numeric($t) OR $a < 1 OR $a > 3)
			return false;
			
		if($a == 1)
			$r = $t;
		if($a == 2)
			$r = ($t * 60);
		if($a == 3)
			$r = ($t * 60 * 60);
		if($a == 3)
			$r = ($t * 24 * 60 * 60);

		return ($m) ? (time() - $r) : (time() + $r);
	}
	
	// ¿True o False?
	// - $i (Int): Valor númerico de referencia.
	static function IsTrue($i)
	{
		return $i % 2 == 0 ? true : false;
	}
	
	// Comprimir HTML.
	// - $buffer: Buffer/HTML.
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
	
	// Verificar si un valor es válido.
	// - $value: Valor a comprobar.
	// - $type (email, username, ip, credit.card, url, password): Tipo de comprobación.
	static function Valid($value, $type = 'email')
	{
		if($type == 'email')
			$p = '^[^0-9][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,48}$/';
		if($type == 'username')
			$p = '^[a-z\d_]{5,32}$/i';
		if($type == 'ip')
			$p = '^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/';
		if($type == 'credit.card')
			$p = '^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/';
		if($type == 'url')
			$p = '^(http|https):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i';
		if($type == 'password')
			$p = '^[a-z+0-9]/i';
		if($type == 'subdomain')
			$p = '^[a-z]{3,10}$/i';
		if($type == 'domain')
			$p = '^([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i';
			
		if(empty($p) OR empty($value))
			return false;
			
		$valid = preg_match("/$p", $value);
		return (!$valid) ? false : true;
	}
	
	// Redireccionar página.
	// - $url (Url): Dirección web o página local.
	// - $javascript (Bool): ¿Usar JavaScript?
	static function Redirect($url = '', $javascript = false)
	{
		if(empty($url))
		{
			if($javascript)
				exit('<script>parent.document.location = "' . PATH . '"; document.location = "' . PATH . '";</script>');

			header('Location: ' . PATH);
			exit;
		}
		
		if(!Core::Valid($url, 'url') AND !self::Contains($url, array('./', '//localhost'), true))
			$url = PATH . $url;

		if($javascript)
			exit('<script>parent.document.location = "' . $url . '"; document.location = "' . $url . '";</script>');
			
		header('Location: ' . $url);
		exit;
	}
	
	// Verificar si una cadena tiene malas palabras.
	// - $str: Cadena a comprobar.
	static function StrBlocked($str)
	{
		if(!is_string($str))
			return false;

		$q = Query('wordsfilter')->Select('word')->Run();
		
		while($row = fetch_assoc($q))
		{
			$result = str_ireplace($row['word'], '*', $str);

			if($str !== $result)
				return true;
		}
		
		return false;
	}
	
	// Filtrar malas palabras de una cadena.
	// - $str: Cadena a filtrar.
	static function FilterBlocked($str, $replace = '****')
	{
		if(!is_string($str))
			return $str;
			
		$q = Query('wordsfilter')->Select('word')->Run();
		
		while($row = fetch_assoc($sql))
			$str = str_ireplace($row['word'], $replace, $str);
			
		return $str;
	}
	
	// Filtrar una cadena.
	// Protección contra: SQL Inyection - XSS Inyection (Opcional) - Caracteres inválidos.
	// - $str: Cadena a filtrar.
	// - $html (Bool): ¿Aplicar filtro XSS?
	// - $from (Charset): Codificación original de la cadena a filtar.
	// - $to (Charset): Codificación deseada de la cadena a filtrar.
	static function Filter($str, $html = true, $from = '', $to = '')
	{
		if(empty($to))
			$to = CHARSET;

		if(is_array($str))
		{
			if(count($str) > 50)
				return;

			$final = array();
			
			foreach($str as $param => $value)
				$final[$param] = self::Filter($value, $html, $from, $to);
				
			return $final;
		}
		
		if(!is_string($str) OR !SQL::Ready())
			return $str;
			
		if(self::IsUTF8($str))
			$from = 'UTF-8';
		
		$str = stripslashes(trim($str));

		if (!defined('ENT_SUBSTITUTE'))
			 define('ENT_SUBSTITUTE', 8);
		
		if($html)
			$str = htmlentities($str, ENT_QUOTES | ENT_SUBSTITUTE, $from, false);
			
		$str = SQL::escape_string($str);
		$str = str_ireplace('&amp;', '&', $str);

		if(!empty($from) AND $from !== $to)
			$str = iconv($from, $to . '//TRANSLIT//IGNORE', $str);
		
		return nl2br($str);
	}
	
	// Filtrar una cadena.
	// Protección contra: XSS Inyection - Caracteres inválidos.
	// - $str: Cadena a filtrar.
	// - $from (Charset): Codificación original de la cadena a filtar.
	// - $to (Charset): Codificación deseada de la cadena a filtrar.
	static function Clean($str, $from = '', $to = '')
	{
		if(empty($to))
			$to = CHARSET;

		if(is_array($str))
		{
			if(count($str) > 50)
				return;

			$final = array();
			
			foreach($str as $param => $value)
				$final[$param] = self::Clean($value, $from, $to);
				
			return $final;
		}
		
		if(!is_string($str))
			return $str;
			
		if(self::IsUTF8($str))
			$from = 'UTF-8';

		if (!defined('ENT_SUBSTITUTE'))
			 define('ENT_SUBSTITUTE', 8);
			
		$str = trim($str);
		$str = htmlentities($str, ENT_COMPAT | ENT_SUBSTITUTE, $from, false);			
		$str = str_replace('&amp;', '&', $str);
		
		if(!empty($from) AND $from !== $to)
			$str = iconv($from, $to . '//TRANSLIT//IGNORE', $str);
		
		return nl2br($str);
	}

	// Filtra una cadena y limpia sus Html entities.
	// - $str: Cadena a filtrar.
	static function CleanENT($str)
	{
		if(!is_string($str))
			return $str;
			
		if(substr_count($str, '&') && substr_count($str, ';')) 
		{ 
			$amp_pos 	= strpos($str, '&');
			$semi_pos 	= strpos($str, ';'); 
			
			if($semi_pos > $amp_pos) 
			{ 
				$tmp = substr($str, 0, $amp_pos); 
				$tmp = $tmp . substr($str, $semi_pos + 1, strlen($str)); 
				$str = $tmp;
				
				if(substr_count($str, '&') && substr_count($str, ';')) 
					$str = self::CleanENT($tmp); 
			} 
		}
		
		return nl2br($str);
	}
	
	// Filtrar una cadena.
	// Transforma una cadena de una codificación a otra.
	// - $str: Cadena a convertir.
	// - $html (Bool): ¿Filtrar HTML con HTML ENTITIES? (Evitar Inyección XSS)
	static function FixText($str, $from = 'UTF-8', $to = 'ISO-8859-15', $html = false)
	{
		// Causaba errores de comprobración, descomentar bajo responsabilidad propia.
		//if(!is_string($str))
			//return $str;

		if(is_array($str))
		{
			$final = array();
			
			foreach($str as $param => $value)
				$final[$param] = self::FixText($value, $from, $to, $html);
				
			return $final;
		}
			
		$str = trim($str);			
		$str = ($html) ? htmlentities($str, ENT_COMPAT | ENT_SUBSTITUTE, $from, false) : iconv($from, $to . '//TRANSLIT//IGNORE', $str);

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

	// Obtener el motor del navegador web del Agente.
	// - $agent: Agente.
	static function GetEngine($agent = AGENT)
	{
		Lang::SetSection('global');

		$engines = array(
			'Webkit' 	=> 'AppleWebKit',
			'Presto' 	=> 'Presto',
			'Gecko' 	=> 'Gecko',
			'Trident'	=> 'Trident'
		);

		foreach($engines as $engine => $pattern)
		{
			if(preg_match("/$pattern/i", $agent))
				return $engine;
		}

		return _l('%unknow%');
	}
	
	// Obtener el navegador web del Agente web.
	// - $agent: Agente web.
	static function GetBrowser($agent = AGENT)
	{
		Lang::SetSection('global');
			
		$navegadores = array(
		  'Opera Mini' 		=> 'Opera Mini',
		  'Opera Mobile' 	=> 'Opera Mobi',
		  'Mobile' 			=> 'Mobile',
		   
          'Opera' 			=> 'Opera',
          'Mozilla Firefox' => 'Firefox',
		  'RockMelt' 		=> 'RockMelt',
          'Google Chrome' 	=> 'Chrome',
		  'Maxthon' 		=> 'Maxthon',
		  
		  'Internet Explorer 10' 	=> 'MSIE 10',
		  'Internet Explorer 9' 	=> 'MSIE 9',
		  'Internet Explorer' 		=> 'MSIE',
		  
		  'Galeon' 		=> 'Galeon',
          'MyIE' 		=> 'MyIE',
          'Lynx' 		=> 'Lynx',
          'Konqueror' 	=> 'Konqueror',		  
		  'Mozilla' 	=> 'Mozilla/5',
		  
		  'Google BOT' 						=> 'Googlebot',
		  'Google Adsense BOT' 				=> 'Mediapartners-Google',
		  'Google AdWords BOT' 				=> 'Adsbot-Google',
		  'Google Images BOT' 				=> 'Googlebot-Image',
		  'Google Site Verification BOT' 	=> 'Google-Site-Verification',
		  
		  'Facebook BOT' 	=> 'facebookexternalhit',
		  'Twitter BOT' 	=> 'Twitterbot',
		  'PostRank BOT' 	=> 'PostRank',		  
		  'InfoSmart BOT'	=> 'InfoBot',
		  'Nikiri BOT' 		=> 'NikirinBOT',
		  
		  'Ezooms BOT' 				=> 'Ezooms',
		  'Yandex BOT' 				=> 'YandexBot',
		  'Alexa BOT' 				=> 'alexa.com',
		  'MetaURI BOT' 			=> 'MetaURI',
		  'Gnip.com BOT' 			=> 'UnwindFetchor',
		  'Creative Commons BOT' 	=> 'CC Metadata',
		  'LongURL BOT' 			=> 'LongURL',
		  'Bit.ly BOT' 				=> 'bitlybot',
		  'InAgist BOT' 			=> 'InAGist',
		  'Twitmunin BOT' 			=> 'Twitmunin',
		  'Twikle BOT' 				=> 'Twikle',
		  'AddThis BOT' 			=> 'AddThis.com',
		  
		  'Http Client' 			=> 'HttpClient'
		);
		
		foreach($navegadores as $navegador => $pattern)
		{
			if(preg_match("/$pattern/i", $agent))
				return $navegador;
		}

		return _l('%unknow%');
	}
	
	// Obtener el sistema operativo del Agente web.
	// - $agent: Agente web.
	static function GetOS($agent = AGENT) 
	{
		Lang::SetSection('global');
			
		$so_s = array(
			'Android' 		=> 'Android',
			'iPhone' 		=> 'iPhone',
			'iPod' 			=> 'iPod',
			'BlackBerry' 	=> 'BlackBerry',
			
			'Windows 8' 			=> 'Windows NT 6.2',
			'Windows 7' 			=> 'Windows NT 6.1',
			'Windows Vista' 		=> 'Windows NT 6.0',
			'Windows Server 2003'	=> 'Windows NT 5.2',
			'Windows XP' 			=> 'Windows NT 5.1|Windows XP',
			'Windows 2000' 			=> 'Windows NT 5.0|Windows 2000',
			'Windows 98' 			=> 'Windows 98|Win98',
		  
			'Windows 95' 	=> 'Windows 95|Win95|Windows_95',
			'Windows ME' 	=> 'Windows 98|Win 9x 4.90|Windows ME',
			'Linux' 		=> 'Linux|X11',
			'MacOS' 		=> 'Mac_PowerPC|Macintosh'
		);
		
		foreach($so_s as $so=>$pattern)
		{
			if(preg_match("/$pattern/i", $agent))
				return $so;
		}
		
		return '%unknow%';
	}
	
	// Identificar si el Agente web es un móvil.
	// - $agent: Agente web.
	static function IsMobile($agent = AGENT)
	{			
		$browser 	= self::GetBrowser($agent);
		$os 		= self::GetOS($agent);
			
		if(preg_match("/Opera Mini|Opera Mobile|Mobile/i", $browser))
			return true;
			
		if(preg_match("/Android|iPhone|iPod|BlackBerry/i", $os))
			return true;		
		
		return false;
	}

	// Obtener información de la ubicación de la IP.
	// - $ip: Dirección IP.
	// - $api: Llave API de http://www.ipinfodb.com
	static function GetLocation($ip = IP, $api = 'ea1c287638a2410e58d17d91bd7d8df9f4ab53f8735b8274cb16de5bbacd00b6')
	{
		$cache 		= _SESSION('location_' . $ip);

		if(is_array($cache))
			return $cache;

		$url 		= "http://api.ipinfodb.com/v2/ip_query.php?key=$api&ip=$ip&timezone=true";
		$data 		= file_get_contents($url);

		$result 	= array();
		$fields 	= new SimpleXMLElement($data);

		foreach($fields as $key => $value)
			$result[(string)$key] = (string)$value;

		_SESSION('location_' . $ip, $result);
		return $result;
	}
	
	// Identificar si el Agente web es un robot.
	// - $agent: Agente web.
	static function IsBOT($agent = AGENT)
	{
		$browser = self::GetBrowser($agent);
		return (strpos($browser, 'BOT') == false) ? false : true;
	}
	
	// Envíar un correo electrónico.
	// - $data (Array): Datos de envio y configuración.
	static function SendEmail($data)
	{
		if(!is_array($data))
			return false;
			
		if($data['method'] !== 'mail' AND $data['method'] !== 'phpmailer')
			$data['method'] 	= 'mail';
			
		if(empty($data['from']))
			$data['from'] 		= 'beatrock@infosmart.mx';
			
		if(empty($data['html']))
			$data['html'] 		= true;
			
		if(empty($data['from.name']))
			$data['from.name'] 	= (defined('SITE_NAME')) ? SITE_NAME : 'BeatRock';

		if(empty($data['content']))
		{
			$data['content'] 	= 'text/html';
			$data['html'] 		= true;
		}
			
		if($data['method'] == 'mail')
		{
			$headers = '';
			$headers .= "Return-Path: <$data[from]>\r\n";
			$headers .= "From: \"" . $data['from.name'] . "\" <$data[from]>\r\n";
			$headers .= "Reply-to: noreply <$data[from]>\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: $data[content]; charset=utf-8\r\n";
			
			$data['body'] = stripslashes(wordwrap($data['body'], 70));
			$data['result'] = @mail($data['to'], $data['subject'], $data['body'], $headers);
		}
		else
		{
			$Mail = new PHPMailer();	

			$Mail->From 	= $data['from'];
			$Mail->FromName = $data['from.name'];
			$Mail->Subject 	= $data['subject'];
			$Mail->Body 	= $data['body'];

			$Mail->AddAddress($data['to']);
			$Mail->MsgHTML($data['body']);
			$Mail->IsHTML($data['html']);
			
			if(!empty($data['host']) AND !empty($data['host.port']) AND !empty($data['host.username']))
			{
				$Mail->IsSMTP();					
				$Mail->Host 	= $data['host'];
				$Mail->Port 	= $data['host.port'];
				$Mail->SMTPAuth = true;
				$Mail->Username = $data['host.username'];
				$Mail->Password = $data['host.password'];
				
				if(!empty($data['host.secure']))
					$Mail->SMTPSecure = $data['host.secure'];
			}
			
			$data['result'] = $Mail->Send();
		}
		
		return $data['result'];
	}
	
	// Enviar un correo electrónico de Error.
	static function SendError()
	{
		global $config;
		
		if(empty($config['errors']['email.reports']) OR !self::Valid($config['errors']['email.reports']))
			return false;

		$last = @file_get_contents(BIT . 'LAST_SENDERROR');

		if(time() < $last AND !empty($last))
			return false;
		
		$message 	= Tpl::Process(KERNEL_TPL_BIT . '/Error.Mail', true);
		$sitename 	= (defined('SITE_NAME')) ? SITE_NAME : PATH;

		$result 	= self::SendEmail(array(
			'method' 	=> 'mail',
			'to' 		=> $config['errors']['email.reports'],
			'subject' 	=> _l('%problems%' . $sitename, 'global'),
			'body' 		=> $message
		));

		if($result == false)
		{
			$result = self::SendEmail(array(
				'method' 		=> 'phpmailer',
				'to'			=> $config['errors']['email.reports'],
				'subject' 		=> _l('%problems%' . $sitename, 'global'),
				'body'			=> $message,
				'host'			=> 'mail.infosmart.mx',
				'host.port' 	=> 26,
				'host.password' => ']X([=g.C{+Hi',
				'host.username' => 'beatrock@infosmart.mx'
			));
		}
		
		@file_put_contents(BIT . 'LAST_SENDERROR', (time() + (3 * 60)));
		return $result;
	}

	// Enviar un correo de recuperación.
	static function SendRecover()
	{
		global $config;

		if(empty($config['errors']['email.reports']) OR !self::Valid($config['errors']['email.reports']))
			return false;

		$last = @file_get_contents(BIT . 'LAST_SENDRECOVER');

		if(time() < $last AND !empty($last))
			return false;
		
		$message 	= Tpl::Process(KERNEL_TPL_BIT . '/Error.Recover', true);
		$sitename 	= (defined('SITE_NAME')) ? SITE_NAME : PATH;

		$result 	= self::SendEmail(array(
			'method' 	=> 'mail',
			'to' 		=> $config['errors']['email.reports'],
			'subject' 	=> _l('%problems%' . $sitename, 'global'),
			'body' 		=> $message
		));

		if($result == false)
		{
			$result = self::SendEmail(array(
				'method' 		=> 'phpmailer',
				'to'			=> $config['errors']['email.reports'],
				'subject' 		=> _l('%problems%' . $sitename, 'global'),
				'body'			=> $message,
				'host'			=> 'mail.infosmart.mx',
				'host.port' 	=> 26,
				'host.password' => ']X([=g.C{+Hi',
				'host.username' => 'beatrock@infosmart.mx'
			));
		}

		@file_put_contents(BIT . 'LAST_SENDRECOVER', (time() + (3 * 60)));
		return $result;
	}

	// Enviar un correo de sospecha
	static function SendWait()
	{
		global $config;

		if(empty($config['errors']['email.reports']) OR !self::Valid($config['errors']['email.reports']))
			return false;

		$last = @file_get_contents(BIT . 'LAST_SENDWAIT');

		if(time() < $last AND !empty($last))
			return false;
		
		$message 	= Tpl::Process(KERNEL_TPL_BIT . '/Error.Suspicion', true);
		$sitename 	= (defined('SITE_NAME')) ? SITE_NAME : PATH;

		$result 	= self::SendEmail(array(
			'method' 	=> 'mail',
			'to' 		=> $config['errors']['email.reports'],
			'subject' 	=> _l('%problems%' . $sitename, 'global'),
			'body' 		=> $message
		));

		if($result == false)
		{
			$result = self::SendEmail(array(
				'method' 		=> 'phpmailer',
				'to'			=> $config['errors']['email.reports'],
				'subject' 		=> _l('%problems%' . $sitename, 'global'),
				'body'			=> $message,
				'host'			=> 'mail.infosmart.mx',
				'host.port' 	=> 26,
				'host.password' => ']X([=g.C{+Hi',
				'host.username' => 'beatrock@infosmart.mx'
			));
		}

		@file_put_contents(BIT . 'LAST_SENDWAIT', (time() + (1 * 60)));
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