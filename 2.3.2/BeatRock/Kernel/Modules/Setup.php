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

class Setup
{
	// Función privada - Restaurar el archivo de configuración.
	private static function restoreBackup()
	{
		BitRock::log("Se ha detectado que el archivo de configuración no existe, se intentará restaurar automáticamente.", "warning");
		
		// El Backup del archivo de configuración existe, restaurarlo desde este.
		if(file_exists(KERNEL . 'Configuration.Backup.php'))
			return Io::Copy(KERNEL . 'Configuration.Backup.php', KERNEL . 'Configuration.php');
		
		// Obtener la copia de configuración.
		$ab = Core::theSession("backup_config");
			
		// Esta vacia.
		if(empty($ab))
			return false;
			
		// Restaurar archivo de configuración.
		BitRock::log("El archivo de configuración de recuperación no existe, pero se ha restaurado desde la información de recuperación del usuario.");
		return Io::Write(KERNEL . 'Configuration.php', $ab);
	}
	
	// Función - Inicializar.
	public static function Init()
	{	
		// El archivo de configuración no existe.
		if(!file_exists(KERNEL . 'Configuration.php'))
		{
			// Intentando restaurarlo.
			$result = self::restoreBackup();
			
			// Ha fallado la restauración.
			if(!$result)
			{
				// La instalación existe, redireccionar a la instalación.
				// Si no, lanzar error.
				if(file_exists('./Setup' . DS . 'index.php'))
					Core::Redirect("./Setup/");
				else if(file_exists('../Setup' . DS . 'index.php'))
					Core::Redirect("../Setup/");
				else
					BitRock::launchError('03x');
			}
		}
		
		// Verificar los permisos de las carpetas de Backups, Logs, Temporal y Cache.
		// Solo si no estamos en Windows.
		if(PHP_OS !== "WINNT")
		{
			if(!is_writable(BIT . 'Backups'))
				@chmod(BIT . 'Backups', 0777);
				
			if(!is_writable(BIT . 'Logs'))
				@chmod(BIT . 'Logs', 0777);
				
			if(!is_writable(BIT . 'Temp'))
				@chmod(BIT . 'Temp', 0777);
				
			if(!is_writable(BIT . 'Cache'))
				@chmod(BIT . 'Temp', 0777);
		}
		
		// Implementando el archivo de configuración.
		require_once(KERNEL . 'Configuration.php');		
		
		// Verificando y aplicando la configuración.
		self::verifyConfig($config);		
		self::applyConfig($config);
		
		// Devolver los datos de la configuración.
		return $config;
	}
	
	// Función privada - ¿Soportamos GZIP?
	private static function supportGzip()
	{
		global $Kernel;
		
		// El servidor no soporta la compresión.
		if(empty($_SERVER['HTTP_ACCEPT_ENCODING']))
			return false;
		// Ya hay un módulo de compresión activado.
		else if(ini_get('zlib.output_compression') == 'On' OR ini_get('zlib.output_compression_level') > 0 OR ini_get('output_handler') == 'ob_gzhandler')
			return false;
		// ¡Si aceptamos GZIP!
		else if(extension_loaded('zlib') AND (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE) AND $Kernel['gzip'] !== false)
			return true;
		else
			return false;
	}
	
