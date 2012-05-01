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
if(!defined("BEATROCK"))
	exit;	
	
class Social
{
	public static $data = Array();
	public static $reg_data = Array();
	
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
		
		if(isset($values['facebook']))
			Tpl::addMeta("fb:app_id", $values['facebook']['appId'], "property");
	}
	
	// Función - Preparar la obtención de datos de un usuario con un servicio.
	// - $service (facebook, twitter): Servicio.
	// - $filter (Bool): ¿Filtrar información?
	public static function Init($service = "facebook", $filter = true)
	{
		// Servicio inválido.
		if($service !== "facebook" AND $service !== "twitter" AND $service !== "google")
			return self::Error("12s", __FUNCTION__);
			
		// Extraer todas las variables.
		extract($GLOBALS);
		
		// Usar el servicio de Facebook.
		if($service == "facebook")
		{
			// Preparar instancia de conexión.
			require_once(MODS . "External" . DS . "facebook" . DS . "facebook.php");
			$data = self::$data['facebook'];
			
			// No se han escrito los datos necesarios.
			if(empty($data['appId']) OR empty($data['secret']))
				return self::Error("13s", __FUNCTION__, "Los datos para la conexión a Facebook no son correctos.");
			
			// Preparar configuración de conexión.
			$fb = new Facebook(Array(
				"appId" => $data['appId'],
				"secret" => $data['secret']
			));
			
			// No se pudo realizar una conexión con Facebook.
			if($fb == false)
				return false;
			
			// Obteniendo la ID del usuario.
			$user = $fb->getUser();
			$me = null;
			
			// Se ha obtenido la ID correctamente.
			// si no es así, redireccionar a la Url de Inicio de sesión.
			if($user)
			{
				// Tratando de obtener la información del usuario.
				try
				{ $me = $fb->api("/me"); }
				catch(FacebookApiException $e) 
				{ 
					self::Error("11s", __FUNCTION__, $e);
					$fb = false;
				}
			}
			else
				Core::Redirect($fb->getLoginUrl());
		}
		
		// Usar el servicio de Twitter.
		if($service == "twitter")
		{
			// Preparar instancia de conexión.
			require_once(MODS . "External" . DS . "twitter" . DS . "twitteroauth.php");
			
			// Definiendo información de acceso actual.
			$data = self::$data['twitter'];
			$auth = Core::theSession("twitter_api");
			
			// No se han escrito los datos necesarios.
			if(empty($data['key']) OR empty($data['secret']))
				return self::Error("13s", __FUNCTION__, "Los datos para la conexión a Twitter no son correctos.");
			
			// Accediendo con Token válido.
			if($R['oauth_token'] == $auth['oauth_token'])
			{
				$tw = new TwitterOAuth($data['key'], $data['secret'], $auth['oauth_token'], $auth['oauth_token_secret']);
				$auth = $tw->getAccessToken($R['oauth_verifier']);
			}
			
			// Obteniendo instancia de conexión con OAuth.
			$tw = new TwitterOAuth($data['key'], $data['secret'], $auth['oauth_token'], $auth['oauth_token_secret']);
			
			// Tratando de obtener la información del usuario.
			try
			{ $me = $tw->get('account/verify_credentials'); }
			catch(Exception $e)
			{ self::Error("11s", __FUNCTION__, $e); }
			
			// La información de acceso no es válida.
			if($me->error == "Could not authenticate you.")
			{
				// Obteniendo información de acceso temporal.
				$req = $tw->getRequestToken(PATH_NOW);
				
				// Guardando información de acceso.
				Core::theSession("twitter_api", Array(
					"oauth_token" => $req['oauth_token'],
					"oauth_token_secret" => $req['oauth_token_secret']
				));
				
				// Hubo un error al obtener el acceso, intentarlo nuevamente.
				if(empty($req['oauth_token']))
				{
					header("Location: " . PATH_NOW);
					exit;
				}
				
				// Redireccionando a la Url de inicio de sesión.
				if($tw->http_code == 200 OR $tw->http_code == 401)
					Core::Redirect($tw->getAuthorizeURL($req['oauth_token']));
			}
		}
		
		// User el servicio de Google.
		if($service == "google")
		{
			// Preparar instancia de conexión.
			require_once(MODS . "External" . DS . "google" . DS . "apiClient.php");
			require_once(MODS . "External" . DS . "google" . DS . "contrib" . DS . "apiPlusService.php");
			
			// Definiendo información de acceso actual.
			$data = self::$data['google'];
			$auth = Core::theSession("google_api");
			
			// No se han escrito los datos necesarios.
			if(empty($data['clientId']) OR empty($data['key']) OR empty($data['secret']))
				return self::Error("13s", __FUNCTION__, "Los datos para la conexión a Google no son correctos.");
			
			// Ajustando dirección de Regreso.
			$PATH_NOW = preg_replace('/(?|&)code=(.*)/is', '', PATH_NOW);
			
			// Iniciando y preparando la conexión.
			$go = new apiClient();
			$go->setApplicationName(SITE_NAME);
			$go->setClientId($data['clientId']);
			$go->setClientSecret($data['secret']);
			$go->setDeveloperKey($data['key']);
			$go->setRedirectUri($PATH_NOW);
			$go->setScopes(Array('https://www.googleapis.com/auth/plus.me'));
			
			// Iniciando la instancia para el servicio de Google+
			$pl = new apiPlusService($go);
			
			if(!empty($R['code']))
			{
				$go->authenticate();
				
				Core::theSession("google_api", Array(
					"access_token" => $go->getAccessToken()
				));
				
				header("Location: $PATH_NOW");
				exit;
			}
			
			if(!empty($auth['access_token']))
				$go->setAccessToken($auth['access_token']);
				
			if($go->getAccessToken())
			{
				// Tratando de obtener la información del usuario.
				try
				{ $me = $pl->people->get('me'); }
				catch(Exception $e) { self::Error("11s", __FUNCTION__, $e); }
				
				Core::theSession("google_api", Array(
					"access_token" => $go->getAccessToken()
				));
			}
			else
				Core::Redirect($go->createAuthUrl());
		}
		
		// La información del usuario es un Objeto, convertirla a matriz (Array)
		if(is_object($me))
			$me = get_object_vars($me);
		
		// La información es una matriz y queremos filtrar los datos.
		if(is_array($me) AND $filter)
			$me = CleanText($me);
			
		// La ID del usuario no es válida.
		if(!is_numeric($me['id']))
			$me['id'] = $me['id_str'];
		
		// No hay nombre de usuario disponible.
		if(empty($me['username']))
			$me['username'] = $me['screen_name'];
		
		// No hay nombre de usuario disponible.
		if(empty($me['username']))
			$me['username'] = $me['displayName'];
			
		// No hay nombre disponible.
		if(empty($me['name']) OR is_array($me['name']))
			$me['name'] = $me['username'];
			
		// No hay imagen de perfil disponible.
		if(empty($me['profile_image_url']))
			$me['profile_image_url'] = $me['image']['url'];
			
		// No hay imagen de perfil disponible.
		if(empty($me['profile_image_url']))
			$me['profile_image_url'] = "http://graph.facebook.com/$me[id]/picture?type=large";
		
		// Devolver información del usuario.
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
	
	// Función - Iniciar sesión o registrar con el servicio.
	// - $service (facebook, twitter): Servicio.
	// - $cookie (Bool): ¿Auto conectarse con Cookies?
	public static function LoginOrNew($service = "facebook", $cookie = true)
	{
		// Servicio inválido.
		if($service !== "facebook" AND $service !== "twitter" AND $service !== "google")
			return self::Error("12s", __FUNCTION__);
			
		// Obteniendo la información del usuario.
		$info = self::Init($service);			
		// Verificando si el usuario ya existe.
		$verify = Users::ServiceExist($info['id'], $service);
		
		// El usuario no existe, registrarlo.
		// De otra forma, iniciar sesion.
		if(!$verify)
			return self::NewUser($service, $info, $cookie);
		else
			return self::Login($service, $info, $cookie);
	}
	
	// Función privada - Iniciar sesion.
	// - $service (facebook, twitter): Servicio.
	// - $info: Información del usuario.
	// - $cookie (Bool): ¿Auto conectarse con Cookies?
	private static function Login($service, $info = "", $cookie = true)
	{
		// Servicio inválido.
		if($service !== "facebook" AND $service !== "twitter" AND $service !== "google")
			return self::Error("12s", __FUNCTION__);
			
		// Si no se definio la información, obtenerla.
		if(empty($info))
			$info = self::Init($service);
			
		// Obteniendo información del servicio.
		$data = Users::Service($info['id'], $service);
		$user = Users::UserService($data['user_hash'], $service);
		
		// El servicio no existe.
		if($data == false OR $user == false)
			return "NOT_EXIST";
			
		// Actualizar servicio con la información más reciente del usuario.
		Users::UpdateService(Array(
			"info" => json_encode($info)
		), $data['id']);
			
		// Guardar la información e iniciar sesión.
		Core::theSession("service_info", $data['info']);
		Users::Login($user['id'], $cookie);
		
		return true;
	}
	
	// Función privada - Agregar un nuevo usuario.
	// - $service (facebook, twitter): Servicio.
	// - $info: Información del usuario.
	// - $cookie (Bool): ¿Auto conectarse con Cookies?
	private static function NewUser($service, $info = "", $cookie = true)
	{
		// Servicio inválido.
		if($service !== "facebook" AND $service !== "twitter" AND $service !== "google")
			return self::Error("12s", __FUNCTION__);
		
		// Si no se definio la información, obtenerla.		
		if(empty($info))
			$info = self::Init($service);
			
		// Agregando nuevo servicio y obteniendo hash.
		$hash = Users::NewService($info['id'], $service, $info['username'], json_encode($info));
		
		self::$reg_data['user_hash'] = $hash;
		self::$reg_data['service'] = $service;
			
		// Contraseña predeterminada.
		$pass = "social";
			
		// Insertar usuario en la base de datos.
		$userId = Users::NewUser($info['username'], $pass, $info['name'], $info['email'], $info['birthday'], $info['profile_image_url'], false, self::$reg_data);
		
		// Auto conectarse.
		if($cookie)
			self::Login($service, $info);
			
		return $userId;
	}
}
?>