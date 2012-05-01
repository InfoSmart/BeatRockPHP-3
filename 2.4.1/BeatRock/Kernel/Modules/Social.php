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
	public static $data = Array();
	public static $reg_data = Array();
	public static $PATH_NOW = "";

	public static $fb = null;
	public static $tw = null;

	public static $go = null;
	public static $pl = null;
	
	// Función privada - Lanzar error.
	// - $function: Función causante.
	// - $msg: Mensaje del error.
	private static function Error($code, $function, $msg = '')
	{
		BitRock::setStatus($msg, __FILE__, Array('function' => $function));
		BitRock::launchError($code);
		
		return false;
	}
	
	// Función - Preparar los datos.
	// - $values (Array): Datos de preparación.
	public static function Prepare($values)
	{
		self::$data = $values;

		$PATH_NOW = preg_replace('/(?|&)code=(.*)/is', '', PATH_NOW);
		self::$PATH_NOW = $PATH_NOW;
		
		if(isset($values['facebook']))
		{
			Tpl::addMeta('fb:app_id', $values['facebook']['appId'], 'property');

			if(!empty($values['facebook']['admins']))
				Tpl::addMeta('fb:admins', $values['facebook']['admins'], 'property');
		}
	}

	// Función - Preparar API.
	// - $service (facebook, twitter): Servicio.
	public static function InitAPI($service = "facebook")
	{
		if($service !== 'facebook' AND $service !== 'twitter' AND $service !== 'google')
			return self::Error('12s', __FUNCTION__);

		if($service == 'facebook')
		{
			if(self::$fb !== null)
				return true;

			require(MODS . 'External' . DS . 'facebook' . DS . 'facebook.php');
			$data = self::$data['facebook'];
			
			if(empty($data['appId']) OR empty($data['secret']))
				return self::Error('13s', __FUNCTION__, 'Los datos para la conexión a Facebook no son correctos.');
			
			$fb = new Facebook(Array(
				'appId' => $data['appId'],
				'secret' => $data['secret']
			));
			
			if(!$fb)
				return false;

			self::$fb = $fb;
			return true;
		}

		if($service == "twitter")
		{
			if(self::$tw !== null)
				return true;

			require(MODS . 'External' . DS . 'twitter' . DS . 'twitteroauth.php');
			global $R;
			
			$data = self::$data['twitter'];
			$auth = Core::theSession('twitter_api');
			
			if(empty($data['key']) OR empty($data['secret']))
				return self::Error('13s', __FUNCTION__, 'Los datos para la conexión a Twitter no son correctos.');
			
			if($R['oauth_token'] == $auth['oauth_token'])
			{
				$tw = new TwitterOAuth($data['key'], $data['secret'], $auth['oauth_token'], $auth['oauth_token_secret']);
				$auth = $tw->getAccessToken($R['oauth_verifier']);
			}
			
			$tw = new TwitterOAuth($data['key'], $data['secret'], $auth['oauth_token'], $auth['oauth_token_secret']);

			self::$tw = $tw;
			return true;
		}

		if($service == "google")
		{
			if(self::$go !== null)
				return true;

			require(MODS . 'External' . DS . 'google' . DS . 'apiClient.php');
			require(MODS . 'External' . DS . 'google' . DS . 'contrib' . DS . 'apiPlusService.php');

			$data = self::$data['google'];

			if(empty($data['clientId']) OR empty($data['key']) OR empty($data['secret']))
				return self::Error('13s', __FUNCTION__, 'Los datos para la conexión a Google no son correctos.');
			
			$go = new apiClient();
			$go->setApplicationName(SITE_NAME);
			$go->setClientId($data['clientId']);
			$go->setClientSecret($data['secret']);
			$go->setDeveloperKey($data['key']);
			$go->setRedirectUri(self::$PATH_NOW);
			$go->setScopes(Array('https://www.googleapis.com/auth/plus.me'));
			
			$pl = new apiPlusService($go);

			self:$go = $go;
			self::$pl = $pl;
			return true;
		}
	}
	
	// Función - Preparar la obtención de datos de un usuario con un servicio.
	// - $service (facebook, twitter): Servicio.
	// - $filter (Bool): ¿Filtrar información?
	// - $scope: Permisos de la aplicación.
	public static function Init($service = "facebook", $filter = true, $scope = "")
	{
		if($service !== 'facebook' AND $service !== 'twitter' AND $service !== 'google')
			return self::Error("12s", __FUNCTION__);

		self::InitAPI($service);
		
		if($service == 'facebook')
		{
			$fb = self::$fb;

			if($fb == null)
				return self::Error('11s', __FUNCTION__, 'No se ha podido preparar la API del servicio correctamente.');

			$user = $fb->getUser();
			$me = null;
			
			if($user)
			{
				try
				{ $me = $fb->api('/me', 'GET'); }
				catch(FacebookApiException $e) 
				{ 
					self::Error('11s', __FUNCTION__, $e);
					$fb = false;
				}
			}
			else
			{
				$params = Array();

				if(!empty($scope))
					$params = Array('scope' => $scope);

				Core::Redirect($fb->getLoginUrl($params));
			}
		}
		
		if($service == "twitter")
		{
			$tw = self::$tw;

			if($tw == null)
				return self::Error("11s", __FUNCTION__, "No se ha podido preparar la API del servicio correctamente.");
			
			try
			{ $me = $tw->get('account/verify_credentials'); }
			catch(Exception $e)
			{ self::Error('11s', __FUNCTION__, $e); }
			
			if($me->error == 'Could not authenticate you.')
			{
				$req = $tw->getRequestToken(PATH_NOW);
				
				Core::theSession('twitter_api', Array(
					'oauth_token' => $req['oauth_token'],
					'oauth_token_secret' => $req['oauth_token_secret']
				));
				
				if(empty($req['oauth_token']))
					Core::Redirect(PATH_NOW);
				
				if($tw->http_code == 200 OR $tw->http_code == 401)
					Core::Redirect($tw->getAuthorizeURL($req['oauth_token']));
			}
		}
		
		if($service == 'google')
		{
			$go = self::$go;
			$pl = self::$pl;

			if($go == null)
				return self::Error('11s', __FUNCTION__, 'No se ha podido preparar la API del servicio correctamente.');

			$auth = Core::theSession('google_api');
			global $R;
			
			if(!empty($R['code']))
			{
				$go->authenticate();
				
				Core::theSession('google_api', Array(
					'access_token' => $go->getAccessToken()
				));

				Core::Redirect(self::$PATH_NOW);
			}
			
			if(!empty($auth['access_token']))
				$go->setAccessToken($auth['access_token']);
				
			if($go->getAccessToken())
			{
				try
				{ $me = $pl->people->get('me'); }
				catch(Exception $e) { self::Error('11s', __FUNCTION__, $e); }
				
				Core::theSession('google_api', Array(
					'access_token' => $go->getAccessToken()
				));
			}
			else
				Core::Redirect($go->createAuthUrl());
		}
		
		if(is_object($me))
			$me = get_object_vars($me);
		
		if(is_array($me) AND $filter)
			$me = CleanText($me);
			
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
			$me['profile_image_url'] = "http://graph.facebook.com/$me[id]/picture?type=large";
		
		return $me;
	}
	
	// Función - Preparar instancia para Facebook.
	// - $sec (init, js, all): Sección a implementar.
	// - $params (Array): Parametros de configuración inicial.
	public static function PrepareFacebook($sec = "all", $params = Array())
	{
		$html = "";
		$fb = self::$data['facebook'];
			
		if($sec == "init" OR $sec == "all")
		{
			if(empty($params['status']))
				$params['status'] = "true";
				
			if(empty($params['cookie']))
				$params['cookie'] = "true";
				
			if(empty($params['xfbml']))
				$params['xfbml'] = "true";
				
			if(empty($params['oauth']))
				$params['oauth'] = "true";
				
				
			$html .= "<script>
			window.fbAsyncInit = function() {
				FB.init({
					appId: \"$fb[appId]\",
					status: $params[status], 
					cookie: $params[cookie],
					xfbml: $params[xfbml],
					oauth: $params[oauth]
				});
			};</script>";
		}
		
		if($sec == "js" OR $sec == "all")
		{
			$html .= '<div id="fb-root"></div><script>(function(d){
				var js, id = "facebook-jssdk"; if (d.getElementById(id)) {return;}
				js = d.createElement("script"); js.id = id; js.async = true;
				js.src = "//connect.facebook.net/es_MX/all.js#xfbml=1&appId=' . $fb['appId'] . '";
				d.getElementsByTagName("head")[0].appendChild(js);
			}(document));</script>';
		}
		
		return $html;
	}

	// Función - Publicar una acción Open Graph en Facebook.
	// - $object: Objeto.
	// - $action: Acción.
	// - $url: Dirección web de la acción.
	public static function PublishAction($object, $action, $url)
	{
		self::InitAPI('facebook');
		$fb = self::$fb;

		if($fb == null)
			return self::Error('11s', __FUNCTION__, 'No se ha podido preparar la API del servicio correctamente.');

		$res = $fb->api("/me/$object:$action", 'POST', Array(
			'beverage' => $url
		));

		return $res['id'];
	}
	
	// Función - Iniciar sesión o registrar con el servicio.
	// - $service (facebook, twitter): Servicio.
	// - $cookie (Bool): ¿Auto conectarse con Cookies?
	public static function LoginOrNew($service = "facebook", $cookie = true)
	{
		if($service !== 'facebook' AND $service !== 'twitter' AND $service !== 'google')
			return self::Error('12s', __FUNCTION__);
			
		$info = self::Init($service);			
		$verify = Users::ServiceExist($info['id'], $service);

		return !$verify ? self::NewUser($service, $info, $cookie) : self::Login($service, $info, $cookie);
	}
	
	// Función privada - Iniciar sesion.
	// - $service (facebook, twitter): Servicio.
	// - $info: Información del usuario.
	// - $cookie (Bool): ¿Auto conectarse con Cookies?
	private static function Login($service, $info = "", $cookie = true)
	{
		if($service !== 'facebook' AND $service !== 'twitter' AND $service !== 'google')
			return self::Error('12s', __FUNCTION__);
			
		if(empty($info))
			$info = self::Init($service);
			
		$data = Users::Service($info['id'], $service);
		$user = Users::UserService($data['user_hash'], $service);
		
		if($data == false OR $user == false)
			return 'NOT_EXIST';
			
		Users::UpdateService(Array(
			'info' => json_encode($info)
		), $data['id']);
			
		Core::theSession('service_info', $data['info']);
		Users::Login($user['id'], $cookie);
		
		return true;
	}
	
	// Función privada - Agregar un nuevo usuario.
	// - $service (facebook, twitter): Servicio.
	// - $info: Información del usuario.
	// - $cookie (Bool): ¿Auto conectarse con Cookies?
	private static function NewUser($service, $info = "", $cookie = true)
	{
		if($service !== 'facebook' AND $service !== 'twitter' AND $service !== 'google')
			return self::Error('12s', __FUNCTION__);
		
		if(empty($info))
			$info = self::Init($service);
			
		$hash = Users::NewService($info['id'], $service, $info['username'], json_encode($info));
		
		self::$reg_data['user_hash'] = $hash;
		self::$reg_data['service'] = $service;
			
		$pass = "social";			
		$userId = Users::NewUser($info['username'], $pass, $info['name'], $info['email'], $info['birthday'], $info['profile_image_url'], false, self::$reg_data);
		
		if($cookie)
			self::Login($service, $info);
			
		return $userId;
	}

	// Función - Agregar una meta etiqueta para Open Graph.
	// - $object: Objeto.
	// - $param: Parametro/acción
	// - $value: Valor.
	public static function addOG($object, $param, $value)
	{
		Tpl::addMeta("$object:$param", $value, "property");
	}

	// Función - Agregar video para Open Graph.
	// - $video: Dirección del video o recurso para reproducirlo.
	// - $type: MIME TYPE del video/recurso.
	// - $width: Ancho de reproducción del video.
	// - $height: Altura de reproducción del video.
	// - $secure_url: Dirección segura (https) del video o recurso para reproducirlo.
	public static function addVideo($video, $type, $width = 400, $height = 300, $secure_video = "")
	{
		global $site;

		Tpl::addMeta("og:video", $video, "property");
		Tpl::addMeta("og:video:type", $type, "property");
		Tpl::addMeta("og:video:width", $width, "property");
		Tpl::addMeta("og:video:height", $height, "property");

		if(!empty($secure_video))
			Tpl::addMeta("og:video:secure_url", $secure_video, "property");

		$site['site_type'] = 'video.other';
	}

	// Función - Agregar audio para Open Graph.
	// - $audio: Dirección del archivo de audio o recurso para reproducirlo.
	// - $type: MIME TYPE del audio/recurso.
	// - $secure_url: Dirección segura (https) del archivo de audio o recurso para reproducirlo.
	public static function addAudio($audio, $type, $secure_audio = "")
	{
		Tpl::addMeta("og:audio", $audio, "property");
		Tpl::addMeta("og:audio:type", $type, "property");
		
		if(!empty($secure_audio))
			Tpl::addMeta("og:audio:secure_url", $secure_audio, "property");
	}
}
?>