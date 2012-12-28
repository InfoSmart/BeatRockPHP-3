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
	
class Bit
{
	static $status 		= array();
	static $logs 		= array();
	static $files 		= array();
	static $dirs 		= array();
	static $inerror 	= false;
	static $ignore 		= false;
	static $details 	= array();
	static $controllers = array();
	
	// Inicialización de BitRock.
	function __construct()
	{
		// Verificar la versión de PHP.
		if(!version_compare(PHP_VERSION, '5.3.0', '>='))
			exit('Oops! BeatRock no es compatible con esta versión de PHP (' . phpversion() . '). Por favor actualiza tu versión de PHP a la 5.3 o superior. Lo sentimos :(');

		// Registrar funciones de carga de controladores y apagado de BeatRock.
		spl_autoload_register('Bit::LoadController');
		register_shutdown_function('Bit::Shutdown');	
		
		// Registrar funciones de control de errores y excepciones.
		set_exception_handler('Bit::Exception');		
		set_error_handler('Bit::Error', E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);		

		self::Log('BeatRock ha comenzado.');
		
		// Registrar archivos y carpetas necesarios.	
		self::Register(APP . 'Setup.Header.php');
		self::Register(APP . 'Setup.php');
		self::Register(KERNEL_TPL_BIT . 'Error.html');
		self::Register(HEADERS . 'Header.php');
		self::Register(HEADERS . 'Footer.php');
		
		self::Register(BIT, true);
		self::Register(HEADERS, true);
		self::Register(KERNEL_TPL, true);
		
		self::VerifyBoot();
	}
	
	// Registrar archivo/directorio requerido.
	// - $file: Ruta del archivo/directorio.
	// - $dir (Bool): ¿Es un directorio?
	static function Register($file, $dir = false)
	{
		($dir) ? self::$dirs[] = $file : self::$files[] = $file;
	}
	
	// Verificación de inicio.
	static function VerifyBoot()
	{
		foreach(self::$files as $FILE)
		{			
			if(!file_exists($FILE))
				self::Status('El archivo necesario especificado no existe.', $FILE);
		}
		
		foreach(self::$dirs as $DIR)
		{
			if(!is_dir($DIR))
				self::Status('El directorio especificado no existe.', $DIR);
		}

		if(file_exists(APP . 'Configuration.php'))
		{		
			if(!function_exists('curl_init'))
				self::Status('La librería cURL esta desactivada en PHP, esta es necesaria para BeatRock, por favor activela para continuar.', '', array('function' => 'curl_init'));
			
			if(!function_exists('json_decode'))
				self::Status('La librería JSON esta desactivada en PHP, esta es necesaria para BeatRock, por favor activela para continuar.', '', array('function' => 'json_decode'));
		}
		
		if(!empty(self::$status['response']))
			self::LaunchError('setup.init');
		
		self::$files 	= array();
		self::$dirs 	= array();

		self::Log('La verificación de inicio se ha completado.');
	}
	
	// Guardar log.
	// - $message: Mensaje a guardar.
	// - $type (info, warning, error, mysql): Tipo del log.
	static function Log($message, $type = 'info')
	{
		global $config;
		
		if(!is_string($message) OR $config['logs']['capture'] == false)
			return false;

		$ALLOWED = array('info', 'warning', 'error', 'mysql', 'memcache');
		
		if(!in_array($type, $ALLOWED))
			return false;
		
		if($type == 'info')
		{
			$status = 'INFO';
			$color 	= '#045FB4';
		}
		
		if($type == 'warning')
		{
			$status = 'ALERTA';
			$color 	= '#8A4B08';
		}
		
		if($type == 'error')
		{
			$status = 'ERROR';
			$color 	= 'red';
		}
		
		if($type == 'mysql')
		{
			$status = 'MYSQL';
			$color 	= '#0B610B';			
		}

		if($type == 'memcache')
		{
			$status = 'Memcache';
			$color 	= '#29088A';			
		}

		if(in_array('Lang', self::$controllers))
			$message = _l($message);
		
		$html = '<label title="' . date('h:i:s') . '"><b style="color: '.$color.'">['.$status.']</b> - '.$message.'</label><br />';
		$text = '['.$status.'] (' . date('h:i:s') . ') - '.$message.'\r\n';
		
		self::$logs['all']['html'] .= $html;
		self::$logs['all']['text'] .= $text;		
		self::$logs[$type]['html'] .= $html;
		self::$logs[$type]['text'] .= $text;
	}
	
