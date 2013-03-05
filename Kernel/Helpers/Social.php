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

## --------------------------------------------------
## Controlador Social
## --------------------------------------------------
## Contiene las funciones y herramientas
## necesarias para interactuar el sitio y la tabla
## site_config de su base de datos.
## --------------------------------------------------	
	
class Social
{
	static $data 		= array();
	static $reg_data 	= array();
	
	static $PATH_NOW 	= '';

	static $go = null;
	static $pl = null;

	static $SERVICES = array(
		'facebook', 'twitter', 'google', 'steam'
	);
	
	// Lanzar error.
	// - $code: Código del error.
	// - $function: Función causante.
	// - $message: Mensaje del error.
	static function Error($code, $function, $message = '', $file = '')
	{
		Lang::SetSection('mod.social');

		Bit::Status($msg, __FILE__, array('function' => $function, 'file' => $file));
		Bit::LaunchError($code);
		
		return false;
	}

	// Obtener el recurso de Facebook.
	static function GetFb()
	{
		Fb::Init();
		return Fb::$API;
	}

	// Obtener el recurso de Twitter.
	static function GetTwitter()
	{
		Twitter::Init();
		return Twitter::$API;
	}

	// Obtener el recurso de Steam.
	static function GetSteam()
	{
		Steam::Init();
		return Steam::$API;
	}

	// Obtener el recurso de Steam.
	static function GetPlus()
	{
		Google::Init();
		return Google::$API;
	}
	
	// Preparar los datos.
	// - $values (Array): Datos de preparación.
	static function Prepare($values)
	{
		self::$data 	= $values;
		self::$PATH_NOW = preg_replace('/(\?|&)code=(.*)/is', '', PATH_NOW);
		
		if(isset($values['facebook']))
		{
			Tpl::Meta('fb:app_id', $values['facebook']['appId'], 'property');

			if(!empty($values['facebook']['admins']))
				Tpl::Meta('fb:admins', $values['facebook']['admins'], 'property');
		}
	}

	// Preparar API.
	// - $service (facebook, twitter): Servicio.
	static function Init($service = 'facebook')
	{
		if(!in_array($service, self::$SERVICES))
			return self::Error('social.service', __FUNCTION__);

		if($service == 'facebook')
			Fb::Init();

		if($service == 'twitter')
			Twitter::Init();

		if($service == 'google')
			Google::Init();

		if($service == 'steam')
			Steam::Init();
	}
	
	// Preparar la obtención de datos de un usuario con un servicio.
	// - $service (facebook, twitter): Servicio.
	// - $filter (Bool): ¿Filtrar información?
	// - $scope: Permisos de la aplicación.
	static function Get($service = 'facebook', $filter = true, $scope = '')
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
			$me = Google::Get_Me();

		if($service == 'steam')
		{
			$me 			= Steam::Get_Me();
			$me['games']	= Steam::Get_Me_Games();
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
	
	// Iniciar sesión o registrarse con el servicio.
	// - $service (facebook, twitter): Servicio.
	// - $cookie (Bool): ¿Auto conectarse con Cookies?
	static function LoginOrNew($service = 'facebook', $cookie = true)
	{
		if(!in_array($service, self::$SERVICES))
			return self::Error('social.service', __FUNCTION__);
			
		$info 	= self::Get($service);			
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
			$info = self::Get($service);
			
		$data = Users::Service($info['id'], $service);
		$user = Users::UserService($data['service_hash']);
		
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
		_DELSESSION('steam_userId');
		_DELSESSION('twitter_token');
		_DELSESSION('google_token');
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
			$info = self::Get($service);
			
		$hash = Users::NewService($info['id'], $service, $info['username'], _f(json_encode($info), false));
		
		self::$reg_data['service_hash'] = $hash;
			
		$pass = 'social';			
		$userId = Users::NewUser($info['username'], $pass, $info['name'], $info['email'], $info['birthday'], $info['profile_image_url'], false, self::$reg_data);
		
		self::Login($service, $info, $cookie);			
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
}
?>