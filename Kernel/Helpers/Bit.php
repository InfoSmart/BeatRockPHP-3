<?
/**
 * BeatRock
 *
 * Framework para el desarrollo de aplicaciones web.
 *
 * @author 		Iván Bravo <webmaster@infosmart.mx>
 * @copyright 	InfoSmart 2013. Todos los derechos reservados.
 * @license 	http://creativecommons.org/licenses/by-sa/2.5/mx/  Creative Commons "Atribución-Licenciamiento Recíproco"
 * @link 		http://beatrock.infosmart.mx/
 * @version 	3.0
 *
 * @package 	Bit
 * Se encarga de todo los procesos internos de BeatRock
 * Iniciar, administrar, procesar, apagar...
 *
*/

# Acción ilegal
if( !defined('BEATROCK') )
	exit;

/**
 * Niveles de LOG.
 */
const 	LOG_INFO 		= 1,
		LOG_WARNING 	= 2,
		LOG_ERROR 		= 3,
		LOG_SQL 		= 4,
		LOG_MEMCACHE 	= 5;

class Bit
{
	/**
	 * Información/Estado del error a lanzar.
	 * @var array
	 */
	static $status 		= array();
	/**
	 * Los
	 * @var array
	 */
	static $logs 		= array();
	/**
	 * Archivos necesarios.
	 * @var array
	 */
	static $files 		= array();
	/**
	 * Directorios necesarios.
	 * @var array
	 */
	static $dirs 		= array();
	/**
	 * ¿Estamos procesando/mostrando en un error?
	 * @var boolean
	 */
	static $inerror 	= false;
	/**
	 * ¿Ignorar futuros errores?
	 * @var boolean
	 */
	static $ignore 		= false;
	/**
	 * Detalles/Información del error actual.
	 * @var array
	 */
	static $details 	= array();
	/**
	 * Lista de ayudantes cargados.
	 * @var array
	 */
	static $helpers 	= array();
	/**
	 * Librerías/Funciones necesarias.
	 * @var array
	 */
	static $functions 	= array('curl_init', 'json_decode');