	// Guardar logs.
	static function SaveLog()
	{
		global $config;
		$save = $config['logs']['save'];
		
		if(!$save)
			return false;
		
		if($save !== 'onerror' AND empty(self::$logs[$save]))	
			return false;
		
		if($save == 'onerror')
		{
			if(empty(self::$logs['error']))
				return false;
			
			$save = 'all';
		}		
		
		$name = 'Logs-' . date('d_m_Y') . '-' . time() . '.txt';
		Io::SaveLog($name, self::$logs[$save]['text']);

		return $name;
	}
	
	// Imprimir logs.
	// - $html (Bool): ¿Imprimir en formato de HTML? (Más bonito)
	// - $type (all, error, warning, info, mysql, memcache): Tipo de Logs ha imprimir.
	static function PrintLog($html = true, $type = 'all')
	{
		if(empty($type))
			$type = 'all';
			
		$finish = (microtime(true) - START);
		self::Log('BeatRock tardo ' . substr($finish, 0, 5) . ' segundos en ejecutarse con un uso de ' . round(memory_get_usage() / 1024,1) . ' KB de Memoria.');
		
		echo ($html) ? self::$logs[$type]['html'] : self::$logs[$type]['text'];
	}
	
	// Cargar un controlador.
	// - $name: Nombre del controlador.
	static function LoadController($name)
	{
		if(self::ControllerLoaded($name))
			return;

		if($name == 'SQL')
		{
			global $config;
			self::$controllers[] = $name;

			if($config['sql']['type'] == 'mysql')
				$name = 'MySQL';

			else if($config['sql']['type'] == 'sqlite')
				$name = 'SQLite';
		}

		$CONTROLLER = $name.'.php';
		
		if(file_exists(KERNEL_CTRLS . $CONTROLLER))
			require KERNEL_CTRLS . $CONTROLLER;

		else if(file_exists(APP_CTRLS . $CONTROLLER))
			require APP_CTRLS . $CONTROLLER;

		else if(file_exists(APP_CTRLS . 'External' . DS . $CONTROLLER))
			require APP_CTRLS . 'External' . DS . $CONTROLLER;

		else if(file_exists(KERNEL_CTRLS . 'Server' . DS . $CONTROLLER))
			require KERNEL_CTRLS . 'Server' . DS . $CONTROLLER;

		else if(file_exists(KERNEL_CTRLS . 'API' . DS . $CONTROLLER))
			require KERNEL_CTRLS . 'API' . DS . $CONTROLLER;

		else
		{
			self::Status('No se ha podido cargar el controlador "'.$name.'".', $name);
			self::LaunchError('bitrock.load.module');
		}
		
		if($name == 'Codes')
			new Codes();
		if($name == 'DNS')
			require_once APP_CTRLS . 'External' . DS . 'SMTPValidate.php';
			
		self::$controllers[] = $name;
		self::Log('Se ha cargado el controlador "'.$name.'" correctamente.');
	}

	static function ControllerLoaded($name)
	{
		return (in_array($name, self::$controllers)) ? true : false;
	}
	
	// Ha ocurrido un error.
	// Variables de respuesta especificadas por el callback.
	static function Error($num, $message, $file, $line)
	{
		self::Status($message, $file, array('line' => $line));
		self::LaunchError('php.code');
		
		return true;
	}
	
	// Ha ocurrido una excepción.
	// Variable de respuesta especificada por el callback.
	static function Exception($e)
	{
		self::Status($e->getMessage(), $e->getfile(), array('line' => $e->getline()));
		self::LaunchError('php.exception');
		
		return true;
	}
	
	// Establecer la información del último error.
	// - $response: Mensaje de respuesta.
	// - $file: Archivo responsable.
	// - $data (Array): Más información...
	static function Status($response, $file, $data = array())
	{	
		self::$status['response'] 	= Lang::SetParams($response);
		self::$status['file'] 		= $file;
		
		foreach($data as $param => $value)
			self::$status[$param] = $value;
	}

	// Ignorar (o no) un error que se ocacione.
	static function Ignore()
	{
		(self::$ignore == true) ? self::$ignore = false : self::$ignore = true;
	}
	
