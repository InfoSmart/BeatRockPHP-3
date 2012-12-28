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

class Google
{
	static $API 	= null;
	static $PLUS 	= null;

	// Preparar instancia para Google+.
	static function Prepare($lang = LANG)
	{
		return '<script src="https://apis.google.com/js/plusone.js">{lang: "' . $lang . '"}</script>';
	}

	// Preparar un plugin de Google.
	// - $plugin: El plugin ha preparar.
	// - $href: Dirección/Dominio para el plugin.
	// - $params: Parametros de configuración.
	// 		- size: small, medium, tall
	// 		- annotation: inline, none
	static function Plugin($type = '+button', $href = PATH_NOW, $params = [])
	{
		if($type == '+button')
		{
			$html = new Html();
			$html->Set('class', 'g-plusone');
			$html->Set('data-href', $href);

			if(!empty($params['size']))
				$html->Set('data-size', $params['size']);

			if(!empty($params['annotation']))
				$html->Set('data-annotation', $params['annotation']);
		}

		return $html->Build();
	}

	// Preparar e implementar la API.
	static function Init()
	{
		if(self::$API !== null)
			return true;

		require APP_CTRLS . 'External' . DS . 'google' . DS . 'apiClient.php';
		require APP_CTRLS . 'External' . DS . 'google' . DS . 'contrib' . DS . 'apiPlusService.php';

		$data = Social::$data['google'];

		if(empty($data['clientId']) OR empty($data['key']) OR empty($data['secret']))
			return Social::Error('social.instance', __FUNCTION__, '%error.google.data%');

		$API = new apiClient();
		$API->setApplicationName(SITE_NAME);
		$API->setClientId($data['clientId']);
		$API->setClientSecret($data['secret']);
		$API->setDeveloperKey($data['key']);
		$API->setRedirectUri(Social::$PATH_NOW);
		$API->setScopes(['https://www.googleapis.com/auth/plus.me']);

		$PLUS = new apiPlusService($API);

		self::$API 	= $API;
		self::$PLUS = $PLUS;

		return true;
	}

	// Obteniendo y verificando el recurso de la API.
	// - $user (Bool): ¿Verificar si el usuario esta online?
	static function API($user = true)
	{
		self::Init();

		$API 	= self::$API;
		$PLUS 	= self::$PLUS;

		if($API == null)
			return Social::Error('social.instance.fail', __FUNCTION__, '%error.google%');

		if($user)
		{
			try
			{
				$verify = $PLUS->people->get('me');
			}
			catch(Exception $e)
			{
				self::LogIn();
			}				
		}

		return $API;
	}

	// Iniciar sesión y obtener permisos del usuario.
	// - $return (bool): ¿Obtener la dirección de inicio en vez de redireccionar?
	static function LogIn($return = false)
	{
		global $R;

		$API 	= self::API(false);
		$auth 	= _SESSION('google_token');

		if(isset($auth['access_token']))
			$API->setAccessToken($auth['access_token']);

		else if(isset($R['code']))
			$API->authenticate();

		$request = $API->getAccessToken();

		if($request)
		{
			_SESSION('google_token', [
				'access_token'	=> $request
			]);

			$API->setAccessToken($request);

			if(isset($R['code']))
				Core::Redirect(Social::$PATH_NOW);
		}
		else
		{
			$url = $API->createAuthUrl();

			if($return)
				return $url;

			Core::Redirect($url);
		}

		return true;
	}

	// Salir de la sesión en Google+.
	static function LogOut()
	{
		_DELSESSION('google_token');
	}

	// Obtener información básica del usuario.
	static function Get_Me()
	{
		$API 	= self::API();
		$PLUS 	= self::$PLUS;

		$me 	= null;

		try
		{
			$me = $PLUS->people->get('me');
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}

		return $me;
	}
}
?>