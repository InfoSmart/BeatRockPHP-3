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

class Setup
{	
	// Función - Inicializar BeatRock.
	static function Init()
	{
		Lang::SetSection('mod.setup');

		if(!file_exists(KERNEL . 'Configuration.json'))
		{
			$result = self::RestoreBackup();
			
			if(!$result)
			{
				if(file_exists('./Setup' . DS . 'index.php'))
					Core::Redirect('./Setup/');

				else if(file_exists('../Setup' . DS . 'index.php'))
					Core::Redirect('../Setup/');

				else
					BitRock::LaunchError('03x');
			}
		}
		
		if(PHP_OS !== 'WINNT')
		{
			clearstatcache();

			if(!is_writable(BIT . 'Backups'))
				@chmod(BIT . 'Backups', 0777);
				
			if(!is_writable(BIT . 'Logs'))
				@chmod(BIT . 'Logs', 0777);
				
			if(!is_writable(BIT . 'Temp'))
				@chmod(BIT . 'Temp', 0777);
				
			if(!is_writable(BIT . 'Cache'))
				@chmod(BIT . 'Temp', 0777);
		}
		
		$config = Core::LoadJSON(KERNEL . 'Configuration.json');

		self::Verify($config);		
		self::Apply($config);
		
		return $config;
	}
	
	// Función - ¿Soportamos GZIP?
	static function SupportGzip()
	{
		global $Kernel;
		
		if(extension_loaded('zlib') AND (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE) AND $Kernel['gzip'] !== false)
			return true;

		return false;
	}

	// Función privada - Verificar configuración.
	// - $config: Configuración.
	static function Verify($config)
	{
		BitRock::$status 	= array();
		$file 				= KERNEL . 'Configuration.json';
		
		$mysql 		= $config['mysql'];
		$site 		= $config['site'];
		$security 	= $config['security'];
		
		if(empty($mysql['host']) OR empty($mysql['user']) OR empty($mysql['pass']) OR empty($mysql['name']))
			BitRock::SetStatus('%error.config.mysql%', $file);

		else if(empty($site['path']) OR empty($site['resources']))
			BitRock::SetStatus('%error.config.path%', $file);

		else if($security['level'] < 0 OR $security['level'] > 5)
			BitRock::SetStatus('%error.config.level%', $file);
		
		if($mysql['user'] == 'root' OR $mysql['user'] == 'admin')
			BitRock::Log('%error.config.muser%', 'warning');

		if(empty($security['hash']))
			BitRock::Log('%error.config.hash%', 'warning');
			
		if(!empty(BitRock::$status['response']))
			BitRock::LaunchError('setup.config');
	}
	
	// Función privada - Aplicar configuración.
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
			BitRock::Log('%using.ssl%');
		}
		else
		{
			if($config['server']['ssl'] == true)
			{
				Client::SavePost();				
				Core::Redirect('https://' . URL);
			}
			
			$protocol = 'http://';
			BitRock::Log('%using.http%');
		}
		
		if($config['server']['gzip'] AND $page['gzip'] !== false OR $page['gzip'] == true)
		{
			if(self::SupportGzip())
			{
				ob_start('ob_gzhandler');
				BitRock::Log('%using.gzip%');
			}
			else
			{
				ini_set('zlib.output_compression', 'On');
				ob_start('Core::Compress');
			}
		}
		else		
			ob_start();
		
		if(is_integer($config['errors']['report']))
			error_reporting($config['errors']['report']);
		
		if(!empty($site['country']))
			date_default_timezone_set($site['country']);
		
		define('PATH', 		$protocol . $site['path']);
		define('PATH_NS', 	'http://' . $site['path']);
		define('PATH_SSL', 	'https://' . $site['path']);
		
		define('RESOURCES', 	$protocol . $site['resources']);
		define('RESOURCES_SYS', $protocol . $site['resources.sys']);
		
		define('ADMIN', 	$protocol . $site['admin']);
		define('DB_PREFIX', $config['mysql']['alias']);
		
		define('PROTOCOL', $protocol);
		define('PATH_NOW', PROTOCOL . URL);
		
		BitRock::Log('%config.apply%');
	}

	// Función privada - Restaurar el archivo de configuración.
	static function RestoreBackup()
	{
		BitRock::Log('%config.try.backup%', 'warning');
		
		if(file_exists(KERNEL . 'Configuration.Backup.json'))
			return Io::Copy(KERNEL . 'Configuration.Backup.json', KERNEL . 'Configuration.json');
		
		$data = Core::TheCache('backup_config');
			
		if(empty($data))
			return false;
			
		BitRock::Log('%config.try.user%');
		return Io::Write(KERNEL . 'Configuration.json', $data);
	}
}
?>