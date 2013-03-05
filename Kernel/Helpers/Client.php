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
 * @package 	Client
 * Permite obtener información del visitante.
 *
*/

# Acción ilegal.
if( !defined('BEATROCK') )
	exit;

class Client
{
	###############################################################
	## Guardar la información "$_POST" temporalmente.
	## Ejemplo: En caso de que suceda un error, estos datos podrán ser recuperados.
	###############################################################
	/**
	 * Guarda la información $_POST temporalmente.
	 * Ejemplo: En caso de que suceda un error, estos datos podrán ser recuperdos.
	 */
	static function SavePost()
	{
		global $site;
		$prefix = ( !empty($site['session_alias']) ) ? $site['session_alias'] : ROOT;

		foreach( $_POST as $key => $value )
			$_SESSION[$prefix . 'POST'][$key] = $value;
	}

	/**
	 * Obtiene la información guardada de $_POST
	 * Ejemplo: Restaurar la información perdida por un error.
	 */
	static function GetPost()
	{
		global $site, $_POST;
		$prefix = ( !empty($site['session_alias']) ) ? $site['session_alias'] : ROOT;

		if( empty($_SESSION[$prefix . 'POST']) )
			return;

		foreach( $_SESSION[$prefix . 'POST'] as $key => $value )
		{
			if( empty($value) )
				continue;

			$_POST[$key] = $value;
		}

		unset($_SESSION[$prefix . 'POST']);
	}

	/**
	 * Obtiene información del visitante.
	 * @param string $data Información:
	 * - ip: 		Dirección IP
	 * - agent: 	Agente del navegador
	 * - engine: 	Motor del navegador
	 * - browser: 	Nombre del navegador
	 * - os:		Nombre del sistema operativo
	 * - host: 		Nombre Host
	 * - from: 		Dirección web de donde proviene.
	 * - country: 	País de residencia.
	 * - timezone: Zona horaria.
	 * @return Información.
	 */
	static function Get($data)
	{
		global $config;
		$data = strtolower($data);

		switch( $data )
		{
			case 'ip':
				return IP;
			break;

			case 'agent':
				return AGENT;
			break;

			case 'engine':
				return self::GetEngine();
			break;

			case 'browser':
				return self::GetBrowser();
			break;

			case 'os':
				return self::GetOS();
			break;

			case 'host':
				return ( $config['server']['host'] ) ? @gethostbyaddr(IP) : IP;
			break;

			case 'from':
				return FROM;
			break;

			case 'country':
				# Compatibilidad con CloudFlare
				if( !empty($_SERVER['HTTP_CF_IPCOUNTRY']) )
					return $_SERVER['HTTP_CF_IPCOUNTRY'];

				$country = self::GetLocation();
				return $country['CountryCode'];
			break;

			case 'timezone':
				$country = self::GetLocation();
				return $country['TimezoneName'];
			break;
		}
	}

	/**
	 * Obtiene el motor del navegador a partir del agente.
	 * @param string $agent Agente
	 * @return string Motor
	 */
	static function GetEngine($agent = AGENT)
	{
		Lang::SetSection('global');

		$engines = array(
			'Webkit' 	=> 'AppleWebKit',
			'Presto' 	=> 'Presto',
			'Gecko' 	=> 'Gecko',
			'Trident'	=> 'Trident'
		);

		foreach( $engines as $engine => $pattern )
		{
			if( preg_match("/$pattern/i", $agent) )
				return $engine;
		}

		return _l('%unknow%');
	}

	/**
	 * Obtiene el nombre del navegador a partir del agente.
	 * @param string $agent Agente
	 * @return string Nombre del navegador.
	 */
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

		foreach( $navegadores as $navegador => $pattern )
		{
			if( preg_match("/$pattern/i", $agent) )
				return $navegador;
		}

		return _l('%unknow%');
	}

	/**
	 * Obtiene el sistema operativo a partir del Agente.
	 * @param string $agent Agente
	 * @return string Sistema operativo.
	 */
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

		foreach( $so_s as $so => $pattern )
		{
			if (preg_match("/$pattern/i", $agent) )
				return $so;
		}

		return _l('%unknow%');
	}

	/**
	 * Identifica si el Agente es un navegador móvil.
	 * @param string $agent Agente
	 * @return boolean Devuelve true si es un navegador móvil o false si es
	 * un navegador de escritorio o un BOT.
	 */
	static function IsMobile($agent = AGENT)
	{
		$browser 	= self::GetBrowser($agent);
		$os 		= self::GetOS($agent);

		if( preg_match("/Opera Mini|Opera Mobile|Mobile/i", $browser) )
			return true;

		if( preg_match("/Android|iPhone|iPod|BlackBerry/i", $os) )
			return true;

		return false;
	}

	/**
	 * Identifica si el Agente es un Robot/Spider.
	 * @param boolean $agent Devuelve true si es un BOT o false si es
	 * un navegador de escritorio o móvil.
	 */
	static function IsBOT($agent = AGENT)
	{
		$browser = self::GetBrowser($agent);
		return ( strpos($browser, 'BOT') == false ) ? false : true;
	}

	/**
	 * Obtiene información de la ubicación de la IP.
	 * @param string $ip  Dirección IP
	 * @param string $api Llave API de http://www.ipinfodb.com
	 */
	static function GetLocation($ip = IP, $api = 'ea1c287638a2410e58d17d91bd7d8df9f4ab53f8735b8274cb16de5bbacd00b6')
	{
		# Verificamos si hay caché de la IP.
		$cache 		= _SESSION('location_' . $ip);

		# ¡Si la hay! Devolvemos la caché.
		if( is_array($cache) )
			return $cache;

		# Obtenemos los datos de la API.
		$url 		= "http://api.ipinfodb.com/v2/ip_query.php?key=$api&ip=$ip&timezone=true";
		$data 		= @file_get_contents($url);

		# Error al obtener la API.
		if( !$data OR empty($data) )
			return false;

		# Transformamos el resultado XML a una matriz (array)
		$result 	= array();
		$fields 	= new SimpleXMLElement($data);

		foreach( $fields as $key => $value )
			$result[(string)$key] = (string)$value;

		# Guardamos en caché y devolvemos resultado.
		_SESSION('location_' . $ip, $result);
		return $result;
	}
}
?>