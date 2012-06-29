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

	static $fb = null;
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
		{
			if(self::$fb !== null)
				return true;

			require MODS . 'External' . DS . 'facebook' . DS . 'facebook.php';
			$data = self::$data['facebook'];
			
			if(empty($data['appId']) OR empty($data['secret']))
				return self::Error('social.instance', __FUNCTION__, '%error.facebook.data%');
			
			$fb = new Facebook(array(
				'appId'		=> $data['appId'],
				'secret'	=> $data['secret']
			));
			
			if(!$fb)
				return false;

			self::$fb = $fb;
			return true;
		}

		if($service == 'twitter')
		{
			if(self::$tw !== null)
				return true;

			require MODS . 'External' . DS . 'twitter' . DS . 'twitteroauth.php';
			global $R;
			
			$data = self::$data['twitter'];
			$auth = Core::theSession('twitter_api');
			
			if(empty($data['key']) OR empty($data['secret']))
				return self::Error('social.instance', __FUNCTION__, '%error.twitter.data%');
			
			if($R['oauth_token'] == $auth['oauth_token'])
			{
				$tw 	= new TwitterOAuth($data['key'], $data['secret'], $auth['oauth_token'], $auth['oauth_token_secret']);
				$auth 	= $tw->getAccessToken($R['oauth_verifier']);
			}
			
			$tw = new TwitterOAuth($data['key'], $data['secret'], $auth['oauth_token'], $auth['oauth_token_secret']);

			self::$tw = $tw;
			return true;
		}

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

		self::InitAPI($service);
		
		if($service == 'facebook')
		{
			$fb = self::$fb;

			if($fb == null)
				return self::Error('social.instance.fail', __FUNCTION__, '%error.facebook%');

			$user 	= $fb->getUser();
			$me 	= null;
			
			if($user)
			{
				try
				{ $me = $fb->api('/me', 'GET'); }
				catch(FacebookApiException $e) 
				{ self::Error('social.instance.fail', __FUNCTION__, $e); }
			}
			else
			{
				$params = array();

				if(!empty($scope))
					$params = array('scope' => $scope);

				Core::Redirect($fb->getLoginUrl($params));
			}
		}
		
		if($service == 'twitter')
		{
			$tw = self::$tw;

			if($tw == null)
				return self::Error('social.instance.fail', __FUNCTION__, '%error.twitter%');
			
			try
			{ $me = $tw->get('account/verify_credentials'); }
			catch(Exception $e)
			{ self::Error('social.instance.fail', __FUNCTION__, $e); }
			
			if($me->error == 'Could not authenticate you.')
			{
				$req = $tw->getRequestToken(PATH_NOW);
				
				Core::theSession('twitter_api', array(
					'oauth_token' 			=> $req['oauth_token'],
					'oauth_token_secret'	=> $req['oauth_token_secret']
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
				return self::Error('social.instance.fail', __FUNCTION__, '%error.google%');

			global $R;
			$auth = Core::theSession('google_api');
			
			if(!empty($R['code']))
			{
				$go->authenticate();
				
				Core::theSession('google_api', array(
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
				catch(Exception $e) { self::Error('social.instance.fail', __FUNCTION__, $e); }
				
				Core::theSession('google_api', array(
					'access_token' => $go->getAccessToken()
				));
			}
			else
				Core::Redirect($go->createAuthUrl());
		}

		if($service == 'steam')
		{
			$sessionId 	= Core::theSession('steam_userId');
			$userId 	= (!empty($sessionId)) ? $sessionId : SteamSignIn::validate();

			if(!is_numeric($userId))
				Core::Redirect(SteamSignIn::genUrl(false, false));
			else
				Core::theSession('steam_userId', $userId);

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
			$me['profile_image_url'] = 'http://graph.facebook.com/$me[id]/picture?type=large';
		
		return $me;
	}
	
	// Preparar instancia para Facebook.
	// - $sec (init, js, all): Sección a implementar.
	// - $params (Array): Parametros de configuración inicial.
	static function PrepareFacebook($section = 'all', $params = Array())
	{
		$html 	= '';
		$fb 	= self::$data['facebook'];
			
		if($section == 'init' OR $section == 'all')
		{
			if(empty($params['status']))
				$params['status'] 	= 'true';
				
			if(empty($params['cookie']))
				$params['cookie'] 	= 'true';
				
			if(empty($params['xfbml']))
				$params['xfbml'] 	= 'true';
				
			if(empty($params['oauth']))
				$params['oauth'] 	= 'true';
				
				
			$html .= "<script>
			window.fbAsyncInit = function() 
			{
				FB.init({
					appId: '$fb[appId]',
					status: $params[status], 
					cookie: $params[cookie],
					xfbml: $params[xfbml],
					oauth: $params[oauth]
				});
			};</script>";
		}
		
		if($section == 'js' OR $section == 'all')
		{
			$html .= "<div id='fb-root'></div><script>(function(d){
				var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
				js = d.createElement('script'); js.id = id; js.async = true;
				js.src = '//connect.facebook.net/es_MX/all.js#xfbml=1&appId=$fb[appId]';
				d.getElementsByTagName('head')[0].appendChild(js);
			}(document));</script>";
		}
		
		return $html;
	}

	// Publicar una acción Open Graph en Facebook.
	// - $object: Objeto.
	// - $action: Acción.
	// - $url: Dirección web de la acción.
	static function PublishAction($object, $action, $url)
	{
		self::InitAPI('facebook');
		$fb = self::$fb;

		if($fb == null)
			return self::Error('social.instance.fail', __FUNCTION__, '%error.facebook%');

		$res = $fb->api('/me/$object:$action', 'POST', array(
			'beverage' => $url
		));

		return $res['id'];
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
			'info' => json_encode($info)
		), $data['id']);
			
		Core::theSession('service_info', $data['info']);
		Users::Login($user['id'], $cookie);
		
		return true;
	}

	// Elimina las sesiones sociales.
	static function Logout()
	{
		Core::delSession('steam_userId');
		Core::delSession('twitter_api');
		Core::delSession('google_api');
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
			
		$hash = Users::NewService($info['id'], $service, $info['username'], json_encode($info));
		
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
		if(!Core::isValid($href, 'url') AND $plugin !== 'activity')
			return false;

		if(empty($params['width']) OR !is_numeric($params['width']))
			$params['width'] = 450;

		if(empty($params['height']) OR !is_numeric($params['height']))
			$params['height'] = 300;

		if($plugin == 'like_button')
		{
			if(empty($params['action']))
				$params['action'] = 'like';

			$html = new Html('div');
			$html->Set('class', 'fb-like');
			$html->Set('data-href', $href);

			$html->Set('data-width', $params['width']);
			$html->Set('data-action', $params['action']);

			if($params['send'] == 'true')
				$html->Set('data-send', 'true');

			if($params['faces'] == 'true')
				$html->Set('data-show-faces', 'true');
		}

		if($plugin == 'send_button')
		{
			$html = new Html('div');
			$html->Set('class', 'fb-send');
			$html->Set('data-href', $href);
		}

		if($plugin == 'subscribe_button')
		{
			$html = new Html('div');
			$html->Set('class', 'fb-subscribe');
			$html->Set('data-href', $href);

			$html->Set('data-width', $params['width']);

			if($params['faces'] == 'true')
				$html->Set('data-show-faces', 'true');
		}

		if($plugin == 'comments')
		{
			if(empty($params['posts']) OR !is_numeric($params['posts']))
				$params['posts'] = 3;

			$html = new Html('div');
			$html->Set('class', 'fb-comments');
			$html->Set('data-href', $href);

			$html->Set('data-num-posts', $params['posts']);
			$html->Set('data-width', $params['width']);
		}

		if($plugin == 'activity')
		{
			$html = new Html('div');
			$html->Set('class', 'fb-activity');
			$html->Set('data-site', $href);

			$html->Set('data-width', $params['width']);
			$html->Set('data-height', $params['height']);

			if($params['header'] == 'true')
				$html->Set('data-header', 'true');

			if($params['recommendations'] == 'true')
				$html->Set('data-recommendations', 'true');

			if(!empty($params['border']))
				$html->Set('data-border-color', $params['border']);

			if(!empty($params['linktarget']))
				$html->Set('data-linktarget', $params['linktarget']);
		}

		if($plugin == 'like_box')
		{
			$html = new Html('div');
			$html->Set('class', 'fb-like-box');
			$html->Set('data-href', $href);

			$html->Set('data-width', $params['width']);
			$html->Set('data-height', $params['height']);

			if($params['faces'] == 'true')
				$html->Set('data-show-faces', 'true');

			if(!empty($params['border']))
				$html->Set('data-border-color', $params['border']);

			if($params['stream'] == 'true')
				$html->Set('data-stream', $params['stream']);

			if($params['header'] == 'true')
				$html->Set('data-header', $params['header']);
		}

		if($plugin == 'facepile')
		{
			if(empty($params['rows']) OR !is_numeric($params['rows']))
				$params['rows'] = 1;

			$html = new Html('div');
			$html->Set('class', 'fb-facepile');
			$html->Set('data-href', $href);

			$html->Set('data-width', $params['width']);
			$html->Set('data-max-rows', $params['rows']);
		}

		if(!empty($params['color']))
			$html->Set('data-colorscheme', $params['color']);

		if(!empty($params['font']))
			$html->Set('data-font', $params['font']);

		return $html->Build();
	}

	// Preparar un botón de Twitter.
	// - $type: Tipo de botón a preparar.
	// - $href: Dirección/Usuario/Hashtag para el botón.
	// - $params: Parametros de configuración.
	static function TwitterButton($type = 'share', $href = PATH_NOW, $params = array())
	{
		if(empty($params['lang']))
			$params['lang'] = LANG;

		if(empty($params['count']))
			$params['count'] = 'true';

		if($type == 'share')
		{
			$html = new Html('a');
			$html->Set('href', 'https://twitter.com/share');
			$html->Set('class', 'twitter-share-button');
			$html->Set('data-url', $href);
			$html->Set('text', 'Twittear');

			if(!empty($params['content']))
				$html->Set('data-text', $params['content']);

			if(!empty($params['via']))
				$html->Set('data-via', $params['via']);

			if(!empty($params['related']))
				$html->Set('data-related', $params['related']);

			if(!empty($params['hashtags']))
				$html->Set('data-hashtags', $params['hashtags']);
		}

		if($type == 'follow')
		{
			$html = new Html('a');
			$html->Set('href', 'https://twitter.com/' . $href);
			$html->Set('class', 'twitter-follow-button');
			$html->Set('text', 'Seguir a @' . $href);
		}

		if($type == 'tweet')
		{
			$html = new Html('a');
			$html->Set('href', 'https://twitter.com/intent/tweet?button_hashtag=' . $href . '&text=' . $params['content']);
			$html->Set('class', 'twitter-hashtag-button');
			$html->Set('text', 'Tweet #' . $href);

			if(!empty($params['related']))
				$html->Set('data-related', $params['related']);

			if(!empty($params['href']))
				$html->Set('data-url', $params['href']);
		}

		if($type == 'tweet_user')
		{
			$html = new Html('a');
			$html->Set('href', 'https://twitter.com/intent/tweet?screen_name=' . $href . '&text=' . $params['content']);
			$html->Set('class', 'twitter-mention-button');

			if(!empty($params['related']))
				$html->Set('data-related', $params['related']);
		}

		if(!empty($params['size']))
			$html->Set('data-size', $params['size']);

		$html->Set('data-count', $params['count']);
		$html->Set('data-lang', $params['lang']);

		return $html->Build();
	}
}
?>