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
	
class BitRock
{
	static $status 	= array();
	static $logs 	= array();
	static $files 	= array();
	static $dirs 	= array();
	static $inerror = false;
	static $ignore 	= false;
	static $details = array();
	static $modules = array();
	
	// Función - Constructor.
	function __construct()
	{
		if(!version_compare(PHP_VERSION, '5.3.0', '>='))
			exit('BeatRock no soporta esta versión de PHP (' . phpversion() . '). Por favor actualiza tu plataforma de PHP a la 5.3.X o superior.');

		spl_autoload_register('BitRock::LoadMod');
		register_shutdown_function('BitRock::Shutdown');		
		
		set_exception_handler('BitRock::HaveException');		
		set_error_handler('BitRock::HaveError', E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);		

		self::Log('BeatRock ha comenzado.');
		
		self::Register(KERNEL . 'Functions.Header.php');
		self::Register(KERNEL . 'Functions.php');
		self::Register(TEMPLATES_BIT . 'Error.tpl');
		self::Register(HEADERS . 'Header.php');
		self::Register(HEADERS . 'Footer.php');
		
		self::Register(KERNEL . 'BitRock', true);
		self::Register(BIT . 'Logs', true);
		self::Register(HEADERS, true);
		self::Register(TEMPLATES, true);
		
		self::VerifyBoot();
	}
	
	// Función - Registrar archivo/directorio requerido.
	// - $file: Ruta del archivo/directorio.
	// - $dir (Bool): ¿Es un directorio?
	static function Register($file, $dir = false)
	{
		$dir ? self::$dirs[] = $file : self::$files[] = $file;
	}
	
	// Función - Verificación de inicio.
	static function VerifyBoot()
	{
		foreach(self::$files as $f)
		{			
			if(!file_exists($f))
				self::SetStatus('El archivo necesario especificado no existe.', $f);
		}
		
		foreach(self::$dirs as $d)
		{
			if(!is_dir($d))
				self::SetStatus('El directorio especificado no existe.', $d);
		}

		if(file_exists(KERNEL . 'Configuration.json'))
		{		
			if(!function_exists('curl_init'))
				self::SetStatus('La librería cURL esta desactivada en PHP, esta es necesaria para BeatRock, por favor activela para continuar.', '', Array('function' => 'curl_init'));
			
			if(!function_exists('json_decode'))
				self::SetStatus('La librería JSON esta desactivada en PHP, esta es necesaria para BeatRock, por favor activela para continuar.', '', Array('function' => 'json_decode'));
		}
		
		if(!empty(self::$status['response']))
			self::LaunchError('setup.init');
		
		self::$files 	= array();
		self::$dirs 	= array();

		self::Log('La verificación de inicio se ha completado.');
	}
	
	// Función - Guardar log.
	// - $message: Mensaje a guardar.
	// - $type (info, warning, error, mysql): Tipo del log.
	static function Log($message, $type = 'info')
	{
		global $config;
		
		if(!is_string($message))
			return;
			
		if($config['logs']['capture'] == false)
			return;

		$ALLOWED = array('info', 'warning', 'error', 'mysql', 'memcache');
		
		if(!in_array($type, $ALLOWED))
			return;
		
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

		if(in_array('Lang', self::$modules))
			$message = _l($message);
		
		$html = '<label title="' . date('h:i:s') . '"><b style="color: '.$color.'">['.$status.']</b> - '.$message.'</label><br />';
		$text = '['.$status.'] (' . date('h:i:s') . ') - '.$message.'\r\n';
		
		self::$logs['all']['html'] .= $html;
		self::$logs['all']['text'] .= $text;		
		self::$logs[$type]['html'] .= $html;
		self::$logs[$type]['text'] .= $text;
	}
	
	// Función - Guardar logs.
	static function SaveLog()
	{
		global $config;
		$save = $config['logs']['save'];
		
		if(!$save OR empty($save))
			return;
		
		if($save !== 'onerror' AND empty(self::$logs[$save]))	
			return;
		
		if($save == 'onerror')
		{
			if(empty(self::$logs['error']))
				return;
			else
				$save = 'all';
		}		
		
		$name = 'Logs-' . date('d_m_Y') . '-' . time() . '.txt';
		Io::SaveLog($name, self::$logs[$save]['text']);
	}
	