	/**
	 * Constructor
	 */
	function __construct()
	{
		# Verificamos la versión de PHP.
		if ( !version_compare(PHP_VERSION, '5.3.0', '>=') )
			exit('BeatRock no es compatible con esta versión de PHP (' . phpversion() . '). Por favor actualizala a la 5.3 o superior.');

		# Registramos la función LoadHelper encargada de cargar los controladores.
		# y la función Shutdown encargada de apagar y finalizar todo BeatRock.
		spl_autoload_register('Bit::LoadHelper');
		register_shutdown_function('Bit::Shutdown');

		# Registramos las funciones Exception y Error encargadas de mostrar una página de error cuando suceda alguno.
		set_exception_handler('Bit::Exception');
		set_error_handler('Bit::Error', E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

		# Comenzamos la fiesta.
		self::Log('BeatRock ha comenzado.');

		# Registramos los archivos y carpetas necesarias para el funcionamiento de BeatRock.
		self::Register(APP . 				'Setup.Header.php');
		self::Register(APP . 				'Setup.php');
		self::Register(KERNEL_VIEWS_BIT . 	'Error.html');
		self::Register(KERNEL_VIEWS . 		'Header.html');
		self::Register(KERNEL_VIEWS . 		'Footer.html');

		self::Register(BIT, 				true);
		self::Register(KERNEL_VIEWS, 		true);
		self::Register(KERNEL_VIEWS_BIT, 	true);
		self::Register(APP_VIEWS, 			true);
		self::Register(LANGUAGES, 			true);

		# Verificamos que todo este en orden.
		self::VerifyBoot();
	}

	/**
	 * Registra un archivo o directorio necesario para BeatRock.
	 * @param string  $file Ruta del archivo/directorio.
	 * @param boolean $dir  ¿Es un directorio?
	 */
	static function Register($file, $dir = false)
	{
		( $dir ) ? self::$dirs[] = $file : self::$files[] = $file;
	}

	/**
	 * Verifica que los archivos, directos y extensiones necesarios existan.
	 * Lanzara un mensaje de error si alguno de ellos no existe.
	 */
	static function VerifyBoot()
	{
		# Verificamos los directorios.
		foreach( self::$dirs as $DIR )
		{
			# Al parecer un directorio necesario no existe.
			if( !is_dir($DIR) )
				exit('El directorio: <strong>' . $DIR . '</strong> es necesario para el funcionamiento de BeatRock, sin embargo no existe. Intente descargar BeatRock nuevamente.');
		}

		# Verificamos los archivos.
		foreach( self::$files as $FILE )
		{
			# Al parecer un archivo necesario no existe.
			if( !file_exists($FILE) )
				exit('El archivo: <strong>' . $FILE . '</strong> es necesario para el funcionamiento de BeatRock, sin embargo no existe. Intente descargar BeatRock nuevamente.');
		}

		# El archivo de configuración existe, esto quiere decir que ya se ha ejecutado la instalación.
		# !!!FIX Sin esto, si el usuario no tiene Curl o JSON se mostrará la pantalla de error y no se redireccionará a la instalación.
		if( file_exists(APP . 'Configuration.php') )
		{
			# Verificamos las funciones necesarias.
			foreach( $functions as $FUNC )
			{
				# Al parecer alguna función necesaria no existe.
				if( !function_exists($FUNC) )
				{
					# El nombre de la función generalmente es la cadena que esta antes del primer _
					$FUNC_NAME = explode('_', $FUNC);
					$FUNC_NAME = strtoupper($FUNC_NAME[0]);

					self::Status('La librería ' . $FUNC_NAME . ' no se encuentra activada en PHP, esta es necesaria para funcionar, por favor activela para continuar.', '', array('function' => $FUNC));
				}
			}
		}

		# Al parecer ocurrio un error.
		if( !empty(self::$status['response']) )
			self::LaunchError('setup.init');

		# No hace mal borrar las variables.
		self::$files 	= array();
		self::$dirs 	= array();

		# Hemos terminado VerifyBoot()
		self::Log('La verificación de inicio se ha completado.');
	}

	/**
	 * Guardar un log.
	 * @param string $message Mensaje a guardar.
	 * @param string $type    Tipo del log: info (Informativo), warning (Alerta)
	 * error (Error dah), sql (Informativo del servidor SQL), memcache (Informativo del servidor Memcache)
	 */
	static function Log($message, $type = LOG_INFO)
	{
		# Requerimos la variable de configuración.
		global $config;

		# Si el mensaje no es una cadena
		# o se esta ajustado NO GUARDAR LOGS en el archivo de configuración...
		if ( !is_string($message) OR !$config['logs']['capture'] )
			return false;

		# ¿Un tipo inválido?
		if ( $type < 1 OR $type > 5 )
			return false;

		# El ayudante de lenguaje no ha sido cargado, lo necesitamos para la traducción.
		if ( !self::HelperLoaded('Lang') )
			return false;

		# Dependiendo del tipo de log le ajustamos un "prefijo" y un color.
		switch ( $type )
		{
			case LOG_INFO:
				$status = _l('info', '', 'logs');
				$color 	= '#045FB4';
			break;

			case LOG_WARNING:
				$status = _l('alert', '', 'logs');
				$color 	= '#8A4B08';
			break;

			case LOG_ERROR:
				$status = _l('error', '', 'logs');
				$color 	= 'red';
			break;

			case LOG_SQL:
				$status = 'SQL';
				$color 	= '#0B610B';
			break;

			case LOG_MEMCACHE:
				$status = 'Memcache';
				$color 	= '#29088A';
			break;
		}

		# Nos mandan variables de traducción, las traducimos en el idioma del usuario.
		$message = _l($message);

		# Guardamos los logs en formato HTML y TEXT/PLAIN
		$html 	= '<label title="' . date('h:i:s') . '"><b style="color: '.$color.'">['.$status.']</b> - '.$message.'</label><br />';
		$text 	= '['.$status.'] (' . date('h:i:s') . ') - '.$message.'\r\n';

		# Ahora la guardamos en su respectiva variable.
		self::$logs['all']['html'] .= $html;
		self::$logs['all']['text'] .= $text;
		self::$logs[$type]['html'] .= $html;
		self::$logs[$type]['text'] .= $text;
	}

	/**
	 * Guarda los logs en archivos de texto.
	 * Ubicación: /App/BitRock/Logs/
	 * @return string El nombre del archivo generado o false en caso de error.
	 */
	static function SaveLog()
	{
		# Requerimos la variable de configuración.
		global $config;
		$save = $config['logs']['save'];

		# No guardar ningún log.
		if( !$save )
			return false;

		# No se han generado el tipo de logs solicitados.
		# Es decir ¡no hay logs que guardar!
		if( $save !== 'onerror' AND empty(self::$logs[$save]) )
			return false;

		# Solo guardar logs si sucede un error.
		if($save == 'onerror')
		{
			# No ha ocurrido un error.
			if( empty(self::$logs['error']) )
				return false;

			# Guardar TODOS los logs.
			$save = 'all';
		}

		# Nombre que le daremos al archivo.
		$name = 'Logs-' . date('d_m_Y') . '-' . time() . '.txt';
		# Guardamos el log.
		Io::SaveLog($name, self::$logs[$save]['text']);

		# Retornamos el nombre del archivo.
		return $name;
	}

	/**
	 * Imprime los Logs.
	 * @param boolean $html ¿Imprimir en formato de HTML? (Más bonito)
	 * @param string  $type Tipo de logs ha imprimir.
	 */
	static function PrintLog($html = true, $type = 'all')
	{
		# Todos los logs por predeterminado.
		if( empty($type) )
			$type = 'all';

		# Tiempo que nos tomo procesar esto.
		$finish = (microtime(true) - START);

		# Imprimimos los logs solicitados.
		echo ( $html ) ? self::$logs[$type]['html'] : self::$logs[$type]['text'];
	}

	/**
	 * Detecta la ruta de un ayudante y lo carga.
	 * @param string $name Nombre de la clase del ayudante.
	 */
	static function LoadHelper($name)
	{
		# Al parecer el ayudante solicitado ya ha sido cargado.
		if( self::HelperLoaded($name) )
			return;

		# Se trata de un controlador.
		if( substr($name, 0, 5) == 'Ctrl_' )
			return;

		# Requerimos la variable de configuración.
		global $config;

		# Nombre real del ayudante.
		$HELPER 	= $name . '.php';
		# ¿Hemos encontrado al ayudante?
		$found 		= false;

		# Por si acaso...
		if( empty($config['beatrock']['helpers']) )
		{
			$config['beatrock']['helpers'] = array(
				'{APP}Helpers',
				'{KERNEL}Helpers',
				'{KERNEL}Helpers/API',
				'{KERNEL}Helpers/Server',
				'{KERNEL}Helpers/External'
			);
		}

		# Buscar en cada ruta especificada.
		foreach( $config['beatrock']['helpers'] as $path )
		{
			$path = str_ireplace('/', DS, $path);
			$path = Keys($path);

			# ¡Houston, lo hemos encontrado!
			if( file_exists($path . DS . $HELPER) )
			{
				$found = true;
				require $path . DS . $HELPER;
				break;
			}
		}

		# No encontramos al ayudante :(
		if( !$found )
		{
			self::Status('No ha sido posible encontrar al ayudante "' . $name . '".', $name);
			self::LaunchError('bitrock.load.helper');
		}

		# Agregar a la lista.
		self::$helpers[] = $name;

		# Algunos ayudantes necesitan iniciarse.
		# TODO: ¿Mejorar esto?

		if( method_exists($name, '_construct') AND $name !== 'StaticBase' )
			$name::_construct($name);

		if( $name == 'DNS' )
			require_once APP_CTRLS . 'External' . DS . 'SMTPValidate.php';

		if( self::HelperLoaded('Lang') )
		{
			Lang::SetSection('helper.bitrock');
			Reg("%helper.loaded%: $name.");
		}
		else
			Reg("Se ha cargado el ayudante: $name.");
	}

	/**
	 * Verifica si un ayudante ha sido cargadp.
	 * @param string $name Nombre del ayudante.
	 * @return boolean Devuelve true en caso de que se haya cargado o false si no es así.
	 */
	static function HelperLoaded($name)
	{
		return ( in_array($name, self::$helpers) ) ? true : false;
	}

	/**
	 * Controla los errores generados por PHP.
	 * @param int $num     		ID del error
	 * @param string $message 	Mensaje
	 * @param string $file    	Archivo afectado
	 * @param int $line    		Línea causante
	 */
	static function Error($num, $message, $file, $line)
	{
		self::Status($message, $file, array('line' => $line));
		self::LaunchError('php.code');

		return true;
	}

	/**
	 * Controla las excepciones.
	 * @param Exception $e Objeto con información de la excepción.
	 */
	static function Exception($e)
	{
		self::Status($e->getMessage(), $e->getfile(), array('line' => $e->getline()));
		self::LaunchError('php.exception');

		return true;
	}

	/**
	 * Permite establecer información de un error que esta
	 * a punto de ocurrir.
	 * @param string $response 	Mensaje de respuesta.
	 * @param string $file     	Archivo afectado
	 * @param array  $data     	Más información.
	 */
	static function Status($response, $file, $data = array())
	{
		# Generalmente los mensajes de respuesta necesitan ser traducidos.
		self::$status['response'] 	= _l($response);
		self::$status['file'] 		= $file;

		foreach( $data as $key => $value )
			self::$status[$key] = $value;
	}

	/**
	 * ¿Estamos listos para mostrar un error?
	 * @return bool Retorna true en caso de SI o false en caso de que NO.
	 */
	static function Ready4Error()
	{
		return ( empty(self::$status['response']) ) ? false : true;
	}

	/**
	 * Permite ignorar (o dejar de ignorar) el próximo error que ocurra.
	 */
	static function Ignore()
	{
		( self::$ignore == true ) ? self::$ignore = false : self::$ignore = true;
	}

	/**
	 * Mostrar una página de error.
	 * @param string $code Código del error.
	 * @see /App/Languages/es/Codes.json
	 */
	static function LaunchError($code)
	{
		# El ayudante Lang o Codes no se han cargado ¡los necesitamos!
		if( !self::HelperLoaded('Lang') OR !self::HelperLoaded('Codes') )
		{
			# Evitamos la caché y reportamos código 503
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Cache-Control: no-cache');
			header('HTTP/1.1 503 Service Temporarily Unavailable');
			header('Status: 503 Service Temporarily Unavailable');

			# Mostrar el error de forma grotesca, si, tipo IE 6.

			echo '<strong style="color: red">';
			echo "Código de error: $code", "<br />";
			echo self::$status['response'].' en "'.self::$status['file'].'" línea '.self::$status['line'];
			echo '</strong>';

			# Guardamos los logs.
			self::SaveLog();
			exit;
		}

		# Necesitamos todas las variables de PHP.
		extract($GLOBALS);

		# Obtenemos más información del código.
		$info 	= Codes::GetInfo($code);
		# Obtenemos información especial del error.
		$status = self::$status;

		# Obtenemos el último error que PHP genero.
		$last 			= error_get_last();
		$status['last'] = $last['message'].' en "'.$last['file'].'" línea '.$last['line'];

		# ¡Estamos ignorando los errores o ya estamos en uno!
		if( self::$ignore OR self::$inerror )
		{
			# Enviamos un correo electrónico de reporte.
			Core::SendError();
			return;
		}

		# Guardamos los $_POST de esta sesión.
		Client::SavePost();

		# No es un error PHP y
		# el ayudante SQL ha sido cargado.
		if( $code !== 'php.code' AND self::HelperLoaded('SQL') )
		{
			# MySQL o SQLite reportan estar listos.
			if( SQL::Connected() )
			{
				# Un error relacionado a una consulta.
				if( $code == 'sql.query' )
				{
					# Intentamos reparar la base de datos.
					if( $config['sql']['repair.error'] )
						SQL::Repair();

					# Ocultamos el error.
					if( $config['errors']['hide'] )
						Core::HideError();
				}
				else
				{
					# Generamos un código de reporte.
					$report_code = Core::Random(10);

					# Lo insertamos en la tabla site_errors
					$result = SQL::Insert('site_errors', array(
						'report_code' 	=> $report_code,
						'code' 			=> $code,
						'title' 		=> $info['title'],
						'response' 		=> _f($status['response']),
						'file' 			=> _f($status['file'], false),
						'function' 		=> $res['function'],
						'line' 			=> $res['line'],
						'out_file' 		=> _f($status['out_file']),
						'more' 			=> _f(json_encode($status), false),
						'date' 			=> time()
					));
				}
			}
		}

		# Juntamos la información de una sola variable.
		self::$details = array(
			'report_code' 	=> $report_code,
			'code' 			=> $code,
			'info' 			=> $info,
			'res' 			=> $status
		);

		# Guardamos un log de error.
		self::Log('%error.ocurred%: ' . $code . ' - ' . $info['title'] . ' - ' . $info['details'], LOG_ERROR);
		self::$inerror = true;

		# Limpiamos el buffer de salida.
		ob_clean();

		# Evitamos la caché y reportamos código 503
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Cache-Control: no-cache');
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');

		# Esta página esta siendo cargada con AJAX.
		if( $page['ajax'] == true OR $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' AND $page['ajax'] !== false )
		{
			# Devolvemos el error en JSON.
			$data = array('system_error' => self::$details);
			$data = json_encode(_c($data));

			exit($data);
		}

		# Evitamos el uso de Jade.
		global $site;
		$site['site_jade'] = false;

		# Cargamos la plantilla de error.
		$html = new View('bitrock' . DS . 'Error');
		# La traducimos.
		$html = _l($html, LANG, 'page.error');

		# Reemplazamos algunas variables.
		foreach( $info as $param => $value )
			$html = str_ireplace("%$param%", $value, $html);

		# Imprimimos HTML
		exit($html);
	}

	/**
	 * Verifica la carga actual del CPU y la memoria.
	 */
	static function CheckLoad()
	{
		# Requerimos la configuraión del sitio.
		global $site;
		# Próxima verificación programada.
		$last = $_SESSION['load_verify'];

		# Aún no es tiempo de verificar.
		if( time() < $last AND !empty($last) )
			return;

		# Iniciamos las variables.
		$memory_limit 	= ini_get('memory_limit');
		$memory_load 	= 0;
		$apache_load 	= 0;
		$cpu_load 		= 0;

		# No esta vacia el parametro memory_limit de PHP.
		if( !empty($memory_limit) )
		{
			# Uso de memoria actual.
			$memory_load = memory_get_usage() + 500000;

			# Establecemos el limite de memoria.
			if( Contains($memory_limit, 'M') )
				$memory_limit = round(str_replace('M', '', $memory_limit) * 1048576);
		}

		# Memoria usada por Apache.
		$apache_load = Core::memory_usage() + 500000;

		# Carga actual del CPU.
		$cpu_load = Core::sys_load() + 10;

		# Próxima verificación en 3 minutos.
		$_SESSION['load_verify'] = (time() + (3 * 60));

		Lang::SetSection('helper.bitrock');
		Reg("%checkload.verify%: %checkload.ram%: $memory_load KB - %checkload.apache.ram%: $apache_load KB - CPU: $cpu_load%");

		# Todo parece indicar que estamos sobrecargados.
		if( $memory_load > $memory_limit OR
			($site['apache_limit'] >= 52428800 AND $apache_load > $site['apache_limit']) OR
			($site['cpu_limit'] >= 50 AND $cpu_load > $site['cpu_limit']) )
		{
			global $page;

			# Evitamos la caché y reportamos error 503.
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Cache-Control: no-cache');
			header('HTTP/1.1 503 Service Temporarily Unavailable');
			header('Status: 503 Service Temporarily Unavailable');

			# Liberamos un poco de memoria.
			# TODO: ¿Esto hace algo realmente?
			foreach( $GLOBALS as $g => $v )
				unset($g, $v);

			# Cargamos la plantilla de sobrecarga.
			$view = new View(KERNEL_VIEWS_BIT . 'Overload');
			$view->AddLang(LANG, array('global', 'page.overload'));

			exit($view);
		}
	}

	/**
	 * Guarda la información necesaria del sistema de Recuperación
	 * Inteligente.
	 */
	static function SmartBackup()
	{
		# Requerimos la variable de configuración.
		global $config;
		# Próxima recuperación.
		$back = _CACHE('backup_time');

		# Guardamos una recuperación.
		if( $config['server']['backup'] AND ( empty($back) OR time() > $back ) )
		{
			# Archivo de configuración.
			_CACHE('backup_config', Io::Read(APP . 'Configuration.php'));
			# Base de datos.
			_CACHE('backup_db', 	SQL::Backup('', true));
			# Próxima recuperación en 30 min.
			_CACHE('backup_time', 	Core::Time(30, 2));
		}
		else if( !$config['server']['backup'] )
		{
			# Eliminamos toda recuperación guardada.
			_DELCACHE('backup_config');
			_DELCACHE('backup_db');
			_DELCACHE('backup_time');
		}
	}
	/**
	 * Realiza los procesos necesarios para apagar BeatRock.
	 */
	static function ShutDown()
	{
		$finish = (microtime(true) - START);

		self::log('BeatRock tardo ' . substr($finish, 0, 5) . ' segundos en ejecutarse con un uso de ' . round(memory_get_usage() / 1024,1) . ' KB de Memoria.');

		if( self::HelperLoaded('SQL') )
			self::log('Se realizaron ' . SQL::$querys . ' consultas durante la sesión actual.', 'sql');

		self::log('Se cargaron ' . count(self::$helpers) . ' ayudantes durante la sesión actual.');

		# Requerimos la configuración del sitio.
		global $page;

		# Al parecer necesitamos cargar una plantilla.
		if( !empty($page['id']) AND empty(Tpl::$html) )
			Tpl::Load();

		# Si no estamos en un error
		# y el HTML de la plantilla existe.
		if( self::$inerror == false AND !empty(Tpl::$html) )
		{
			# Guardamos en caché la plantilla.
			Tpl::$html->SaveCache();
			# Imprimimos el HTML.
			echo Tpl::$html;
		}

		# Se ha cargado el controlador SQL.
		if( self::HelperLoaded('SQL') )
		{
			# Y esta preparado.
			if( SQL::Connected() )
			{
				# Ejecutamos los cronometros.
				Site::CheckTimers();
				# Guardamos los logs
				Site::SaveLog();
				# Destruimos la conexión al servidor (MySQL)
				SQL::Disconnect();
			}
		}

		# Hay archivos temporales por borrar.
		if( !empty(Io::$temp) )
		{
			# Borrar, borrar, borrar...
			foreach( Io::$temp as $t )
				@unlink($t);
		}

		# Guardamos los logs
		self::SaveLog();

		# Cerramos la sesión.
		session_write_close();

		# Descomente la siguiente linea para ver los últimos logs...
		#self::PrintLog(true);
	}

	/**
	 * Guarda un Backup de TODA la aplicación.
	 * @param boolean $db ¿Incluir un backup de la base de datos?
	 * @return string El nombre del archivo .zip
	 */
	static function Backup($db = false)
	{
		# Requerimos la configuración del sitio.
		global $site;

		# Ubicación de los backups.
		$path = BIT . 'Backups' . DS;
		# Nombre que le daremos al backup.
		$name = 'Backup-' . date('d_m_Y') . '-' . time() . '.zip';

		# Iniciamos y creamos el archivo ZIP.
		$zip = new Zip($path . $name);
		$zip->AddDir(ROOT, '/');

		# También guardar un backup de la base de datos.
		if( $db )
		{
			$b = SQL::Backup();
			$b = BIT . 'Backups' . DS . $b;

			$zip->Add($b);
			unlink($b);
		}

		# Enviar este backup a los servidores de recuperación.
		if( $site['site_backups_servers'] == 'true' )
			Bit::FTPBackup($path . $name, $name);

		self::Log('Se ha creado un Backup completo correctamente.');
		return $name;
	}

	/**
	 * Envia un Backup a los servidores de recuperación FTP.
	 * @param string $file     Ruta del Backup remoto.
	 * @param string $filename Nombre del Backup Remoto.
	 */
	static function FTPBackup($file, $filename)
	{
		# Obtenemos los servidores de recuperación.
		$servers = Site::Get('backups_servers');

		while( $row = Assoc() )
		{
			$folder = 'Backup-' . date('d_m_Y');
			$ftp 	= new Ftp($row['host'], $row['username'], $row['password'], $row['port']);

			# Irnos a un directorio remoto.
			if( !empty($row['directory']) )
			{
				self::$ignore = true;
				$ftp->ToDir($row['directory']);
			}

			# Subimos el Backup.
			$ftp->NewDir($folder);
			$ftp->ToDir($folder);
			$ftp->Upload($file, $filename);
		}

		self::$ignore = false;
	}

	/**
	 * Imprime las estadísticas.
	 */
	static function Statistics()
	{
		global $constants;

		$finish 	= (microtime(true) - START);
		$finish 	= substr($finish, 0, 5);

		$memory 	= round(memory_get_usage() / 1024 / 1024,1);
		$modules 	= count(self::$helpers);

		$variables  = $GLOBALS;
		$variables 	= count($variables);

		$functions 	= get_defined_functions();
		$functions 	= count($functions['user']);

		$constant 	= count($constants);
		$files 		= get_included_files();

		$return = 'BeatRock tardo ' .$finish. ' segundos en ejecutarse con un uso de ' .$memory. ' MB de memoria.<br />';
		$return .= 'Se han cargado ' .$modules. ' ayudantes durante la sesión actual.<br />';
		$return .= 'Se han establecido ' .$variables. ' variables, '.$constant.' constantes y ' .$functions.' funciones durante la ejecución actual.<br />';

		if( self::HelperLoaded('BaseSQL') )
			$return .= 'Se realizaron ' . SQL::$querys . ' consultas SQL durante la ejecución actual.<br />';

		$return .= '<br />Los siguientes archivos (' . count($files) . ') se han cargado:<br />';

		$files 	= Core::SplitArray($files);

		foreach($files as $file)
			$return .= $file;

		return $return;
	}
}
?>