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

class Client
{
	// Guardar información "POST" temporalmente.
	// Ejemplo: En caso de que suceda un error, estos datos podrán ser restaurados.
	static function SavePost()
	{
		global $site;		
		$prefix = (!empty($site['session_alias'])) ? $site['session_alias'] : ROOT;
		
		foreach($_POST as $param => $value)
			$_SESSION[$prefix . 'POST'][$param] = $value;
	}
	
	// Obtener información "POST" guardados.
	// Para restaurar información perdida.
	static function GetPost()
	{
		global $site, $_POST;		
		$prefix = (!empty($site['session_alias'])) ? $site['session_alias'] : ROOT;
		
		if(empty($_SESSION[$prefix . 'POST']))
			return;
		
		foreach($_SESSION[$prefix . 'POST'] as $param => $value)
		{
			if(empty($value))
				continue;
				
			$_POST[$param] = $value;
		}

		unset($_SESSION[$prefix . 'POST']);
	}
	
	// Obtener un dato del usuario.
	// - $i (ip, agent, browser, os, host, from): Dato a obtener.
	static function Get($i)
	{
		global $config;
		$i = strtolower($i);
		
		switch($i)
		{
			case 'ip':
				return IP;
			break;
			
			case 'agent':
				return AGENT;
			break;

			case 'engine':
				return Core::GetEngine();
			break;
			
			case 'browser':
				return Core::GetBrowser();
			break;
			
			case 'os':
				return Core::GetOS();
			break;
			
			case 'host':
				return ($config['server']['host']) ? @gethostbyaddr(IP) : IP;
			break;
			
			case 'from':
				return FROM;
			break;

			case 'country':
				if(!empty($_SERVER['HTTP_CF_IPCOUNTRY']))
					return $_SERVER['HTTP_CF_IPCOUNTRY'];

				$country = Core::GetLocation();
				return $country['CountryCode'];
			break;

			case 'timezone':
				$country = Core::GetLocation();
				return $country['TimezoneName'];
			break;
		}
	}
}
?>