	// Función privada - Aplicar configuración.
	// - $config: Configuración.
	private static function applyConfig($config)
	{
		// Configuración del sitio.
		$site = $config['site'];
		
		// Estamos usando el protocolo de conexión segura.
		if(SSL == "on")
		{
			// Para evitar errores fatales del navegador...
			error_reporting(E_CORE_ERROR & E_RECOVERABLE_ERROR);
			
			// Forzar el NO uso del protocolo seguro.
			if($config['server']['ssl'] == false AND $config['server']['ssl'] !== null)
			{
				// Guardar datos "POST" para restaurarlos en la próxima ejecución.
				Client::SavePost();
				// Redireccionar al procolo normal.
				Core::Redirect("http://" . URL);
			}
			
			// Definir el protocolo.
			$protocol = "https://";
			BitRock::log('Se esta usando el procolo de conexión segura.');
		}
		else
		{
			// Forzar el uso del protocolo seguro.
			if($config['server']['ssl'] == true)
			{
				// Guardar datos "POST" para restaurarlos en la próxima ejecución.
				Client::SavePost();				
				// Redireccionar al procolo seguro.
				Core::Redirect("https://" . URL);
			}
			
			// Definir el protocolo.
			$protocol = "http://";
			BitRock::log('Se esta usando el procolo de conexión normal.');
		}
		
		// Queremos compresión GZIP.
		if($config['server']['gzip'] AND $page['gzip'] !== false OR $page['gzip'] == true)
		{
			// Si el servidor soporta la compresión GZIP, activarlo.
			// De otra manera activar la compresión ZLIB y comprimir HTML.
			if(self::supportGzip())
			{
				ob_start('ob_gzhandler');
				BitRock::log("Se esta usando la compresión GZIP ¡Perfecto!");
			}
			else
			{
				ini_set('zlib.output_compression', 'On');
				ob_start('Core::Compress');
			}
		}
		// De otra forma capturar buffer de salida.
		else		
			ob_start();
		
		// Definiendo el reporte de errores.
		if(is_integer($config['errors']['report']))
			error_reporting($config['errors']['report']);
		
		// Definiendo la zona horaria del servidor.
		if(!empty($site['country']))
			date_default_timezone_set($site['country']);
		
		// Definiendo ubicaciones web.
		define("PATH", $protocol . $site['path']);
		define("PATH_NS", "http://$site[path]");
		define("PATH_SSL", "https://$site[path]");
		
		define("RESOURCES", $protocol . $site['resources']);
		define("RESOURCES_SYS", $protocol . $site['resources.sys']);
		
		define("ADMIN", $protocol . $site['admin']);
		define("DB_ALIAS", $config['mysql']['alias']);
		
		// Definiendo protocolo usado y dirección actual.
		define("PROTOCOL", $protocol);
		define("PATH_NOW", PROTOCOL . URL);
		BitRock::log('Se han aplicado las condiciones del archivo de configuración con éxito.');
	}
	
	// Función privada - Verificar configuración.
	// - $config: Configuración.
	private static function verifyConfig($config)
	{
		// Estableciendo estado.
		BitRock::$status = Array();
		$file = KERNEL . 'Configuration.php';
		
		// Definiendo secciones de la configuración.
		$mysql = $config['mysql'];
		$site = $config['site'];
		$security = $config['security'];
		
		// Configuración para la conexión MySQL inválida.
		if(empty($mysql['host']) OR empty($mysql['user']) OR empty($mysql['pass']) OR empty($mysql['name']))
			BitRock::setStatus('Los datos para la conexión al servidor MySQL no estan completos.', $file);
		// Configuración de ubicación web inválida.
		else if(empty($site['path']) OR empty($site['resources']))
			BitRock::setStatus('Los datos de ubicación de la aplicación no estan completos.', $file);
		// Configuración de encriptación inválida.
		else if($security['level'] < 0 OR $security['level'] > 5)
			BitRock::setStatus('El nivel de codificación es inválido.', $file);
		
		// No es recomendable... >.<
		if($mysql['user'] == "root" OR $mysql['user'] == "admin")
			BitRock::log('Por seguridad es recomendable que cambie el usuario para la conexión al servidor MySQL.', 'warning');
		// ¿Sin palabra de encriptación? >.>
		if(empty($security['hash']))
			BitRock::log('No se ha definido una cadena de seguridad para la codificación, por favor defina alguna para aumentar el nivel de seguridad.', 'warning');
			
		// Lanzar error en caso de haber.
		if(!empty(BitRock::$status['response']))
			BitRock::launchError('03x');
	}
}
?>