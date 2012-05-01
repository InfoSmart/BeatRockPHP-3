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

class Ftp
{
	private static $connection = null;
	private static $host = "";
	private static $username = "";
	private static $password = "";
	private static $port = 21;
	private static $ssl = false;
	private static $pasv = true;
	
	// Función privada - Lanzar error.
	// - $function: Función causante.
	// - $msg: Mensaje del error.
	private static function Error($code, $function, $msg = '')
	{
		BitRock::setStatus($msg, __FILE__, Array('function' => $function));
		BitRock::launchError($code);
	}
	
	// Función privada - ¿Hay alguna conexión activa?
	// - $e: ¿Lanzar error en caso de que no haya una conexión activa?
	private static function isReady($e = false)
	{
		return empty(self::$host) ? false : true;
	}
	
	// Función - Destruir conexión activa.
	public static function Crash()
	{
		// Si no hay una conexión activa, cancelar.
		if(!self::isReady())
			return;
		
		// Cerrando conexión FTP.
		ftp_quit(self::$connection);
		
		// Restaurando variables.
		self::$connection = null;
		self::$host = null;
		self::$username = "";
		self::$password = "";
		self::$port = 21;
		self::$ssl = false;
		self::$pasv = true;
	}
	
	// Función - Preparar una conexión FTP.
	// - $host: Host para la conexión.
	// - $username: Nombre de usuario.
	// - $password: Contraseña.
	// - $port (Int): Puerto (Predeterminado: 21).
	// - $ssl (Bool): ¿Usar conexión segura?
	// - $pasv (Bool): ¿Modo pasivo?
	public static function Init($host, $username, $password, $port = 21, $ssl = false, $pasv = true)
	{
		// Destruir conexión activa.
		self::Crash();
		
		// Si el puerto no es numerico, usar el 21.
		if(!is_numeric($port))
			$port = 21;
		
		// Si el uso de conexión segura no es booleano, usar false.
		if(!is_bool($ssl))
			$ssl = false;
		
		// Si el uso de modo pasivo no es booleano, usar true.
		if(!is_bool($pasv))
			$pasv = true;
		
		// Estableciendo variables.
		self::$host = $host;
		self::$username = $username;
		self::$password = $password;
		self::$port = $port;
		self::$ssl = $ssl;
		self::$pasv = $pasv;
		
		BitRock::log("Se ha definido la configuración para una conexión FTP a '$host'.");
	}
	
	// Función privada - Conectarse.
	private static function Connect()
	{
		// Si no hay una conexión preparada, lanzar error.
		if(!self::isReady())
			self::Error("01f", __FUNCTION__);
		
		// Ya hay una conexión activa, usar esa.
		if(self::$connection !== null)
			return self::$connection;
		
		// Conectarse al servidor FTP.
		if(self::$ssl)
			$f = ftp_ssl_connect(self::$host, self::$port) or self::Error("02f", __FUNCTION__);
		else
			$f = ftp_connect(self::$host, self::$port) or self::Error("02f", __FUNCTION__);
		
		// Iniciando sesión en el servidor FTP.
		ftp_login($f, self::$username, self::$password) or self::Error("02f", __FUNCTION__, 'Ha ocurrido un problema al intentar iniciar sesión, por favor verifica si las credenciales estan correctamente escritas.');
		ftp_pasv($f, self::$pasv);
		
		// Estableciendo conexión activa.
		self::$connection = $f;		
		return $f;
	}
	
	// Función - Obtener el directorio actual.
	public static function GetDir()
	{
		// Conectarse y realizar acción.
		$f = self::Connect();
		$d = ftp_pwd($f);
		
		return $d;
	}
	
	// Función - Ir a un directorio.
	// - $dir: Ruta del directorio a ir.
	public static function ToDir($dir)
	{
		// Conectarse y realizar acción.
		$f = self::Connect();
		$r = ftp_chdir($f, $dir);
		
		return $r;
	}
	
	// Función - Escribir un archivo.
	// - $to: Ruta del archivo de destino.
	// - $data: Datos/Bits del archivo.
	public static function Write($to, $data)
	{
		// Guardar datos en un archivo temporal y subirlo al servidor FTP.
		$file = Io::SaveTemporal($data);
		return self::Upload($file, $to);
	}
	
	// Función - Subir un archivo.
	// - $file: Ruta del archivo a subir.
	// - $to: Ruta del archivo de destino.
	public static function Upload($file, $to)
	{
		// Conectarse y realizar acción.
		$f = self::Connect();
		$r = ftp_put($f, $to, $file, FTP_BINARY);
		
		return $r;
	}
	
	// Función - Leer/Bajar un archivo.
	// - $file: Ruta del archivo que queremos.
	// - $to: Ruta del archivo donde guardar/bajar.	
	public static function Read($file, $to = '')
	{
		// En caso de no especificar donde guardarlo, usar un archivo temporal.
		if(empty($to))
			$to = BIT . 'Temp' . DS . Core::Random(20);
			
		// Conectarse y realizar acción.
		$f = self::Connect();
		ftp_get($f, $to, $file, FTP_BINARY);
		
		return Io::Read($to);
	}
	
	// Función - Eliminar un archivo.
	// - $file: Ruta del archivo que queremos eliminar.
	public static function Delete($file)
	{
		// Conectarse y realizar acción.
		$f = self::Connect();
		$r = ftp_delete($f, $file);
		
		return $r;
	}
	
	// Función - Crear un directorio.
	// - $dir: Ruta del directorio que queremos crear.
	public static function NewDir($dir)
	{
		// Conectarse y realizar acción.
		$f = self::Connect();
		$r = ftp_mkdir($f, $dir);
		
		return $r;
	}
	
	// Función - Eliminar un directorio y sus archivos.
	// - $dir: Ruta del directorio que queremos eliminar.
	public static function DeleteDir($dir)
	{
		// Conectarse y realizar acción.
		$f = self::Connect();
		$r = ftp_rmdir($f, $dir);
		
		return $r;
	}
	
	// Función - Ejecutar un comando FTP.
	// - $c: Comando a ejecutar.
	public static function Command($c)
	{
		// Conectarse y realizar acción.
		$f = self::Connect();
		$r = ftp_exec($f, $c);
		
		return $r;
	}
	
	// Función - Cambiar los permisos de un archivo/directorio.
	// - $file: Ruta del archivo a cambiar permisos.
	// - $mode (Int): Permisos.
	public static function Chmod($file, $mode = 0777)
	{
		// Conectarse y realizar acción.
		$f = self::Connect();
		$r = ftp_chmod($f, $mode, $file);
		
		return $r == false ? false : true;
	}
}
?>