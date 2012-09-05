<?php
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
	
class Social
{
	static $data 		= array();
	static $reg_data 	= array();
	static $PATH_NOW 	= '';

	static $tw = null;
	static $st = null;

	static $go = null;
	static $pl = null;

	static $SERVICES = array(
		'facebook', 'twitter', 'google', 'steam'
	);
	
	// Lanzar error.
	// - $code: Código del error.
	// - $function: Función causante.
	// - $message: Mensaje del error.
	static function Error($code, $function, $message = '')
	{
		Lang::SetSection('mod.social');

		BitRock::SetStatus($msg, __FILE__, array('function' => $function));
		BitRock::LaunchError($code);
		
		return false;
	}

	// Obtener el recurso de Facebook.
	static function GetFb()
	{
		Fb::Init();
		return Fb::$api;
	}

	// Obtener el recurso de Twitter.
	static function GetTwitter()
	{
		self::InitAPI('twitter');
		return self::$tw;
	}

	// Obtener el recurso de Steam.
	static function GetSteam()
	{
		self::InitAPI('steam');
		return self::$tw;
	}

	// Obtener el recurso de Steam.
	static function GetPlus()
	{
		self::InitAPI('google');
		return self::$pl;
	}
	
	// Preparar los datos.
	// - $values (Array): Datos de preparación.
	static function Prepare($values)
	{
		self::$data 	= $values;
		self::$PATH_NOW = preg_replace('/(?|&)code=(.*)/is', '', PATH_NOW);
		
		if(isset($values['facebook']))
		{
			Tpl::addMeta('fb:app_id', $values['facebook']['appId'], 'property');

			if(!empty($values['facebook']['admins']))
				Tpl::addMeta('fb:admins', $values['facebook']['admins'], 'property');
		}
	}

	// Preparar API.
	// - $service (facebook, twitter): Servicio.
	static function InitAPI($service = 'facebook')
	{
		if(!in_array($service, self::$SERVICES))
			return self::Error('social.service', __FUNCTION__);

		if($service == 'facebook')
			Fb::Init();

		if($service == 'twitter')
			Twitter::Init();

		if($service == 'google')
		{
			if(self::$go !== null)
				return true;

			require MODS . 'External' . DS . 'google' . DS . 'apiClient.php';
			require MODS . 'External' . DS . 'google' . DS . 'contrib' . DS . 'apiPlusService.php';

			$data = self::$data['google'];

			if(empty($data['clientId']) OR empty($data['key']) OR empty($data['secret']))
				return self::Error('social.instance', __FUNCTION__, '%error.google.data%');
			
			$go = new apiClient();
			$go->setApplicationName(SITE_NAME);
			$go->setClientId($data['clientId']);
			$go->setClientSecret($data['secret']);
			$go->setDeveloperKey($data['key']);
			$go->setRedirectUri(self::$PATH_NOW);
			$go->setScopes(Array('https://www.googleapis.com/auth/plus.me'));
			
			$pl = new apiPlusService($go);

			self:$go 	= $go;
			self::$pl 	= $pl;

			return true;
		}

		if($service == 'steam')
		{
			if(self::$st !== null)
				return true;

			require MODS . 'External' . DS . 'steam' . DS . 'SteamLogin.class.php';
			require MODS . 'External' . DS . 'steam' . DS . 'SteamAPI.class.php';
		}
	}
	
