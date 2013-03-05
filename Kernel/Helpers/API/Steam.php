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

class Steam
{
	static $API = null;

	// Obtener la información pública de un usuario.
	// - $id: Nombre de usuario o ID.
	static function GetInfo()
	{

	}

	// Preparar e implementar la API.
	static function Init()
	{
		if(self::$API !== null)
			return true;

		require APP_CTRLS . 'External' . DS . 'steam' . DS . 'SteamLogin.class.php';
		require APP_CTRLS . 'External' . DS . 'steam' . DS . 'SteamAPI.class.php';

		$sessionId 	= _SESSION('steam_userId');
		$userId 	= (!empty($sessionId)) ? $sessionId : SteamSignIn::validate();

		if(!is_numeric($userId))
			Core::Redirect(SteamSignIn::genUrl(false, false));

		_SESSION('steam_userId', $userId);

		$API = new SteamAPI($userId);

		if(!$API)
			return false;

		self::$API = $API;
		return true;	
	}

	// Obteniendo y verificando el recurso de la API.
	// - $user (Bool): ¿Verificar si el usuario esta online?
	static function API($user = true)
	{
		self::Init();
		$API = self::$API;

		if($API == null)
			return Social::Error('social.instance.fail', __FUNCTION__, '%error.steam%');

		if($user)
		{
			$verify = $API->me;

			if(!$verify)
				self::LogIn();
		}

		return $API;
	}

	// Iniciar sesión y obtener permisos del usuario.
	// - $return (bool): ¿Obtener la dirección de inicio en vez de redireccionar?
	static function LogIn($return = false)
	{
		_DELSESSION('steam_userId');
		$url = SteamSignIn::genUrl(false, false);

		if($return)
			return $url;
		
		Core::Redirect($url);
	}

	// Salir de la sesión en Steam.
	static function LogOut()
	{
		_DELSESSION('steam_userId');
	}

	// Obtener información básica del usuario.
	static function Get_Me()
	{
		$API 	= self::API();
		$me 	= null;

		try
		{
			$me = $API->me;
		}
		catch(Exception $e)
		{
			Social::Error('social.instance.fail', __FUNCTION__, $e);
		}

		return $me;
	}

	// Obtener los juegos del usuario.
	static function Get_Me_Games()
	{
		$API 	= self::API();
		$games 	= null;

		try
		{
			$games = $API->gameList;
		}
		catch(Exception $e)
		{
			Social::Error('social.instance.fail', __FUNCTION__, $e);
		}

		return $games;
	}
}
?>