	// Función - Imprimir Logs.
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
	
	// Función privada - Cargar un módulo.
	// - $name: Nombre del modulo.
	function LoadMod($name)
	{
		if(in_array($name, self::$modules))
			return;

		$mod = $name.'.php';
		
		if(file_exists(MODS . $mod))
			require MODS . $mod;
		else if(file_exists(SITE_MODS . $mod))
			require SITE_MODS . $mod;
		else if(file_exists(MODS . 'External' . DS . $mod))
			require MODS . 'External' . DS . $mod;
		else
		{
			self::SetStatus('No se ha podido cargar el módulo "'.$name.'".', $name);
			self::LaunchError('bitrock.load.module');
		}
		
		if($name == 'Codes')
			Codes::Init();
		if($name == 'DNS')
			require_once MODS . 'External/SMTPValidate.php';
			
		self::$modules[] = $name;
		self::Log('Se ha cargado el módulo "'.$name.'" correctamente.');
	}
	
	// Función - Ha ocurrido un error.
	// Variables de respuesta especificadas por el Callback.
	static function HaveError($num, $message, $file, $line)
	{
		self::SetStatus($message, $file, array('line' => $line));
		self::LaunchError('php.code');
		
		return true;
	}
	
	// Función - Ha ocurrido una excepción.
	// Variable de respuesta especificada por el callback.
	static function HaveException($e)
	{
		self::SetStatus($e->getMessage(), $e->getfile(), array('line' => $e->getline()));
		self::LaunchError('php.exception');
		
		return true;
	}
	
	// Función - Establecer estado/información de un error.
	// - $response: Mensaje de respuesta.
	// - $file: Archivo responsable.
	// - $data (Array): Más información...
	static function SetStatus($response, $file, $data = array())
	{	
		self::$status['response'] 	= Lang::SetParams($response);
		self::$status['file'] 		= $file;
		
		foreach($data as $param => $value)
			self::$status[$param] = $value;
	}
	
	// Función - Lanzar un error.
	// - $code: Código del error.
	static function LaunchError($code)
	{
		if(self::$ignore OR self::$inerror)
		{
			self::$ignore = false;
			return;
		}
		
		self::$inerror = true;
		extract($GLOBALS);
		
		$info 	= Codes::GetInfo($code);
		$res 	= self::$status;
		
		$last 			= error_get_last();
		$res['last'] 	= $last['message'].' en "'.$last['file'].'" línea '.$last['line'];
		
		Client::SavePost();
		
		if(MySQL::Ready() AND $code !== 'php.code')
		{
			if($code == '03m')
			{								
				if($config['mysql']['repair.error'] OR $config['errors']['hidden'])
					MySQL::Repair();
					
				Core::HiddenError();
			}
			else
			{
				$report_code = Core::Random(10);
				
				MySQL::query_insert('site_errors', array(
					'report_code' 	=> $report_code,
					'code' 			=> $code,
					'title' 		=> $info['title'],
					'response' 		=> _f($res['response']),
					'file' 			=> _f($res['file']),
					'function' 		=> $res['function'],
					'line' 			=> $res['line'],
					'out_file' 		=> _f($res['out_file']),
					'more' 			=> _f(json_encode($res), false),
					'date' 			=> time()
				));
			}
		}
			
		self::$details = array(
			'report_code' 	=> $report_code,
			'code' 			=> $code,
			'info' 			=> $info,
			'res' 			=> $res
		);
		
		$mail_result = Core::SendError();
		self::Log('Ha ocurrido un error: '.$code.' - '.$info['title'].' - '.$info['details'], 'error');
		
		ob_flush();

		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Cache-Control: no-cache');
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');

		if(empty(Lang::$lang))
			Lang::Init();
		
		$html = Tpl::Process(TEMPLATES_BIT . 'Error');
		$html = _l($html, 'page.error');
		
		foreach($info as $param => $value)
			$html = str_ireplace("%$param%", $value, $html);
		
		exit($html);
	}
	
