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

class Twitter
{
	static $API = null;

	// Preparar instancia para Twitter.
	static function Prepare()
	{
		return '<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';
	}

	// Preparar un botón de Twitter.
	// - $type: Tipo de botón a preparar.
	// - $href: Dirección/Usuario/Hashtag para el botón.
	// - $params: Parametros de configuración.
	static function Plugin($type = 'share', $href = PATH_NOW, $params = [])
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

	// Obtener la información pública de un usuario.
	// - $id: Nombre de usuario o ID.
	static function GetInfo($id, $entities = 'true')
	{
		$url = 'https://api.twitter.com/1/users/lookup.json?screen_name=' . $id . '&include_entities=' . $entities;

		$curl = new Curl($url);
		$data = $curl->Get();
		$data = json_decode($data, true);

		if(isset($data['error']) OR isset($data['errors']))
			return false;

		return $data;
	}

	// Preparar e implementar la API.
	private static function Init()
	{
		if(self::$API !== null)
			return true;

		require APP_CTRLS . 'External' . DS . 'twitter' . DS . 'twitteroauth.php';
		global $R;

		$data = Social::$data['twitter'];
		$auth = _SESSION('twitter_token');

		if(empty($data['key']) OR empty($data['secret']))
			return Social::Error('social.instance', __FUNCTION__, '%error.twitter.data%');
		
		if($R['oauth_token'] == $auth['oauth_token'])
		{
			$API 	= new TwitterOAuth($data['key'], $data['secret'], $auth['oauth_token'], $auth['oauth_token_secret']);
			$auth 	= $API->getAccessToken($R['oauth_verifier']);
		}
			
		$API = new TwitterOAuth($data['key'], $data['secret'], $auth['oauth_token'], $auth['oauth_token_secret']);

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
			return Social::Error('social.instance.fail', __FUNCTION__, '%error.facebook%');

		if($user)
		{
			try
			{
				$verify = $API->get('account/verify_credentials');
			}
			catch(Exception $e)
			{
				Social::Error('social.instance.fail', __FUNCTION__, $e);
			}

			if($verify->error == 'Could not authenticate you.')
				self::LogIn();
		}

		return $API;
	}

	// Iniciar sesión y obtener permisos del usuario.
	// - $return (bool): ¿Obtener la dirección de inicio en vez de redireccionar?
	static function LogIn($return = false)
	{
		$API 		= self::API(false);
		$request 	= $API->getRequestToken(PATH_NOW);

		_SESSION('twitter_token', [
			'oauth_token' 			=> $request['oauth_token'],
			'oauth_token_secret'	=> $request['oauth_token_secret']
		]);

		if(empty($request['oauth_token']))
			Core::Redirect(PATH_NOW);

		if($API->http_code == 200 OR $API->http_code == 401)
		{
			$url = $API->getAuthorizeURL($request['oauth_token']);

			if($return)
				return $url;
			
			Core::Redirect($url);
		}

		return true;
	}

	// Salir de la sesión en Twitter.
	static function LogOut()
	{
		_DELSESSION('twitter_token');
	}

	// Obtener información básica del usuario.
	static function Get_Me()
	{	
		$API 	= self::API();
		$me 	= null;

		try
		{
			$me = $API->get('account/verify_credentials');
		}
		catch(FacebookApiException $e)
		{
			Social::Error('social.instance.fail', __FUNCTION__, $e);
		}

		return $me;
	}

	// Hacer una petición a la API.
	// - $type: Tipo de petición.
	// - $action: Acción.
	// - $params (array): Parametros a enviar.
	// Más información: https://dev.twitter.com/docs/api
	static function Get_Me_($type, $action = '', $params = [])
	{
		$API 	= self::API();
		$result = null;

		$request = $type;

		if(!empty($action))
			$request .= '/' . $action;

		try
		{
			$result = $API->get($request, $params);
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}

		return $result;
	}

	// Hacer una petición a la API que requiere una ID.
	// - $id (int): ID del objeto.
	// - $type: Tipo de petición.
	// - $action: Acción.
	// - $params (array): Parametros a enviar.
	// Más información: https://dev.twitter.com/docs/api
	static function Get_Id_($id, $type, $action, $params = [])
	{
		$API 	= self::API();

		$result 	= null;
		$method 	= strtoupper($method);
		$request 	= $type . '/' . $action . '/' . $id;

		try
		{
			$result = $API->get($request, $params);
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}

		return $result;
	}

	// Publicar una petición a la API que requiere una ID.
	// - $id (int): ID del objeto.
	// - $type: Tipo de petición.
	// - $action: Acción.
	// - $params (array): Parametros a enviar.
	// Más información: https://dev.twitter.com/docs/api
	static function Publish_Id_($id, $type, $action, $params = [])
	{
		$API 	= self::API();

		$result 	= null;
		$method 	= strtoupper($method);
		$request 	= $type . '/' . $action . '/' . $id;

		try
		{
			$result = $API->get($request, $params);
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}

		return $result;
	}

	// Publicar un nuevo estado (tweet)
	// - $status (string): Mensaje.
	// - $replyId (int): ID del tweet al que se responde.
	static function Publish_Status($status, $replyId = '')
	{
		$API 	= self::API();
		$result = null;

		try
		{
			$result = $API->post('statuses/update', [
				'status'				=> $status,
				'in_reply_to_status_id'	=> $replyId
			]);
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}

		return $result;
	}
}
?>