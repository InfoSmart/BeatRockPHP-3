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

class Robot
{
	// Obtener toda la información posible de un sitio web.
	// - $link: Dirección web.
	static function Data($link)
	{
		if(!Core::Valid($link, URL))
			return false;

		$result = array();

		$result['html'] 		= self::GetHtml($link);
		$result['meta'] 		= self::GetMetas($result['html']);

		$result['title'] 		= self::GetTitle($result['html']);
		$result['description'] 	= $result['meta']['description'];
		$result['keywords'] 	= $result['meta']['keywords'];
		$result['links'] 		= self::GetLinks($result['html']);
		$result['headers'] 		= get_headers($link, 1);
		$result['lang'] 		= self::GetLang($result['html']);
		$result['status'] 		= $headers[0];
		$result['md5'] 			= md5($result['html']);
		$result['page'] 		= Core::GetPage($link);
		$result['url'] 			= str_ireplace("www.", "", Core::GetHost($link));

		$result['robots'] 		= self::GetRobots($html);

		if($result['robots']['nosnippet'] == 'true')
			unset($result['description']);

		return $result;
	}

	// Obtener el titulo de un sitio web.
	// - $html: Dirección web o código HTML del sitio.
	static function GetTitle($html)
	{
		$m 		= self::GetMetas($html);
		$res 	= $m['title'];

		if(!empty($res))
			return $res;

		preg_match('/<title>([^<]+)<\/title>/i', $html, $title);
		return (!empty($title[1])) ? $title[1] : false;
	}

	// Obtener las "meta etiquetes" de un sitio web.
	// - $html: Dirección web o código HTML del sitio.
	static function GetMetas($html)
	{
		if(Core::Valid($html, URL))
			$html = self::GetHtml($html);

		Bit::$ignore = true;

		$link 	= Io::SaveTemporal($html);
		$res 	= get_meta_tags($link);

		return $res;
	}

	// Obtener el idioma del sitio web.
	// - $html: Dirección web o código HTML del sitio.
	static function GetLang($html)
	{
		$m 		= self::GetMetas($html);
		$res 	= (!empty($m['content-language'])) ? $m['content-language'] : $m['lang'];

		if(!empty($res))
			return $res;

		preg_match('/lang="([^<]+)"/i', $html, $lang);
		return (!empty($lang[1])) ? substr($lang[1], 0, 2) : false;
	}

	// Obtener las direcciones web dentro del sitio web.
	// - $html: Dirección web o código HTML del sitio.
	static function GetLinks($html, $debug = false)
	{
		if(Core::Valid($html, URL))
			$html = self::GetHtml($html);

		preg_match_all('/<a\s+.*?href=[\"\']?([^\"\' >]*)[\"\']?[^>]*>(.*?)<\/a>/i', $html, $links, PREG_SET_ORDER);

		if($debug == false)
		{
			$res = array();

			foreach($links as $r)
				$res[] = $r[1];
		}
		else
			$res = $links;

		return (is_array($res)) ? $res : false;
	}

	// Obtener una lista de las reglas de la meta etiqueta "robots".
	// - $html: Dirección web o código HTML del sitio.
	static function GetRobots($html)
	{
		if(Core::Valid($html, URL))
			$html = self::GetHtml($html);

		$meta = self::GetMetas($html);

		if(empty($meta['robots']))
			return;

		$r 		= explode(",", $meta['robots']);
		$res 	= array();

		foreach($r as $robot)
		{
			$robot = trim($robot);
			$res[$robot] = "true";
		}

		return $res;
	}

	// Obtener el código HTML de un sitio web.
	// - $link: Dirección web.
	static function GetHtml($link)
	{
		global $site;

		$curl = new Curl($link, array(
			'agent' 	=> 'BeatRobot V1',
			'timeout' 	=> 10
		));

		return $curl->Get();
	}
}
?>