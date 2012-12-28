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

class Setup
{	
	// Inicializar la preparación.
	function __construct()
	{
		Lang::SetSection('mod.setup');

		if(!file_exists(APP . 'Configuration.php'))
		{
			$result = self::Restore();
			
			if(!$result)
			{
				if(file_exists('./Setup' . DS . 'index.php'))
					Core::Redirect('./Setup/');

				else if(file_exists('../Setup' . DS . 'index.php'))
					Core::Redirect('../Setup/');

				else
					Bit::LaunchError('setup.init');
			}
		}
		
		clearstatcache();

		if(!is_writable(BIT . 'Backups'))
			@chmod(BIT . 'Backups', 0777);
				
		if(!is_writable(BIT . 'Logs'))
			@chmod(BIT . 'Logs', 0777);
				
		if(!is_writable(BIT . 'Temp'))
			@chmod(BIT . 'Temp', 0777);
				
		if(!is_writable(BIT . 'Cache'))
			@chmod(BIT . 'Temp', 0777);
		
		global $config;
		require APP . 'Configuration.php';

		self::Verify($config);		
		self::Apply($config);

		if($config['security']['antiddos'] == true)
			self::Protect();
	}
	
	// ¿Soportamos GZIP?
	static function SupportGzip()
	{
		global $Kernel;
		
		if(extension_loaded('zlib') AND (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE) AND $Kernel['gzip'] !== false)
			return true;

		return false;
	}

	// Verificar configuración.
	// - $config: Configuración.
	static function Verify($config)
	{
		Bit::$status 	= array();
		$file 			= APP . 'Configuration.php';
		
		$sql 		= $config['sql'];
		$site 		= $config['site'];
		$security 	= $config['security'];
		
		if($sql['type'] == 'mysql')
		{
			if(empty($sql['host']) OR empty($sql['user']) OR empty($sql['pass']) OR empty($sql['name']))
				Bit::Status('%error.config.mysql%', $file);

			if($sql['user'] == 'root' OR $sql['user'] == 'admin')
				Bit::Log('%error.config.muser%', 'warning');
		}
		
		else if($sql['type'] == 'sqlite')
		{
			if(empty($sql['name']))
				Bit::Status('%error.config.sqlite%', $file);
		}

		else
			Bit::Status('%error.config.sqltype%', $file);

		if(empty($site['path']) OR empty($site['resources']))
			Bit::Status('%error.config.path%', $file);

		else if($security['level'] < 0 OR $security['level'] > 5)
			Bit::Status('%error.config.level%', $file);

		if(empty($security['hash']))
			Bit::Log('%error.config.hash%', 'warning');
			
		if(!empty(Bit::$status['response']))
			Bit::LaunchError('setup.config');
	}
	
	// Aplicar configuración.
	// - $config: Configuración.
	static function Apply($config)
	{
		global $page;
		$site = $config['site'];
		
		if(SSL == 'on')
		{
			error_reporting(E_CORE_ERROR & E_RECOVERABLE_ERROR);
			
			if($config['server']['ssl'] == false)
			{
				Client::SavePost();
				Core::Redirect('http://' . URL);
			}
			
			$protocol = 'https://';
			Bit::Log('%using.ssl%');
		}
		else
		{
			if($config['server']['ssl'] == true)
			{
				Client::SavePost();				
				Core::Redirect('https://' . URL);
			}
			
			$protocol = 'http://';
			Bit::Log('%using.http%');
		}
		
		if($config['server']['gzip'] AND $page['gzip'] !== false OR $page['gzip'] == true)
		{
			if(self::SupportGzip())
			{
				ob_start('ob_gzhandler');
				Bit::Log('%using.gzip%');
			}
			else
			{
				ini_set('zlib.output_compression', 'On');
				ob_start('Core::Compress');
			}
		}
		else		
			ob_start();
		
		if(!empty($site['timezone']))
			date_default_timezone_set($site['timezone']);
		
		define('PATH', 		$protocol . $site['path']);
		define('PATH_NS', 	'http://' . $site['path']);
		define('PATH_SSL', 	'https://' . $site['path']);
		
		define('RESOURCES', 		$protocol . $site['resources']);
		define('RESOURCES_GLOBAL', 	$protocol . $site['resources.global']);
		
		define('ADMIN', 	$protocol . $site['admin']);
		define('DB_PREFIX', $config['sql']['prefix']);
		
		define('PROTOCOL', $protocol);
		define('PATH_NOW', PROTOCOL . URL);
		
		Bit::Log('%config.apply%');
	}

	// Restaurar el archivo de configuración.
	static function Restore()
	{
		Bit::Log('%config.try.backup%', 'warning');
		
		if(file_exists(APP . 'Configuration.Backup.php'))
			return Io::Copy(APP . 'Configuration.Backup.php', APP . 'Configuration.php');
		
		$backup = _CACHE('backup_config');
			
		if(empty($backup))
			return false;
			
		Bit::Log('%config.try.user%');
		return Io::Write(APP . 'Configuration.php', $backup);
	}

	// Proteger el sitio AntiDDOS.
	static function Protect()
	{
		$blackip = Core::LoadJSON(ROOT . 'black_ip.json');

		if(is_numeric(array_search(IP, $blackip)))
		{
			header('HTTP/1.0 503 Service Temporarily Unavailable');
			header('Connection: close');
			exit;
		}
	}
}
?>