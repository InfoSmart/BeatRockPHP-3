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
	
/*
	BeatRock tiene soporte de logs por medio de ChromePHP.
	Si cuentas con ChromePHP (http://www.chromephp.com/) puedes descomentar
	la línea que define 'DEBUG' en Init.php
*/

class BitRock
{
	public static $status = Array();
	public static $logs = Array();
	private static $files = Array();
	private static $dirs = Array();
	private static $inerror = false;
	public static $ignore = false;
	public static $details = Array();
	public static $modules = 0;
	public static $load_modules = Array();
	
	// Función - Constructor.
	public function __construct()
	{
		if(!version_compare(PHP_VERSION, '5.3.0', '>='))
			exit('BeatRock no soporta esta versión de PHP (' . phpversion() . '). Por favor actualiza tu plataforma de PHP a la 5.3.X o superior.');

		spl_autoload_register(Array(self, 'LoadMod'));		
		self::log('BeatRock ha comenzado.');
		
		set_exception_handler(Array($this, 'HaveException'));
		
		if(defined('DEBUG'))
			set_error_handler(Array($this, 'HaveError'), E_ALL & ~E_NOTICE);
		else
			set_error_handler(Array($this, 'HaveError'), E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
		
		register_shutdown_function('BitRock::ShutDown');
		
		self::RegReq(KERNEL . 'Functions.Header.php');
		self::RegReq(KERNEL . 'Functions.php');
		self::RegReq(BIT_TEMP . 'Error.tpl');
		self::RegReq(BIT_TEMP . 'Error.Mobile.tpl');
		self::RegReq(HEADERS . 'Header.php');
		self::RegReq(HEADERS . 'Header.Mobile.php');
		self::RegReq(HEADERS . 'Footer.php');
		
		self::RegReq(KERNEL . 'BitRock', true);
		self::RegReq(BIT . 'Backups', true);
		self::RegReq(BIT . 'Logs', true);
		self::RegReq(BIT . 'Temp', true);
		self::RegReq(HEADERS, true);
		self::RegReq(TEMPLATES, true);
		
		self::VerifyBoot();
	}
	
	// Función - Registrar archivo/directorio requerido.
	// - $file: Ruta del archivo/directorio.
	// - $dir (Bool): ¿Es un directorio?
	public static function RegReq($file, $dir = false)
	{
		$dir ? self::$dirs[] = $file : self::$files[] = $file;
	}
	
	// Función - Verificación de inicio.
	public static function VerifyBoot()
	{	
		self::$status = Array();

		foreach(self::$files as $f)
		{			
			if(!file_exists($f))
				self::setStatus('El archivo necesario especificado no existe.', $f);
		}
		
		foreach(self::$dirs as $d)
		{
			if(!is_dir($d))
				self::setStatus('El directorio especificado no existe.', $d);
		}
		
		if(!function_exists('curl_init'))
			self::setStatus('Se ha detectado que la librería de cURL esta desactivada en PHP, esta es necesaria para BeatRock, por favor activela para continuar.', '', Array('function' => 'curl_init'));
		
		if(!function_exists('json_decode'))
			self::setStatus('Se ha detectado que la librería de JSON esta desactivada en PHP, esta es necesaria para BeatRock, por favor activela para continuar.', '', Array('function' => 'json_decode'));
		
		if(!empty(self::$status['response']))
			self::launchError('02x');
		
		self::log('La verificación de inicio se ha completado.');
	}
	
	// Función - Guardar log.
	// - $message: Mensaje a guardar.
	// - $type (info, warning, error, mysql): Tipo del log.
	public static function log($message, $type = 'info')
	{
		global $config;
		
		if(!is_string($message))
			return;
			
		if(isset($config['logs']['capture']) AND $config['logs']['capture'] == false)
			return;
		
		if($type !== 'info' AND $type !== 'warning' AND $type !== 'error' AND $type !== 'mysql' AND $type !== 'memcache')
			return;
		
		if($type == 'info')
		{
			$status = 'INFO';
			$color = '#045FB4';
			
			if(defined('DEBUG'))
				ChromePhp::log('['.$status.'] - '.$message);
		}
		
		if($type == 'warning')
		{
			$status = 'ALERTA';
			$color = '#8A4B08';
			
			if(defined('DEBUG'))
				ChromePhp::warn('['.$status.'] - '.$message);
		}
		
		if($type == 'error')
		{
			$status = 'ERROR';
			$color = 'red';
			
			if(defined('DEBUG'))
				ChromePhp::error('['.$status.'] - '.$message);
		}
		
		if($type == 'mysql')
		{
			$status = 'MYSQL';
			$color = '#0B610B';			
			
			if(defined('DEBUG'))
				ChromePhp::log('['.$status.'] - '.$message);
		}

		if($type == 'memcache')
		{
			$status = 'Memcache';
			$color = '#29088A';			
			
			if(defined('DEBUG'))
				ChromePhp::log('['.$status.'] - '.$message);
		}
		
		$html = '<label title="' . date('h:i:s') . '"><b style="color: '.$color.'">['.$status.']</b> - '.$message.'</label><br />';
		$text = '['.$status.'] (' . date('h:i:s') . ') - '.$message.'\r\n';
		
		self::$logs['all']['html'] .= $html;
		self::$logs['all']['text'] .= $text;		
		self::$logs[$type]['html'] .= $html;
		self::$logs[$type]['text'] .= $text;
	}
	
	// Función - Guardar logs.
	public static function SaveLog()
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
	// - $type (all, error, warning, info, mysql): Tipo de Logs ha imprimir.
	public static function PrintLog($html = true, $type = 'all')
	{
		if(empty($type))
			$type = 'all';
			
		$finish = (microtime(true) - START);
		self::log('BeatRock tardo ' . substr($finish, 0, 5) . ' segundos en ejecutarse con un uso de ' . round(memory_get_usage() / 1024,1) . ' KB de Memoria.');
		
		echo $html ? self::$logs[$type]['html'] : self::$logs[$type]['text'];
	}
	
	// Función privada - Cargar un módulo.
	// - $name: Nombre del modulo.
	private function LoadMod($name)
	{
		if(in_array($name, self::$load_modules))
			return;

		$mod = $name.'.php';
		
		if(file_exists(MODS . $mod))
			require(MODS . $mod);
		else if(file_exists(MODS . 'External' . DS . $mod))
			require(MODS . 'External' . DS . $mod);
		else
		{
			self::SetStatus('No se ha podido cargar el módulo "'.$name.'".', $name);
			self::LaunchError('04x');
		}
		
		if($name == 'Codes')
			Codes::LoadCodes();
			
		++self::$modules;
		self::$load_modules[] = $name;

		self::log('Se ha cargado el módulo "'.$name.'" correctamente.');
	}
	
	// Función - Ha ocurrido un error.
	// Variables de respuesta especificadas por el Callback.
	public static function HaveError($num, $message, $file, $line)
	{
		self::SetStatus($message, $file, Array('line' => $line));
		self::LaunchError('01x');
		
		return true;
	}
	
	// Función - Ha ocurrido una excepción.
	// Variable de respuesta especificada por el callback.
	public static function HaveException($e)
	{
		self::SetStatus($e->getMessage(), $e->getfile(), Array('line' => $e->getline()));
		self::LaunchError('01e');
		
		return true;
	}
	
	// Función - Establecer estado/información de un error.
	// - $response: Mensaje de respuesta.
	// - $file: Archivo responsable.
	// - $data (Array): Más información...
	public static function SetStatus($response, $file, $data = Array())
	{
		self::$status['response'] = $response;
		self::$status['file'] = $file;
		
		foreach($data as $param => $value)
			self::$status[$param] = $value;
	}
	
	// Función - Lanzar un error.
	// - $code: Código del error.
	public static function LaunchError($code)
	{
		if(self::$ignore)
		{
			self::$ignore = false;
			return;
		}
		
		if(self::$inerror)
			return;
		
		self::$inerror = true;
		extract($GLOBALS);
		
		$info = Codes::GetInfo($code);
		$res = self::$status;
		
		$last = error_get_last();
		$res['last'] = $last['message'].' en "'.$last['file'].'" línea '.$last['line'];
		
		Client::SavePost();
		
		if(MySQL::Ready() AND $code !== '01x')
		{
			if($code == '03m')
			{				
				if($config['mysql']['optimize.error'] OR $config['errors']['hidden'])
					MySQL::Optimize();
					
				if($config['mysql']['repair.error'] OR $config['errors']['hidden'])
					MySQL::Repair();
					
				Core::HiddenError();
			}
			else
			{
				MySQL::query_insert('site_errors', Array(
					'code' => $code,
					'title' => $info['title'],
					'response' => _f($res['response']),
					'file' => _f($res['file']),
					'function' => $res['function'],
					'line' => $res['line'],
					'out_file' => _f($res['out_file']),
					'more' => _f(json_encode($res), false),
					'date' => time()
				));
			}
		}
		
		$e = 'Error';

		if(Core::IsMobile())
			$e = 'Error.Mobile';
			
		self::$details = Array(
			'code' => $code,
			'info' => $info,
			'res' => $res
		);
		
		$mail_result = Core::SendError();
		self::log('Ha ocurrido un error: '.$code.' - '.$info['title'].' - '.$info['details'], 'error');
		
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Cache-Control: no-cache');
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
		
		ob_clean();
		
		$html = Tpl::Process(BIT_TEMP . DS . $e);
		
		foreach($info as $param => $value)
			$html = str_ireplace("%$param%", $value, $html);
		
		exit($html);
	}
	
	// Función - Verificar la carga media del CPU y Memoria.
	public static function CheckLoad()
	{
		global $config;

		$last_verify = $_SESSION['load_verify'];

		if(time() < $last_verify)
			return;
		
		$memory_limit = ini_get('memory_limit');
		$memory_load = 0;
		$apache_load = 0;
		$cpu_load = 0;

		if(!empty($memory_limit))
		{
			$memory_load = memory_get_usage() + 500000;

			if(Contains($memory_limit, 'M'))
				$memory_limit = round(str_replace('M', '', $memory_limit) * 1048576);
		}

		if($config['server']['limit'] >= 52428800)	
			$apache_load = Core::memory_usage() + 500000;

		if($config['server']['limit_load'] >= 50)
			$cpu_load = Core::sys_load() + 10;

		$_SESSION['load_verify'] = (time() + (3 * 60));
		
		if($memory_load > $memory_limit OR $apache_load > $config['server']['limit'] OR $cpu_load > $config['server']['limit_load'])
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
	public static function ShutDown()
	{
		$finish = (microtime(true) - START);
		
		self::log('BeatRock tardo ' . substr($finish, 0, 5) . ' segundos en ejecutarse con un uso de ' . round(memory_get_usage() / 1024,1) . ' KB de Memoria.');
		self::log('Se realizaron ' . MySQL::$querys . ' consultas durante la sesión actual.', 'mysql');
		self::log('Se cargaron ' . self::$modules . ' módulos durante la sesión actual.');
		
		global $page;
		
		if(!empty($page['id']) AND empty(Tpl::$html) AND !Socket::$server)
			Tpl::Load();
			
		if(ob_get_length() !== false AND self::$inerror == false)
		{
			if(!empty(Tpl::$html))
				echo Tpl::$html;
			else
				ob_end_flush();
		}

		Tpl::SaveCache();

		if(is_array("Ftp", self::$load_modules))
			Ftp::Crash();

		if(is_array("Socket", self::$load_modules))
			Socket::Crash();
		
		if(MySQL::Ready())
		{
			if(defined('DEBUG'))
				Site::SaveLog();
				
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
	public static function Backup($db = false)
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
		
		self::log('Se ha creado un Backup total correctamente.');
		return $name;
	}
	
	// Función - Imprimir estadisticas.
	public static function Statistics()
	{
		$finish = (microtime(true) - START);
		
		$return = 'BeatRock tardo ' . substr($finish, 0, 5) . ' segundos en ejecutarse con un uso de ' . round(memory_get_usage() / 1024,1) . ' KB de Memoria.<br />';
		$return .= 'Se realizaron ' . MySQL::$q . ' consultas durante la sesión actual.<br />';
		$return .= 'Se cargaron ' . self::$modules . ' módulos durante la sesión actual.<br />';
		
		return $return;
	}
}
?>