	// Función - Verificar la carga media del CPU y Memoria.
	static function CheckLoad()
	{
		global $site;

		$last_verify = $_SESSION['load_verify'];

		if(time() < $last_verify)
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
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Cache-Control: no-cache');
			header('HTTP/1.1 503 Service Temporarily Unavailable');
			header('Status: 503 Service Temporarily Unavailable');
			
			foreach($GLOBALS as $g => $v)	
				unset($g, $v);
				
			echo Tpl::Process(BIT_TEMP . 'Overload');
			exit(1);
		}
	}
	
	// Función - Apagado de BeatRock.
	static function ShutDown()
	{
		$finish = (microtime(true) - START);
		
		self::log('BeatRock tardo ' . substr($finish, 0, 5) . ' segundos en ejecutarse con un uso de ' . round(memory_get_usage() / 1024,1) . ' KB de Memoria.');
		self::log('Se realizaron ' . MySQL::$querys . ' consultas durante la sesión actual.', 'mysql');
		self::log('Se cargaron ' . count(self::$modules) . ' módulos durante la sesión actual.');
		
		global $page;
		
		if(!empty($page['id']) AND empty(Tpl::$html))
		{
			Tpl::Load();
			Tpl::SaveCache();
		}
			
		if(self::$inerror == false AND !empty(Tpl::$html))
			echo Tpl::$html;

		if(in_array('Ftp', self::$modules))
			Ftp::Crash();

		if(in_array('Socket', self::$modules))
			Socket::Crash();
		
		if(MySQL::Ready())
		{			
			Site::CheckTimers();			
			MySQL::Crash();
		}
		
		if(!empty(Io::$temp))
		{
			foreach(Io::$temp as $t)
				@unlink($t);
		}		
		
		session_write_close();
		self::SaveLog();

		foreach($GLOBALS as $g => $v)	
		{
			if($g == '_COOKIE' OR $g == '_SESSION')
				continue;

			unset($g, $v);
		}
		
		// Descomente la siguiente linea para ver los últimos logs...
		//self::PrintLog(true);
	}
	
	// Función - Guardar un Backup de toda la aplicación.
	// - $db (Bool) - ¿Incluir un backup de la base de datos?
	static function Backup($db = false)
	{
		$name = BIT . 'Backups' . DS . 'Backup-' . date('d_m_Y') . '-' . time() . '.zip';
		
		$a = new PclZip($name);
		$e = $a->create(ROOT);
		
		if($e == 0)
			return false;
		
		if($db)
		{
			$b = MySQL::Backup();
			$b = BIT . 'Backups' . DS . $b;
			
			Zip::Add($name, $b);
			unlink($b);
		}
		
		self::Log('Se ha creado un Backup total correctamente.');
		return $name;
	}
	
	// Función - Imprimir estadisticas.
	static function Statistics()
	{
		global $constants;

		$finish 	= (microtime(true) - START);
		$finish 	= substr($finish, 0, 5);

		$memory 	= round(memory_get_usage() / 1024 / 1024,1);
		$modules 	= count(self::$modules);

		$variables  = $GLOBALS;
		$variables 	= count($variables);

		$functions 	= get_defined_functions();
		$functions 	= count($functions['user']);

		$constant 	= count($constants);
		$files 		= get_included_files();
		
		$return = 'BeatRock tardo ' .$finish. ' segundos en ejecutarse con un uso de ' .$memory. ' MB de memoria.<br />';
		$return .= 'Se han cargado ' .$modules. ' módulos durante la sesión actual.<br />';
		$return .= 'Se han establecido ' .$variables. ' variables, '.$constant.' constantes y ' .$functions.' funciones durante la ejecución actual.<br />';

		if(in_array('MySQL', self::$modules))
			$return .= 'Se realizaron ' . MySQL::$querys . ' consultas MySQL durante la ejecución actual.<br />';

		$return .= '<br />Los siguientes archivos se han cargado:<br />';		
		$return .= Core::SplitArray($files);
		
		return $return;
	}
}
?>