	// Preparar la obtención de datos de un usuario con un servicio.
	// - $service (facebook, twitter): Servicio.
	// - $filter (Bool): ¿Filtrar información?
	// - $scope: Permisos de la aplicación.
	static function Init($service = 'facebook', $filter = true, $scope = '')
	{
		if(!in_array($service, self::$SERVICES))
			return self::Error('social.service', __FUNCTION__);
		
		if($service == 'facebook')
		{
			Fb::SetScope($scope);
			$me = Fb::Get_Me();
		}
		
		if($service == 'twitter')
			$me = Twitter::Get_Me();
		
		if($service == 'google')
		{
			$go = self::$go;
			$pl = self::$pl;

			if($go == null)
				return self::Error('social.instance.fail', __FUNCTION__, '%error.google%');

			global $R;
			$auth = _SESSION('google_token');
			
			if(isset($R['code']))
			{
				$go->authenticate();
				
				_SESSION('google_token', array(
					'access_token' => $go->getAccessToken()
				));

				Core::Redirect(self::$PATH_NOW);
			}
			
			if(isset($auth['access_token']))
				$go->setAccessToken($auth['access_token']);
				
			if($go->getAccessToken())
			{
				try
				{ $me = $pl->people->get('me'); }
				catch(Exception $e) { self::Error('social.instance.fail', __FUNCTION__, $e); }
				
				_SESSION('google_token', array(
					'access_token' => $go->getAccessToken()
				));
			}
			else
				Core::Redirect($go->createAuthUrl());
		}

		if($service == 'steam')
		{
			$sessionId 	= _SESSION('steam_userId');
			$userId 	= (!empty($sessionId)) ? $sessionId : SteamSignIn::validate();

			if(!is_numeric($userId))
				Core::Redirect(SteamSignIn::genUrl(false, false));
			else
				_SESSION('steam_userId', $userId);

			$st = new SteamAPI($userId);
			$me = $st->me;

			if($me == false)
			{
				Core::delSession('steam_userId');
				Core::Redirect(SteamSignIn::genUrl(false, false));
			}

			$me['games'] 	= $st->gameList;
			self::$st 		= $st;
		}
		
		if(is_object($me))
			$me = get_object_vars($me);
		
		if(is_array($me) AND $filter)
			$me = _c($me);
			
		if(!is_numeric($me['id']))
			$me['id'] = $me['id_str'];
		
		if(empty($me['username']))
			$me['username'] = $me['screen_name'];
		
		if(empty($me['username']))
			$me['username'] = $me['displayName'];
			
		if(empty($me['name']) OR is_array($me['name']))
			$me['name'] = $me['username'];
			
		if(empty($me['profile_image_url']))
			$me['profile_image_url'] = $me['image']['url'];
			
		if(empty($me['profile_image_url']))
			$me['profile_image_url'] = 'http://graph.facebook.com/' . $me['id'] . '/picture?type=large';
		
		return $me;
	}
	
	// Preparar instancia para Facebook.
	// - $sec (init, js, all): Sección a implementar.
	// - $params (Array): Parametros de configuración inicial.
	static function PrepareFacebook($section = 'all', $params = array())
	{		
		return Fb::Prepare($section, $params);
	}

	static function PrepareTwitter()
	{
		return Twitter::Prepare();
	}

	static function PreparePlus($lang = 'es')
	{
		$html = '<script type="text/javascript" src="https://apis.google.com/js/plusone.js">';

		if(!empty($lang))
			$html .= "{lang: \"$lang\"}";

		$html .= '</script>';
		return $html;
	}
	
	// Iniciar sesión o registrar con el servicio.
	// - $service (facebook, twitter): Servicio.
	// - $cookie (Bool): ¿Auto conectarse con Cookies?
	static function LoginOrNew($service = 'facebook', $cookie = true)
	{
		if(!in_array($service, self::$SERVICES))
			return self::Error('social.service', __FUNCTION__);
			
		$info 	= self::Init($service);			
		$verify = Users::ServiceExist($info['id'], $service);

		return (!$verify) ? self::NewUser($service, $info, $cookie) : self::Login($service, $info, $cookie);
	}
	
	// Iniciar sesion.
	// - $service (facebook, twitter): Servicio.
	// - $info: Información del usuario.
	// - $cookie (Bool): ¿Auto conectarse con Cookies?
	static function Login($service, $info = '', $cookie = true)
	{
		if(!in_array($service, self::$SERVICES))
			return self::Error('social.service', __FUNCTION__);
			
		if(empty($info))
			$info = self::Init($service);
			
		$data = Users::Service($info['id'], $service);
		$user = Users::UserService($data['user_hash'], $service);
		
		if($data == false OR $user == false)
			return 'NOT_EXIST';
			
		Users::UpdateService(array(
			'info' => _f(json_encode($info), false)
		), $data['id']);
			
		_SESSION('service_info', $data['info']);
		Users::Login($user['id'], $cookie);
		
		return true;
	}

	// Elimina las sesiones sociales.
	static function Logout()
	{
		Core::delSession('steam_userId');
		Core::delSession('twitter_token');
		Core::delSession('google_token');
	}
	
