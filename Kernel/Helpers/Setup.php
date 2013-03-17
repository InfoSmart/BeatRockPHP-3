<?php
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

# Acción ilegal.
if( !defined('BEATROCK') )
	exit;

###############################################################
## Ayudante Setup
###############################################################
## Procesa el archivo de configuración y define las opciones
## técnicas a partir del mismo.
###############################################################

class Setup
{
	###############################################################
	## Constructor
	## Verificamos y preparamos el archivo de configuración.
	###############################################################
	function __construct()
	{
		Lang::SetSection('helper.setup');

		# Al parecer el archivo de configuración no existe.
		if( !file_exists(APP . 'Configuration.php') )
		{
			# Intentamos restaurarlo.
			$result = self::Restore();

			# La restauración fallo (quizá es el primer inicio)
			if( !$result )
			{
				# Detectamos si existe el directorio de instalación.
				if( file_exists('./Setup' . DS . 'index.php') )
					Core::Redirect('./Setup/');

				else if( file_exists('../Setup' . DS . 'index.php') )
					Core::Redirect('../Setup/');

				# ¡No hay configuración ni instalación!
				else
					Bit::LaunchError('setup.init');
			}
		}

		# Limpiamos la caché de archivos y carpetas.
		clearstatcache();

		# Intentamos asignar los permisos recomendados.
		if( !is_writable(BIT . 'Backups') )
			chmod(BIT . 'Backups', 0777);

		if( !is_writable(BIT . 'Logs') )
			chmod(BIT . 'Logs', 0777);

		if( !is_writable(BIT . 'Temp') )
			chmod(BIT . 'Temp', 0777);

		if( !is_writable(BIT . 'Cache') )
			chmod(BIT . 'Temp', 0777);

		global $config;
		require APP . 'Configuration.php';

		# Verificamos y aplicamos la configuración.
		self::Verify();
		self::Apply();
	}

	###############################################################
	## ¿El servidor y cliente soportan la compresión GZIP?
	###############################################################
	static function SupportGzip()
	{
		global $Kernel;

		if( extension_loaded('zlib') AND
			(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) AND
			$Kernel['gzip'] !== false )
		{
			return true;
		}

		return false;
	}

	###############################################################
	## Verifica que la configuración sea correcta y segura.
	###############################################################
	static function Verify()
	{
		global $config;

		# Reiniciamos cualquier error anterior.
		Bit::$status 	= array();
		$file 			= APP . 'Configuration.php';

		$sql 		= $config['sql'];
		$site 		= $config['site'];
		$security 	= $config['security'];

		# La conexión SQL es MySQL
		if( $sql['type'] == 'mysql' )
		{
			# Si el:
			# - Host de conexión
			# - Nombre de usuario
			# - Contraseña
			# - Nombre de la base de datos.
			# Estan vacios... ¿como carajo te conectaras al servidor?
			if( empty($sql['host']) OR empty($sql['user']) OR empty($sql['pass']) OR empty($sql['name']) )
				Bit::Status('%error.config.mysql%', $file);

			# El nombre de usuario es "root" o "admin"
			# Espero que no seas del personal de seguridad informatica...
			if( $sql['user'] == 'root' OR $sql['user'] == 'admin' )
				Bit::Log('%error.config.muser%', 'warning');
		}

		# La conexión SQL es SQLite
		else if( $sql['type'] == 'sqlite' )
		{
			# La ruta a la base de datos esta vacia.
			if( empty($sql['name']) )
				Bit::Status('%error.config.sqlite%', $file);
		}

		# La ruta hacia la web o los recursos esta vacia.
		if( empty($site['path']) OR empty($site['resources']) )
			Bit::Status('%error.config.path%', $file);

		# El nivel de encriptación no es válido.
		if( $security['level'] < 0 OR $security['level'] > 5 )
			Bit::Status('%error.config.level%', $file);

		# La llave de encriptación esta vacia.
		if( empty($security['hash']) )
			Bit::Log('%error.config.hash%', 'warning');

		# Ocurrio un error.
		if( Bit::Ready4Error() )
			Bit::LaunchError('setup.config');
	}

	###############################################################
	## Aplicamos la configuración.
	###############################################################
	static function Apply()
	{
		global $config, $page, $Kernel;
		$site = $config['site'];

		if ( defined('SSL') )
		{
			# Estamos accediendo con https:// (SSL)
			if(SSL == 'on')
			{
				# Evitamos errores internos por usar SSL
				error_reporting(E_CORE_ERROR & E_RECOVERABLE_ERROR);

				# La aplicación no permite acceder con SSL
				# Espero que por una razón lógica...
				if( $config['server']['ssl'] == false )
				{
					# Guardamos la información en $_POST
					Client::SavePost();
					# Redireccionamos a la misma página pero en http://
					Core::Redirect('http://' . URL);
				}

				$protocol = 'https://';
				Bit::Log('%using.ssl%');
			}

			# Estamos accediendo con http:// (Normal)
			else
			{
				# La aplicación requiere que accedamos con https:// (SSL)
				# ¡Yey!
				if( $config['server']['ssl'] == true )
				{
					# Guardamos la información en $_POST
					Client::SavePost();
					# Redireccionamos a la misma página pero en https://
					Core::Redirect('https://' . URL);
				}

				$protocol = 'http://';
				Bit::Log('%using.http%');
			}
		}

		# La aplicación o la página actual piden usar la compresión GZIP.
		if( $config['server']['gzip'] AND ($page['gzip'] !== false OR $page['gzip'] == true) AND $Kernel['gzip'] !== false )
		{
			# El servidor y cliente soportan Gzip
			if( self::SupportGzip() )
			{
				ob_start('ob_gzhandler');
				Bit::Log('%using.gzip%');
			}

			# El servidor y/o cliente no soportan GZIP, intentar con Zlib y compresión
			# de HTML. :itssomething:
			else
			{
				ini_set('zlib.output_compression', 'On');
				ob_start('Core::Compress');
			}
		}
		else
			ob_start();

		# Establecemos la zona horaria del servidor.
		if( !empty($site['timezone']) )
			date_default_timezone_set($site['timezone']);

		# Establecemos las rutas hacia la web, recursos, administración, etc..

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

	###############################################################
	## Intentar restaurar el archivo de configuración
	## ¿Un accidente? ¿Un Hacker? ¡BeatRock es inteligente!
	###############################################################
	static function Restore()
	{
		Bit::Log('%config.try.backup%', 'warning');

		# El archivo de recuperación existe, usarlo.
		if( file_exists(APP . 'Configuration.Backup.php') )
			return Io::Copy(APP . 'Configuration.Backup.php', APP . 'Configuration.php');

		# Obtenemos el backup directamente del sistema de recuperación inteligente.
		$backup = _CACHE('backup_config');

		# ¡No hay ningún backup! Fallamos...
		if( empty($backup) )
			return false;

		Bit::Log('%config.try.user%');
		return Io::Write(APP . 'Configuration.php', $backup);
	}
}
?>