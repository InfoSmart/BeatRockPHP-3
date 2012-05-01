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
	
/*
	BeatRock tiene soporte de logs por medio de ChromePHP.
	Si tienes ChromePHP (http://www.chromephp.com/) puedes descomentar
	la línea que define "DEBUG" en Init.php
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
	
	// Función - Constructor.
	public function __construct()
	{
		// Verificando versión del PHP.
		if(PHP_MAJOR_VERSION >= 5)
		{
			if(PHP_MINOR_VERSION < 3)
				exit('BeatRock no soporta esta versión de PHP (' . phpversion() . '). Por favor actualiza tu plataforma de PHP a la 5.3 o superior.');
		}
		
		// Registrar módulos cuando sea necesario.
		spl_autoload_register(Array(self, 'loadMod'));		
		self::log("BeatRock ha comenzado.");
		
		// Capturar errores para mostrarlas en la página de error.
		set_exception_handler(Array($this, "haveException"));
		
		if(defined("DEBUG"))
			set_error_handler(Array($this, "haveError"), E_ALL & ~E_NOTICE);
		else
			set_error_handler(Array($this, "haveError"), E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
		
		// Registrar la función de apagado.
		register_shutdown_function("BitRock::ShutDown");
		
		// Registrando archivos necesarios.
		self::registerReq(KERNEL . 'Functions.Header.php');
		self::registerReq(KERNEL . 'Functions.php');
		self::registerReq(BIT_TEMP . 'Error.tpl');
		self::registerReq(BIT_TEMP . 'Error.Mobile.tpl');
		self::registerReq(BIT_TEMP . 'Maintenance.tpl');
		self::registerReq(BIT_TEMP . 'Overload.tpl');
		self::registerReq(HEADERS . 'Header.php');
		self::registerReq(HEADERS . 'Header.Mobile.php');
		self::registerReq(HEADERS . 'Footer.php');
		
		// Registrando directorios necesarios.
		self::registerReq(KERNEL . 'BitRock', true);
		self::registerReq(BIT . 'Backups', true);
		self::registerReq(BIT . 'Logs', true);
		self::registerReq(BIT . 'Temp', true);
		self::registerReq(BIT_TEMP, true);
		self::registerReq(HEADERS, true);
		self::registerReq(TEMPLATES, true);
		
		// Verificación de inicio.
		self::verifyBoot();
	}
	
	// Función - Registrar archivo/directorio requerido.
	// - $file: Ruta del archivo/directorio.
	// - $dir (Bool): ¿Es un directorio?
	public static function registerReq($file, $dir = false)
	{
		$dir ? self::$dirs[] = $file : self::$files[] = $file;
	}
	
	// Función - Verificación de inicio.
	public static function verifyBoot()
	{	
		self::$status = Array();
		
		// Comprobación de archivos necesarios.
		foreach(self::$files as $f)
		{			
			if(!file_exists($f))
				self::setStatus('El archivo necesario especificado no existe.', $f);
		}
		
		// Comprobación de directorios necesarios.
		foreach(self::$dirs as $d)
		{
			if(!is_dir($d))
				self::setStatus('El directorio especificado no existe.', $d);
		}
		
		// Comprobación de la librería cURL.
		if(!function_exists("curl_init"))
			self::setStatus('Se ha detectado que la librería de cURL esta desactivada en PHP, esta es necesaria para BeatRock, por favor activela para continuar.', "", Array("function" => "curl_init"));
		
		// Comprobación de la librería JSON.
		if(!function_exists("json_decode"))
			self::setStatus('Se ha detectado que la librería de JSON esta desactivada en PHP, esta es necesaria para BeatRock, por favor activela para continuar.', "", Array("function" => "json_decode"));
		
		// Al parecer hubo errores de inicialización.
		if(!empty(self::$status['response']))
			self::launchError('02x');
		
		self::log("La verificación de inicio se ha completado.");
	}
	
	// Función - Guardar log.
	// - $msg: Mensaje a guardar.
	// - $type (info, warning, error, mysql): Tipo del log.
	public static function log($msg, $type = 'info')
	{
		global $config;
		
		// No es una cadena de texto. -.-"
		if(!is_string($msg))
			return;
			
		// No queremos guardar Logs.
		if(isset($config['logs']['capture']) AND $config['logs']['capture'] == false)
			return;
		
		// Tipo de alerta inválida.
		if($type !== 'info' AND $type !== 'warning' AND $type !== 'error' AND $type !== 'mysql')
			return;
		
		// Tipo: Normal.
		if($type == 'info')
		{
			$status = "INFO";
			$color = "#045FB4";
			
			if(defined("DEBUG"))
				ChromePhp::log("[$status] - $msg");
		}
		
		// Tipo: Alerta.
		if($type == "warning")
		{
			$status = "ALERTA";
			$color = "#8A4B08";
			
			if(defined("DEBUG"))
				ChromePhp::warn("[$status] - $msg");
		}
		
		// Tipo: Error.
		if($type == "error")
		{
			$status = "ERROR";
			$color = "red";
			
			if(defined("DEBUG"))
				ChromePhp::error("[$status] - $msg");
		}
		
		// Tipo: Acción MySQL.
		if($type == "mysql")
		{
			$status = "MYSQL";
			$color = "#21610B";			
			
			if(defined("DEBUG"))
				ChromePhp::log("[$status] - $msg");
		}
		
		$html = "<label title='" . date('h:i:s') . "'><b style='color: $color'>[$status]</b> - $msg</label><br />";
		$text = "[$status] (" . date('h:i:s') . ") - $msg\r\n";
		
		// Estableciendo nuevo log.
		self::$logs['all']['html'] .= $html;
		self::$logs['all']['text'] .= $text;
		
		self::$logs[$type]['html'] .= $html;
		self::$logs[$type]['text'] .= $text;
	}
	
	// Función - Guardar logs.
	public static function saveLog()
	{
		global $config;
		$save = $config['logs']['save'];
		
		// No queremos guardar Logs.
		if($save == false OR empty($save))
			return;
		
		// Solo guardar Logs especificos.
		if($save !== "onerror" AND empty(self::$logs[$save]))	
			return;
		
		// Guardar logs solo si se ocacionan errores.
		if($save == "onerror")
		{
			// No hubo errores, de otra forma, guardar logs.
			if(empty(self::$logs["error"]))
				return;
			else
				$save = "all";
		}		
		
		// Guardando archivo de texto del Log.
		$name = 'Logs-' . date('d_m_Y') . '-' . time() . '.txt';
		Io::SaveLog($name, self::$logs[$save]['text']);
	}
	
	// Función - Imprimir Logs.
	// - $html (Bool): ¿Imprimir en formato de HTML? (Más bonito)
	public static function printLog($html = true, $type = "all")
	{
		// Tipo inválido, usar "mostrar todos".
		if(empty($type))
			$type = "all";
			
		// Estableciendo mensaje de estadisticas de BeatRock.
		$finish = (microtime(true) - START);
		self::log('BeatRock tardo ' . substr($finish, 0, 5) . ' segundos en ejecutarse con un uso de ' . round(memory_get_usage() / 1024,1) . ' KB de Memoria.');
		
		// Imprimiendo Logs.
		echo $html ? self::$logs[$type]['html'] : self::$logs[$type]['text'];
	}
	
	// Función privada - Cargar un módulo.
	// - $name: Nombre del modulo.
	private function loadMod($name)
	{
		// Nombre del modulo.
		$mod = "$name.php";
		
		// Si el archivo del modulo existe, cargarlo.
		// De otra manera lanzar un error.
		if(file_exists(MODS . $mod))
			require_once(MODS . $mod);
		else if(file_exists(MODS . 'External' . DS . $mod))
			require_once(MODS . 'External' . DS . $mod);
		else
		{
			self::setStatus("No se ha podido cargar el módulo '$name'.", $name);
			self::launchError('04x');
		}
		
		// Se requiere ejecutar una función/proceso para funcionar.
		if($name == "Codes")
		{
			// Cargar los códigos de error de BeatRock.
			Codes::loadCodes();
		}
			
		self::$modules++;
		self::log("Se ha cargado el módulo $name correctamente.");
	}
	
	// Función - Ha ocurrido un error.
	// Variables de respuesta especificadas por el Callback.
	public static function haveError($num, $msg, $file, $line)
	{
		self::setStatus($msg, $file, Array('line' => $line));
		self::launchError('01x');
		
		return true;
	}
	
	// Función - Ha ocurrido una excepción.
	// Variable de respuesta especificada por el callback.
	public static function haveException($e)
	{
		self::setStatus($e->getMessage(), $e->getfile(), Array('line' => $e->getline()));
		self::launchError('01e');
		
		return true;
	}
	
	// Función - Establecer estado/información de un error.
	// - $response: Mensaje de respuesta.
	// - $file: Archivo responsable.
	// - $data (Array): Más información...
	public static function setStatus($response, $file, $data = Array())
	{
		self::$status['response'] = $response;
		self::$status['file'] = $file;
		
		foreach($data as $param => $value)
			self::$status[$param] = $value;
	}
	
	// Función - Lanzar un error.
	// - $code: Código del error.
	public static function launchError($code)
	{
		// Ignorar el error...
		if(self::$ignore)
		{
			self::$ignore = false;
			return;
		}
		
		// Ya estamos en un error...
		if(self::$inerror)
			return;
		
		// Estamos en un error.
		self::$inerror = true;
		
		// Extraer todas las variables para su uso.
		extract($GLOBALS);
		
		// Obtener la información del código de error.
		$info = Codes::getInfo($code);
		// Más información...
		$res = self::$status;
		
		// Último error recibido.
		$last = error_get_last();
		$res['last'] = "$last[message] en '$last[file]' línea $last[line].";
		
		// Guardar datos "POST" para restaurarlos en la próxima ejecución.
		Client::SavePost();
		
		// El servidor MySQL ya ha sido preparado y no es un error de PHP.
		if(MySQL::isReady() AND $code !== "01x")
		{
			// Al parecer es un error del servidor MySQL.
			// Si no es así, insertar error en la base de datos.
			if($code == "03m")
			{				
				// Optimizar la base de datos.
				if($config['mysql']['optimize.error'] OR $config['errors']['hidden'])
					MySQL::Optimize();
					
				// Reparar la base de datos.
				if($config['mysql']['repair.error'] OR $config['errors']['hidden'])
					MySQL::Repair();
					
				// Modo "Error escondido".
				Core::HiddenError();
			}
			else
			{
				MySQL::query_insert('site_errors', Array(
					'code' => $code,
					'title' => $info['title'],
					'response' => FilterText($res['response']),
					'file' => FilterText($res['file']),
					'function' => $res['function'],
					'line' => $res['line'],
					'out_file' => FilterText($res['out_file']),
					'more' => FilterText(json_encode($res)),
					'date' => time()
				));
			}
		}
		
		// Nombre del archivo de error.
		$e = "Error";

		if(Core::IsMobile())
			$e .= ".Mobile";
			
		// Definir detalles del error.
		self::$details = Array(
			'code' => $code,
			'info' => $info,
			'res' => $res
		);
		
		// Notificar al correo electrónico especificado que ha ocurrido un error.
		$mail_result = Core::sendError();
		
		// Guardar log del error.
		self::log("Ha ocurrido un error: $code - $info[title] - $info[details]", "error");
		
		// Página web no disponible.
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Cache-Control: no-cache');

		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
		
		// Limpiar el buffer de salida actual.
		ob_clean();
		
		// Requiriendo el archivo de Error.
		$page = Tpl::Process(BIT_TEMP . DS . $e);
		
		foreach($info as $param => $value)
			$page = str_ireplace("%$param%", $value, $page);
		
		// Mostrar error y salir.
		exit($page);
	}
	
	// Función - Verificar la carga media del CPU y Memoria.
	public static function CheckLoad()
	{
		global $config;
		
		// Memoria limite para el Script.
		$memory_limit = ini_get("memory_limit");
		
		// Datos inválidos, no queremos verificación de carga.
		if(empty($memory_limit) OR $config['server']['limit'] < 52428800 OR $config['server']['limit_load'] < 30)
			return;
		
		// Definiendo carga de memoria del script y proceso apache actuales.
		$memory_load = memory_get_usage() + 500000;
		$mem_load = Core::memory_usage() + 500000;
		
		// Defininiendo carga del CPU actual.
		$cpu_load = Core::sys_load() + 10;
	
		// La memoria limite del Script esta en MegaBytes, convertir a Bytes.
		if(Contains(ini_get("memory_limit"), "M"))
			$memory_limit = str_replace("M", "", $memory_limit) * 1048576;
		
		// Al parecer el servidor saturado, mostrar página de sobrecarga y salir.
		if($memory_load > $memory_limit OR $mem_load > $config['server']['limit'] OR $cpu_load > $config['server']['limit_load'])
		{
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Cache-Control: no-cache');

			header('HTTP/1.1 503 Service Temporarily Unavailable');
			header('Status: 503 Service Temporarily Unavailable');
			
			// Liberar todas las variables.
			foreach($GLOBALS as $g => $v)	
				unset($g, $v);
				
			echo Tpl::Process(BIT_TEMP . "Overload");
			exit(1);
		}
	}
	
	// Función - Apagado.
	public static function ShutDown()
	{
		// Tiempo que tardo en ejecutarse BeatRock.
		$finish = (microtime(true) - START);
		
		// Enviar estadisticas.	
		self::log('BeatRock tardo ' . substr($finish, 0, 5) . ' segundos en ejecutarse con un uso de ' . round(memory_get_usage() / 1024,1) . ' KB de Memoria.');
		self::log('Se realizaron ' . MySQL::$q . ' consultas durante la sesión actual.', 'mysql');
		self::log('Se cargaron ' . self::$modules . ' módulos durante la sesión actual.');
		
		global $page;
		
		// Queremos preparar una plantilla.
		if(!empty($page['id']) AND empty(Tpl::$html) AND Socket::$server == false)
			Tpl::Load();
			
		// Hay buffer guardado, mostrarlo.
		if(ob_get_length() !== false AND self::$inerror == false)
		{
			if(!empty(Tpl::$html))
				echo Tpl::$html;
			else
				ob_end_flush();
		}
		
		// Guardar caché.
		Tpl::SaveCache();
		
		// Desconectarse del servidor FTP (Si hay).
		Ftp::Crash();
		Socket::Crash();
		
		// Si el servidor MySQL esta listo.
		// Ejecutar cronometros y desconectarse del servidor.
		if(MySQL::isReady())
		{
			if(defined("DEBUG"))
				Site::saveLog();
				
			Site::checkTimers();			
			MySQL::Crash();
		}
		
		// Se han creado archivos temporales, eliminarlos.
		if(!empty(Io::$temp))
		{
			foreach(Io::$temp as $t)
				@unlink($t);
		}		
		
		// Cerrar sesión actual.
		session_write_close();
		
		// Guardar log.
		self::saveLog();
		
		// Descomente la siguiente linea para ver los últimos logs...
		//self::printLog(true);
	}
	
	// Función - Guardar un Backup de toda la aplicación.
	// - $db (Bool) - ¿Incluir un backup de la base de datos?
	public static function Backup($db = false)
	{
		// Nombre del backup.
		$name = BIT . 'Backups' . DS . 'Backup-' . date('d_m_Y') . '-' . time() . '.zip';
		
		// Iniciar el modulo PclZip.
		$a = new PclZip($name);
		// Crear un nuevo archivo ZIP con la aplicación.
		$e = $a->create(ROOT);
		
		// Ha ocurrido un error... :(
		if($e == 0)
			return false;
		
		// Todo bien, ahora incluimos un backup de la base de datos.
		if($db)
		{
			// Ejecutar backup de la DB.
			$b = MySQL::Backup();
			// Ubicación del backup.
			$b = BIT . 'Backups' . DS . $b;
			
			// Agregar la base de datos al backup general.
			Zip::Add($name, $b);
			@unlink($b);
		}
		
		self::log("Se ha creado un Backup total correctamente.");
		return $name;
	}
	
	public static function Statistics()
	{
		// Tiempo que tardo en ejecutarse BeatRock.
		$finish = (microtime(true) - START);
		
		$return = 'BeatRock tardo ' . substr($finish, 0, 5) . ' segundos en ejecutarse con un uso de ' . round(memory_get_usage() / 1024,1) . ' KB de Memoria.<br />';
		$return .= 'Se realizaron ' . MySQL::$q . ' consultas durante la sesión actual.<br />';
		$return .= 'Se cargaron ' . self::$modules . ' módulos durante la sesión actual.<br />';
		
		return $return;
	}
}
?>