	// Agregar un nuevo usuario.
	// - $service (facebook, twitter): Servicio.
	// - $info: Información del usuario.
	// - $cookie (Bool): ¿Auto conectarse con Cookies?
	static function NewUser($service, $info = '', $cookie = true)
	{
		if(!in_array($service, self::$SERVICES))
			return self::Error('social.service', __FUNCTION__);
		
		if(empty($info))
			$info = self::Init($service);
			
		$hash = Users::NewService($info['id'], $service, $info['username'], _f(json_encode($info), false));
		
		self::$reg_data['user_hash'] 	= $hash;
		self::$reg_data['service'] 		= $service;
			
		$pass = 'social';			
		$userId = Users::NewUser($info['username'], $pass, $info['name'], $info['email'], $info['birthday'], $info['profile_image_url'], false, self::$reg_data);
		
		if($cookie)
			self::Login($service, $info);
			
		return $userId;
	}

	// Agregar una meta etiqueta para Open Graph.
	// - $object: Objeto.
	// - $param: Parametro/acción
	// - $value: Valor.
	static function addOG($object, $param, $value)
	{
		Tpl::addMeta('$object:$param', $value, 'property');
	}

	// Agregar video para Open Graph.
	// - $video: Dirección del video o recurso para reproducirlo.
	// - $type: MIME TYPE del video/recurso.
	// - $width: Ancho de reproducción del video.
	// - $height: Altura de reproducción del video.
	// - $secure_url: Dirección segura (https) del video o recurso para reproducirlo.
	static function addVideo($video, $type, $width = 400, $height = 300, $secure_video = '')
	{
		global $site;

		Tpl::addMeta('og:video', $video, 'property');
		Tpl::addMeta('og:video:type', $type, 'property');
		Tpl::addMeta('og:video:width', $width, 'property');
		Tpl::addMeta('og:video:height', $height, 'property');

		if(!empty($secure_video))
			Tpl::addMeta('og:video:secure_url', $secure_video, 'property');

		$site['site_type'] = 'video.other';
	}

	// Agregar audio para Open Graph.
	// - $audio: Dirección del archivo de audio o recurso para reproducirlo.
	// - $type: MIME TYPE del audio/recurso.
	// - $secure_url: Dirección segura (https) del archivo de audio o recurso para reproducirlo.
	static function addAudio($audio, $type, $secure_audio = '')
	{
		Tpl::addMeta('og:audio', $audio, 'property');
		Tpl::addMeta('og:audio:type', $type, 'property');
		
		if(!empty($secure_audio))
			Tpl::addMeta('og:audio:secure_url', $secure_audio, 'property');
	}

	// Obtener la información pública de un usuario.
	// - $service: Servicio.
	// - $id: Nombre de usuario o ID.
	public static function GetInfo($service = 'facebook', $id)
	{
		if(!in_array($service, self::$SERVICES))
			return false;

		if($service == 'facebook')
			$url = 'http://graph.facebook.com/' . $id;

		if($service == 'twitter')
			$url = 'https://api.twitter.com/1/users/lookup.json?screen_name=' . $id . '&include_entities=true';

		if($service == 'steam')
		{
			self::InitAPI('steam');

			$steam 	= new SteamAPI($id);
			$me 	= $steam->me;

			if($me == false)
				return false;

			return $me;
		}

		Curl::Init($url);
		$data = Curl::Get();
		$data = json_decode($data, true);

		if(isset($data['error']) OR isset($data['errors']))
			return false;

		return $data;
	}

	// Preparar un plugin de Facebook.
	// - $plugin: El plugin ha preparar.
	// - $href: Dirección/Dominio para el plugin.
	// - $params: Parametros de configuración.
	static function FacebookPlugin($plugin = 'like_button', $href = PATH_NOW, $params = array())
	{
		return Fb::Plugin($plugin, $href, $params);
	}

	// Preparar un botón de Twitter.
	// - $type: Tipo de botón a preparar.
	// - $href: Dirección/Usuario/Hashtag para el botón.
	// - $params: Parametros de configuración.
	static function TwitterButton($type = 'share', $href = PATH_NOW, $params = array())
	{
		return Twitter::Plugin($type, $href, $params);
	}

	// Preparar un botón de Google+
	// - $href: Dirección/Usuario/Hashtag para el botón.
	// - $params: Parametros de configuración.
	static function PlusButton($type = 'share', $href = PATH_NOW, $params = array())
	{
		$html = new Html('div');
		$html->Set('class', 'g-plusone');
		
		if(!empty($params['size']))
			$html->Set('data-size', $params['size']);

		if(!empty($params['count']))
			$html->Set('data-annotation', $params['count']);

		return $html->Build();
	}
}
?>