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

class Twitter
{
	static $api = null;

	// Preparar instancia para Twitter.
	static function Prepare()
	{
		return '<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';
	}

	// Preparar un botón de Twitter.
	// - $type: Tipo de botón a preparar.
	// - $href: Dirección/Usuario/Hashtag para el botón.
	// - $params: Parametros de configuración.
	static function Plugin($type = 'share', $href = PATH_NOW, $params = array())
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

		Curl::Init($url);
		$data = Curl::Get();
		$data = json_decode($data, true);

		if(isset($data['error']) OR isset($data['errors']))
			return false;

		return $data;
	}

	// Preparar e implementar la API.
	private static function Init()
	{
		if(self::$api !== null)
			return true;

		require MODS . 'External' . DS . 'twitter' . DS . 'twitteroauth.php';
		global $R;

		$data = Social::$data['twitter'];
		$auth = _SESSION('twitter_token');

		if(empty($data['key']) OR empty($data['secret']))
			return Social::Error('social.instance', __FUNCTION__, '%error.twitter.data%');
		
		if($R['oauth_token'] == $auth['oauth_token'])
		{
			$api 	= new TwitterOAuth($data['key'], $data['secret'], $auth['oauth_token'], $auth['oauth_token_secret']);
			$auth 	= $api->getAccessToken($R['oauth_verifier']);
		}
			
		$api = new TwitterOAuth($data['key'], $data['secret'], $auth['oauth_token'], $auth['oauth_token_secret']);

		self::$api = $api;
		return true;
	}

	// Obteniendo y verificando el recurso de la API.
	// - $user (Bool): ¿Verificar si el usuario esta online?
	static function API($user = true)
	{
		self::Init();
		$api = self::$api;

		if($api == null)
			return Social::Error('social.instance.fail', __FUNCTION__, '%error.facebook%');

		if($user)
		{
			try
			{
				$verify = $api->get('account/verify_credentials');
			}
			catch(Exception $e)
			{
				Social::Error('social.instance.fail', __FUNCTION__, $e);
			}

			if($verify->error == 'Could not authenticate you.')
				self::LogIn();
		}

		return $api;
	}

	// Iniciar sesión y obtener permisos del usuario.
	// - $params (array): Parametros de inicio de sesión.
	// - $return (bool): ¿Obtener la dirección de inicio en vez de redireccionar?
	static function LogIn($return = false)
	{
		$api 		= self::API(false);
		$request 	= $api->getRequestToken(PATH_NOW);

		_SESSION('twitter_token', array(
			'oauth_token' 			=> $request['oauth_token'],
			'oauth_token_secret'	=> $request['oauth_token_secret']
		));

		if(empty($request['oauth_token']))
			Core::Redirect(PATH_NOW);

		if($api->http_code == 200 OR $api->http_code == 401)
		{
			$url = $api->getAuthorizeURL($request['oauth_token']);

			if($return)
				return $url;
			else
				Core::Redirect($url);
		}
	}

	// Obtener información básica del usuario.
	static function Get_Me()
	{	
		$api 	= self::API();
		$me 	= null;

		try
		{
			$me = $api->get('account/verify_credentials');
		}
		catch(FacebookApiException $e)
		{
			Social::Error('social.instance.fail', __FUNCTION__, $e);
		}

		return $me;
	}

	// Más información: https://dev.twitter.com/docs/api
	static function Get_Me_($type, $action = '', $params = array())
	{
		$api 	= self::API();
		$result = null;

		$request = $type;

		if(!empty($action))
			$request .= '/' . $action;

		try
		{
			$result = $api->get($request, $params);
		}
		catch(Exception $e)
		{
			var_dump($e);
			echo $e->getMessage();
		}

		return $result;
	}

	static function Get_Me_Id_($type, $action, $id, $params = array())
	{
		$api 	= self::API();
		$result = null;

		$request = $type;

		$before = array(
			'retweeted_by', 'retwe'
		);

		if($type == 'statuses')
		{

		}

		if(!empty($action))
			$request .= '/' . $action;

		try
		{
			$result = $api->get($request, $params);
		}
		catch(Exception $e)
		{
			var_dump($e);
			echo $e->getMessage();
		}

		return $result;
	}

	static function Search($query, $locale = '', $type = 'recent')
	{
		$api 	= self::API();
		$result = null;
	}
}
?>