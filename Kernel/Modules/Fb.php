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

class Fb
{
	static $api 	= null;
	static $scope 	= array();

	// Preparar instancia para Facebook.
	// - $sec (init, js, all): Sección a implementar.
	// - $params (Array): Parametros de configuración inicial.
	static function Prepare($section = 'all', $params = array())
	{
		$html 	= '';
		$fb 	= Social::$data['facebook'];
			
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
			$html .= "<div id='fb-root'></div><script>(function(d, s, id) {
  				var js, fjs = d.getElementsByTagName(s)[0];
  				if (d.getElementById(id)) return;
  				js = d.createElement(s); js.id = id;
  				js.src = '//connect.facebook.net/es_LA/all.js#xfbml=1&appId=$fb[appId]';
  				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>";
		}

		return $html;
	}

	// Preparar un plugin de Facebook.
	// - $plugin: El plugin ha preparar.
	// - $href: Dirección/Dominio para el plugin.
	// - $params: Parametros de configuración.
	static function Plugin($plugin = 'like_button', $href = PATH_NOW, $params = array())
	{
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

	// Obtener la información pública de un usuario.
	// - $id: Nombre de usuario o ID.
	static function GetInfo($id)
	{
		$url = 'http://graph.facebook.com/' . $id;

		Curl::Init($url);
		$data = Curl::Get();
		$data = json_decode($data, true);

		if(isset($data['error']) OR isset($data['errors']))
			return false;

		return $data;
	}

	// Preparar e implementar la API.
	static function Init()
	{
		if(self::$api !== null)
			return true;

		require MODS . 'External' . DS . 'facebook' . DS . 'facebook.php';
		$data = Social::$data['facebook'];
			
		if(empty($data['appId']) OR empty($data['secret']))
			return Social::Error('social.instance', __FUNCTION__, '%error.facebook.data%');
			
		$api = new Facebook(array(
			'appId'		=> $data['appId'],
			'secret'	=> $data['secret']
		));
			
		if(!$api)
			return false;

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
			$verify = $api->getUser();

			if(!$verify)
				self::LogIn();
		}

		return $api;
	}

	// Establecer los permisos necesarios.
	// - $scope (array): Permisos.
	static function SetScope($scope)
	{
		if(!is_array($scope))
			return;

		self::$scope = $scope;
	}

	// Iniciar sesión y obtener permisos del usuario.
	// - $params (array): Parametros de inicio de sesión.
	// - $return (bool): ¿Obtener la dirección de inicio en vez de redireccionar?
	static function LogIn($params = array(), $return = false)
	{
		$api = self::API(false);

		if(!empty(self::$scope))
		{
			$count 	= count(self::$scope);
			$i 		= 0;

			foreach(self::$scope as $scope)
			{
				++$i;
				$params['scope'] .= $scope;

				if($count !== $i)
					$params['scope'] .= ',';
			}
		}

		$url = $api->getLoginUrl($params);

		if($return)
			return $url;
		else
			Core::Redirect($url);
	}

	// Salir de la sesión en Facebook.
	// - $return (bool): ¿Obtener la dirección de salida en vez de redireccionar?
	static function LogOut($return = false)
	{
		$api = self::API(false);
		$url = $api->getLogoutUrl();

		if($return)
			return $url;
		else
			Core::Redirect($url);
	}

	// Obtener información básica del usuario.
	static function Get_Me()
	{
		$api 	= self::API();
		$me 	= null;

		try
		{
			$me = $api->api('/me', 'GET');
		}
		catch(FacebookApiException $e)
		{
			Social::Error('social.instance.fail', __FUNCTION__, $e);
		}

		return $me;
	}

	// Obtener cierta información del usuario.
	// - $type: Información a obtener.
	// - $limit (int): Limite de valores a obtener.
	static function Get_Me_($type, $limit = 0)
	{
		/*
			apprequests, feed, friends, mutualfriends,
			permissions, picture, posts
		*/
		
		$scope = array(
			'accounts'			=> 'manage_pages',
			'achievements'		=> 'user_games_activity',
			'activities'		=> 'user_activities',
			'albums'			=> 'user_photos',
			'books'				=> 'user_likes',
			'checkins'			=> 'user_checkins',
			'events'			=> 'user_events',
			'family'			=> 'user_relationships',
			'feed'				=> 'read_stream',
			'friendlists' 		=> 'manage_friendlists',
			'friendrequests' 	=> 'read_requests',
			'games'				=> 'user_likes',
			'groups'			=> 'user_groups',
			'home'				=> 'read_stream',
			'inbox'				=> 'read_mailbox',
			'interests'			=> 'user_interests',
			'likes'				=> 'user_likes',
			'links'				=> 'read_stream',
			'locations'			=> 'user_checkins',
			'movies'			=> 'user_likes',
			'music'				=> 'user_likes',
			'notifications'		=> 'manage_notifications',
			'outbox'			=> 'read_mailbox',
			'pokes'				=> 'read_mailbox',
			'posts'				=> 'read_stream',
			'questions'			=> 'user_questions',
			'statuses'			=> 'read_stream',
			'scores'			=> 'user_games_activity',
			'subscribedto'		=> 'user_subscriptions',
			'subscribers'		=> 'user_subscriptions',
			'tagged'			=> 'read_stream',
			'television'		=> 'user_likes',
			'updates'			=> 'read_mailbox',
			'videos'			=> 'user_videos'
		);

		$api 	= self::API();
		$result = null;

		$request = '/me/' . $type;

		if($limit > 0)
			$request .= '?limit=' . $limit;

		try
		{
			$result = $api->api($request, 'GET');
		}
		catch(FacebookApiException $e)
		{
			if(
				Contains($e->getMessage(), 'The user hasn\'t authorized the application to perform this action') OR 
				Contains($e->getMessage(), 'permission')
				)
			{
				self::$scope[] = $scope[$type];
				self::LogIn();
			}

			if(Contains($e->getMessage(), 'token'))
				self::LogIn();
		}

		return $result;
	}

	// Buscar una página/amigo/persona
	// - $type (post, people, page, event, group, place): Tipo de busqueda.
	// - $query: Termino a buscar.
	static function Search($type, $query)
	{
		$api = self::API();

		try
		{
			$result = $api->api('/search?q=' . $query . '&type=' . $type);
		}
		catch(FacebookApiException $e)
		{
			if(Contains($e->getMessage(), 'token'))
				self::LogIn();

			return $e->getMessage();
		}

		return $result;
	}

	// TODO: Crear listas de amigos
	// TODO: Subir videos

	// Publicar una acción.
	// - $object: Objeto/Aplicación.
	// - $action: Acción a publicar.
	// - $url: Dirección web.
	static function Publish_Action($object, $action, $url)
	{
		$api = self::API();

		$result = $api->api("/me/$object:$action", 'POST', array(
			'beverage' => $url
		));

		return $result;
	}

	// Crear un nuevo albúm.
	// - $name: Nombre del nuevo albúm.
	// - $message: Mensaje del nuevo albúm.
	static function Publish_Album($name, $message = '')
	{
		// TODO: Privacidad.

		$api = self::API();

		try
		{
			$result = $api->api('/me/albums', 'POST', array(
				'name'		=> $name,
				'message'	=> utf8_encode($message)
			));
		}
		catch(FacebookApiException $e)
		{
			if(
				Contains($e->getMessage(), 'The user hasn\'t authorized the application to perform this action') OR 
				Contains($e->getMessage(), 'permission')
				)
			{
				self::$scope[] = 'publish_stream';
				self::LogIn();
			}

			if(Contains($e->getMessage(), 'token'))
				self::LogIn();

			return $e->getMessage();
		}

		return $result;
	}

	// Publicar una nueva visita.
	// - $place (facebook page id): ID del lugar que se esta visitando.
	// - $coords: Coordenadas del usuario.
	// - $message: Mensaje opcional.
	// - $picture: Url de la imagen opcional.
	// - $link: Dirección web opcional.
	// - $tags (array): ID de Amigos etiquetados.
	static function Publish_CheckIn($place, $coords, $message = '', $picture = '', $link = '', $tags = array())
	{
		$api = self::API();

		$data = array();
		$data['place']			= $place;
		$data['coordinates']	= $coords;

		if(!empty($message))
			$data['message']	= utf8_encode($message);

		if(!empty($picture))
			$data['picture']	= $picture;

		if(!empty($link))
			$data['link']		= $link;

		if(!empty($tags))
		{
			$count 	= count($tags);
			$i 		= 0;

			foreach($tags as $tag)
			{
				++$i;
				$data['tags'] .= $tag;

				if($count !== $i)
					$data['tags'] .= ',';
			}
		}

		try
		{
			$result = $api->api('/me/checkins', 'POST', $data);
		}
		catch(FacebookApiException $e)
		{
			if(
				Contains($e->getMessage(), 'The user hasn\'t authorized the application to perform this action') OR 
				Contains($e->getMessage(), 'permission')
				)
			{
				self::$scope[] = 'publish_checkins';
				self::LogIn();
			}

			if(Contains($e->getMessage(), 'token'))
				self::LogIn();

			return $e->getMessage();
		}

		return $result;
	}

	// Crear un nuevo evento.
	// - $name: Nombre del evento.
	// - $start_time (unix, utc): Tiempo de inicio.
	// - $end_time (unix, utc): Tiempo para terminar.
	// - $description: Descripción del evento.
	// - $location: Localización del evento.
	// - $privacy (OPEN, FRIENDS, SECRET): Privacidad del evento.
	static function Publish_Event($name, $start_time, $end_time = '', $description = '', $location = '', $privacy = 'OPEN')
	{
		$api = self::API();

		if(is_numeric($start_time))
			$start_time = date('c', $start_time);

		if(is_numeric($end_time))
			$end_time 	= date('c', $end_time);

		$data = array();
		$data['name']			= utf8_encode($name);
		$data['start_time']		= $start_time;

		if(!empty($end_time))
			$data['end_time']		= $end_time;

		if(!empty($description))
			$data['description']	= utf8_encode($description);

		if(!empty($location))
			$data['location']		= utf8_encode($location);

		if(!empty($privacy))
			$data['privacy']		= $privacy;

		try
		{
			$result = $api->api('/me/events', 'POST', $data);
		}
		catch(FacebookApiException $e)
		{
			if(
				Contains($e->getMessage(), 'The user hasn\'t authorized the application to perform this action') OR 
				Contains($e->getMessage(), 'permission')
				)
			{
				self::$scope[] = 'create_event';
				self::LogIn();
			}

			if(Contains($e->getMessage(), 'token'))
				self::LogIn();

			return $e->getMessage();
		}

		return $result;
	}

	// Publicar un link.
	// - $link: Dirección web.
	// - $message: Mensaje opcional.
	static function Publish_Link($link, $message = '')
	{
		$api = self::API();

		try
		{
			$result = $api->api('/me/links', 'POST', array(
				'link'		=> $link,
				'message'	=> utf8_encode($message)
			));
		}
		catch(FacebookApiException $e)
		{
			if(
				Contains($e->getMessage(), 'The user hasn\'t authorized the application to perform this action') OR 
				Contains($e->getMessage(), 'permission')
				)
			{
				self::$scope[] = 'publish_stream';
				self::LogIn();
			}

			if(Contains($e->getMessage(), 'token'))
				self::LogIn();

			return $e->getMessage();
		}

		return $result;
	}

	// Publicar una nota.
	// - $subject: Asunto de la nota.
	// - $message: Mensaje de la nota.
	static function Publish_Note($subject, $message)
	{
		$api = self::API();

		try
		{
			$result = $api->api('/me/notes', 'POST', array(
				'subject'	=> utf8_encode($subject),
				'message'	=> utf8_encode($message)
			));
		}
		catch(FacebookApiException $e)
		{
			if(
				Contains($e->getMessage(), 'The user hasn\'t authorized the application to perform this action') OR 
				Contains($e->getMessage(), 'permission')
				)
			{
				self::$scope[] = 'publish_stream';
				self::LogIn();
			}

			if(Contains($e->getMessage(), 'token'))
				self::LogIn();

			return $e->getMessage();
		}

		return $result;
	}

	// Publicar una foto.
	// - $file (direccion fisica, direccion web, bytes): Archivo de la foto.
	// - $message: Mensaje de la foto.
	// - $place (facebook page id): Lugar donde se tomo la foto.
	// - $no_story (string bool): ¿No aparecer en las noticias?
	static function Publish_Photo($file, $message = '', $place = '', $no_story = 'false')
	{
		$api = self::API();
		$api->setFileUploadSupport(true);

		if(Core::isValid($file, 'url'))
			$file = Io::Read($file);

		if(!file_exists($file))
			$file = Io::SaveTemporal($file);

		try
		{
			$result = $api->api('/me/photos', 'POST', array(
				'source'	=> '@' . $file,
				'message'	=> utf8_encode($message),
				'place'		=> $place,
				'no_story'	=> $no_story
			));
		}
		catch(FacebookApiException $e)
		{
			if(
				Contains($e->getMessage(), 'The user hasn\'t authorized the application to perform this action') OR 
				Contains($e->getMessage(), 'permission')
				)
			{
				self::$scope[] = 'publish_stream';
				self::LogIn();
			}

			if(Contains($e->getMessage(), 'token'))
				self::LogIn();

			return $e->getMessage();
		}


		return $result;
	}

	// Publicar un nuevo estado.
	// - $message: Mensaje.
	// - $tags (array): ID de Amigos etiquetados.
	// - $place (facebook page id): Lugar de la publicación.
	static function Publish_Post($message, $tags = array(), $place = '')
	{
		$api = self::API();

		$data = array();
		$data['message'] = utf8_encode($message);

		if(!empty($tags))
		{
			$count 	= count($tags);
			$i 		= 0;

			foreach($tags as $tag)
			{
				++$i;
				$data['tags'] .= $tag;

				if($count !== $i)
					$data['tags'] .= ',';
			}
		}

		if(!empty($place))
			$data['place'] = $place;

		try
		{
			$result = $api->api('/me/feed', 'POST', $data);
		}
		catch(FacebookApiException $e)
		{
			if(
				Contains($e->getMessage(), 'The user hasn\'t authorized the application to perform this action') OR 
				Contains($e->getMessage(), 'permission')
				)
			{
				self::$scope[] = 'publish_stream';
				self::LogIn();
			}

			if(Contains($e->getMessage(), 'token'))
				self::LogIn();
		}

		return $result;
	}

	// Publicar un nuevo estado con una web.
	// - $message: Mensaje.
	// - $link: Dirección web.
	// - $name: Nombre de la web.
	// - $caption: 
	// - $description: Descripción de la web.
	// - $picture: Imagen de la web.
	// - $tags (array): ID de Amigos etiquetados.
	// - $place (facebook page id): Lugar de la publicación.
	static function Publish_Post_Link($message, $link, $name = '', $caption = '', $description = '', $picture = '', $tags = array(), $place = '')
	{
		$api = self::API();

		$data = array();
		$data['message'] 	= utf8_encode($message);
		$data['link']		= $link;

		if(!empty($name))
			$data['name'] 			= $name;

		if(!empty($caption))
			$data['caption'] 		= $caption;

		if(!empty($description))
			$data['description'] 	= $description;

		if(!empty($picture))
			$data['picture'] 		= $picture;

		if(!empty($tags))
		{
			$count 	= count($tags);
			$i 		= 0;

			foreach($tags as $tag)
			{
				++$i;
				$data['tags'] .= $tag;

				if($count !== $i)
					$data['tags'] .= ',';
			}
		}

		if(!empty($place))
			$data['place'] = $place;

		try
		{
			$result = $api->api('/me/feed', 'POST', $data);
		}
		catch(FacebookApiException $e)
		{
			if(
				Contains($e->getMessage(), 'The user hasn\'t authorized the application to perform this action') OR 
				Contains($e->getMessage(), 'permission')
				)
			{
				self::$scope[] = 'publish_stream';
				self::LogIn();
			}

			if(Contains($e->getMessage(), 'token'))
				self::LogIn();
		}

		return $result;
	}

	// Publicar una nueva pregunta.
	// - $question: Pregunta.
	// - $options (array): Respuestas.
	// - $allow_new (string bool): ¿Permitir nuevas respuestas?
	static function Publish_Question($question, $options = array(), $allow_new = 'false')
	{
		$api = self::API();

		foreach($options as $key => $value)
			$options[$key] = utf8_encode($value);

		try
		{
			$result = $api->api('/me/questions', 'POST', array(
				'question'			=> utf8_encode($question),
				'options'			=> json_encode($options),
				'allow_new_options'	=> $allow_new
			));
		}
		catch(FacebookApiException $e)
		{
			if(
				Contains($e->getMessage(), 'The user hasn\'t authorized the application to perform this action') OR 
				Contains($e->getMessage(), 'permission')
				)
			{
				self::$scope[] = 'publish_stream';
				self::LogIn();
			}

			if(Contains($e->getMessage(), 'token'))
				self::LogIn();

			return $e->getMessage();
		}

		return $result;
	}
}
?>