	// Lanzar un error.
	// - $code: Código del error.
	static function LaunchError($code)
	{
		extract($GLOBALS);
		
		$info 	= Codes::GetInfo($code);
		$status = self::$status;
		
		$last 			= error_get_last();
		$status['last'] = $last['message'].' en "'.$last['file'].'" línea '.$last['line'];

		if(self::$ignore OR self::$inerror)
		{
			Core::SendError();
			return;
		}
		
		Client::SavePost();
		
		if($code !== 'php.code' AND self::ControllerLoaded('SQL'))
		{
			if(SQL::Ready())
			{
				if($code == 'mysql.query')
				{								
					if($config['sql']['repair.error'] OR $config['errors']['hide'])
						SQL::Repair();
						
					Core::HideError();
				}
				else
				{
					$report_code = Core::Random(10);
					
					$result = SQL::query_insert('site_errors', array(
						'report_code' 	=> $report_code,
						'code' 			=> $code,
						'title' 		=> $info['title'],
						'response' 		=> _F($res['response']),
						'file' 			=> _F($res['file'], false),
						'function' 		=> $res['function'],
						'line' 			=> $res['line'],
						'out_file' 		=> _F($res['out_file']),
						'more' 			=> _F(json_encode($res), false),
						'date' 			=> time()
					));
				}
			}
		}
			
		self::$details = array(
			'report_code' 	=> $report_code,
			'code' 			=> $code,
			'info' 			=> $info,
			'res' 			=> $status
		);

		self::Log('Ha ocurrido un error: '.$code.' - '.$info['title'].' - '.$info['details'], 'error');
		self::$inerror = true;
		
		ob_clean();

		if($page['ajax'] == true OR $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' AND $page['ajax'] !== false)
		{
			$data = array('system_error' => self::$details);
			$data = json_encode(_c($data));
			
			exit($data);
		}

		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Cache-Control: no-cache');
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');

		if(empty(Lang::$lang))
			new Lang();
		
		global $site;
		$site['site_jade'] = false;

		$html = Tpl::Process('bitrock' . DS . 'Error');
		$html = _l($html, 'page.error');
		
		foreach($info as $param => $value)
			$html = str_ireplace("%$param%", $value, $html);
		
		exit($html);
	}
	
	// Verificar la carga del CPU y la memoria.
	static function CheckLoad()
	{
		global $site;
		$last_verify = $_SESSION['load_verify'];

		if(time() < $last_verify AND !empty($last_verify))
			return;
		
		$memory_limit 	= ini_get('memory_limit');
		$memory_load 	= 0;
		$apache_load 	= 0;
		$cpu_load 		= 0;

		if(!empty($memory_limit))
		{
			$memory_load = memory_get_usage() + 500000;

			if(Contains($memory_limit, 'M'))
				$memory_limit = round(str_replace('M', '', $memory_limit) * 1048576);
		}

		if($site['apache_limit'] >= 52428800)	
			$apache_load = Core::memory_usage() + 500000;

		if($site['cpu_limit'] >= 50)
			$cpu_load = Core::sys_load() + 10;

		$_SESSION['load_verify'] = (time() + (3 * 60));
		
		if($memory_load > $memory_limit OR $apache_load > $site['apache_limit'] OR $cpu_load > $site['cpu_limit'])
		{
			global $page;

			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Cache-Control: no-cache');
			header('HTTP/1.1 503 Service Temporarily Unavailable');
			header('Status: 503 Service Temporarily Unavailable');
			
			foreach($GLOBALS as $g => $v)	
				unset($g, $v);
			
			$page['lang.sections'] = array('global', 'page.overload');
			echo Tpl::Process(KERNEL_TPL_BIT . 'Overload');
			exit(1);
		}
	}

	// Recuperación avanzada
	static function AdvBackup()
	{
		global $config;
		$back = _CACHE('backup_time');

		if($config['server']['backup'] AND (empty($back) OR time() > $back))
		{
			_CACHE('backup_config', Io::Read(APP . 'Configuration.php'));
			_CACHE('backup_db', 	SQL::Backup('', true));
			_CACHE('backup_time', 	Core::Time(30, 2));
		}
		else if(!$config['server']['backup'])
		{
			_DELCACHE('backup_config');
			_DELCACHE('backup_db');
			_DELCACHE('backup_time');
		}
	}
	
	// Apagado de BeatRock.
	static function ShutDown()
	{
		$finish = (microtime(true) - START);
		
		self::log('BeatRock tardo ' . substr($finish, 0, 5) . ' segundos en ejecutarse con un uso de ' . round(memory_get_usage() / 1024,1) . ' KB de Memoria.');

		if(self::ControllerLoaded('SQL'))
			self::log('Se realizaron ' . SQL::$querys . ' consultas durante la sesión actual.', 'mysql');
		
		self::log('Se cargaron ' . count(self::$controllers) . ' controladores durante la sesión actual.');
		
		global $page;
		
		if(!empty($page['id']) AND empty(Tpl::$html))
		{
			Tpl::Load();
			Tpl::SaveCache();
		}
			
		if(self::$inerror == false AND !empty(Tpl::$html))
			echo Tpl::$html;
		
		if(self::ControllerLoaded('SQL'))
		{
			if(SQL::Ready())
			{			
				Site::CheckTimers();		
				SQL::Destroy();
			}
		}
		
		if(!empty(Io::$temp))
		{
			foreach(Io::$temp as $t)
				@unlink($t);
		}
		
		self::SaveLog();
		session_write_close();

		foreach($GLOBALS as $g => $v)	
		{
			if($g == '_COOKIE' OR $g == '_SESSION')
				continue;

			unset($g, $v);
		}
		
		// Descomente la siguiente linea para ver los últimos logs...
		#self::PrintLog(true);
	}
	
	// Guardar un Backup de toda la aplicación.
	// - $db (Bool) - ¿Incluir un backup de la base de datos?
	static function Backup($db = false)
	{
		global $site;

		$path = BIT . 'Backups' . DS;
		$name = 'Backup-' . date('d_m_Y') . '-' . time() . '.zip';
		
		$a = new PclZip($path . $name);
		$e = $a->create(ROOT);
		
		if($e == 0)
			return false;
		
		if($db)
		{
			$b = SQL::Backup();
			$b = BIT . 'Backups' . DS . $b;
			
			Zip::Add($path . $name, $b);
			unlink($b);
		}

		if($site['site_backups_servers'] == 'true')
			Bit::Send_FTPBackup($path . $name, $name);
		
		self::Log('Se ha creado un Backup total correctamente.');
		return $name;
	}

	static function Send_FTPBackup($file, $filename)
	{
		$servers = Site::Get('backups_servers');

		while($row = fetch_assoc())
		{
			$folder = 'Backup-' . date('d_m_Y');
			$ftp 	= new Ftp($row['host'], $row['username'], $row['password'], $row['port']);

			if(!empty($row['directory']))
			{
				//self::$ignore = true;
				$ftp->ToDir($row['directory']);
			}

			$ftp->NewDir($folder);
			$ftp->ToDir($folder);
			$ftp->Upload($file, $filename);
		}

		Bit::$ignore = false;
	}
	
	// Imprimir estadisticas.
	static function Statistics()
	{
		global $constants;

		$finish 	= (microtime(true) - START);
		$finish 	= substr($finish, 0, 5);

		$memory 	= round(memory_get_usage() / 1024 / 1024,1);
		$modules 	= count(self::$controllers);

		$variables  = $GLOBALS;
		$variables 	= count($variables);

		$functions 	= get_defined_functions();
		$functions 	= count($functions['user']);

		$constant 	= count($constants);
		$files 		= get_included_files();
		
		$return = 'BeatRock tardo ' .$finish. ' segundos en ejecutarse con un uso de ' .$memory. ' MB de memoria.<br />';
		$return .= 'Se han cargado ' .$modules. ' módulos durante la sesión actual.<br />';
		$return .= 'Se han establecido ' .$variables. ' variables, '.$constant.' constantes y ' .$functions.' funciones durante la ejecución actual.<br />';

		if(in_array('SQL', self::$controllers))
			$return .= 'Se realizaron ' . SQL::$querys . ' consultas SQL durante la ejecución actual.<br />';

		$return .= '<br />Los siguientes archivos (' . count($files) . ') se han cargado:<br />';		
		
		$files 		= Core::SplitArray($files);

		foreach($files as $file)
			$return .= $file;
		
		return $return;
	}
}
?>