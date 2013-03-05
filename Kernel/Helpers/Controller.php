<?
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart © 2013 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

# Acción ilegal
if( !defined('BEATROCK') )
	exit;

class Controller extends BaseStatic
{
	static $controllers = array();

	static function Load($controller = 'index', $action = 'index', $parameters = null)
	{
		$request = explode('/', QUERY);

		if( !empty($request[1]) )
			$controller = $request[1];

		if( !empty($request[2]) )
			$action 	= $request[2];

		if( !empty($request[3]) )
			$parameters = $request[3];

		self::LoadController($controller);
		$controller = 'Ctrl_' . $controller;

		call_user_func(array($controller, $action), $parameters);
		call_user_func(array($controller, 'Load'));
	}

	static function LoadController($name)
	{
		global $config;

		$CONTROLLER = $name . '.php';
		$found 		= false;

		if( empty($config['beatrock']['controllers']) )
		{
			$config['beatrock']['controllers'] = array(
				'{APP}Controllers'
			);
		}

		foreach( $config['beatrock']['controllers'] as $path )
		{
			$path = str_ireplace('/', DS, $path);
			$path = Keys($path);

			if( file_exists($path . DS . $CONTROLLER) )
			{
				$found = true;
				require $path . DS . $CONTROLLER;
				break;
			}
		}

		# No encontramos al controlador :(
		if( !$found )
		{
			Bit::Status('No ha sido posible encontrar al controlador "' . $name . '".', $name);
			Bit::LaunchError('bitrock.load.controller');
		}

		self::$controllers[] = $name;
	}
}
?>