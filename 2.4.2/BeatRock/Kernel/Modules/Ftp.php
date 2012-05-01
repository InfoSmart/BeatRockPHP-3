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

class Ftp
{
	private $connection = null;
	private $host = '';
	private $username = '';
	private $password = '';
	private $port = 21;
	private $ssl = false;
	private $pasv = true;
	
	// Función privada - Lanzar error.
	// - $function: Función causante.
	// - $msg: Mensaje del error.
	private function Error($code, $function, $msg = '')
	{
		BitRock::setStatus($msg, __FILE__, Array('function' => $function));
		BitRock::launchError($code);
	}
	
	// Función privada - ¿Hay alguna conexión activa?
	// - $e: ¿Lanzar error en caso de que no haya una conexión activa?
	private function Ready($e = false)
	{
		return empty($this->host) ? false : true;
	}
	
	// Función - Destruir conexión activa.
	public function Crash()
	{
		if(!$this->Ready())
			return;
		
		ftp_quit($this->connection);
		
		$this->connection = null;
		$this->host = null;
		$this->username = '';
		$this->password = '';
		$this->port = 21;
		$this->ssl = false;
		$this->pasv = true;
	}
	
	// Función - Preparar una conexión FTP.
	// - $host: Host para la conexión.
	// - $username: Nombre de usuario.
	// - $password: Contraseña.
	// - $port (Int): Puerto (Predeterminado: 21).
	// - $ssl (Bool): ¿Usar conexión segura?
	// - $pasv (Bool): ¿Modo pasivo?
	public function __construct($host, $username, $password, $port = 21, $ssl = false, $pasv = true)
	{
		$this->Crash();
		
		if(!is_numeric($port))
			$port = 21;
		
		if(!is_bool($ssl))
			$ssl = false;
		
		if(!is_bool($pasv))
			$pasv = true;
		
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->port = $port;
		$this->ssl = $ssl;
		$this->pasv = $pasv;
		
		BitRock::log('Se ha definido la configuración para una conexión FTP a '.$host.'.');
	}
	
	// Función privada - Conectarse.
	private function Connect()
	{
		if(!$this->Ready())
			$this->Error('01f', __FUNCTION__);
		
		if($this->connection !== null)
			return $this->connection;
		
		$f = $this->ssl ? ftp_ssl_connect($this->host, $this->port) or $this->Error('02f', __FUNCTION__) : ftp_connect($this->host, $this->port) or $this->Error('02f', __FUNCTION__);

		ftp_login($f, $this->username, $this->password) or $this->Error("02f", __FUNCTION__, 'Ha ocurrido un problema al intentar iniciar sesión, por favor verifica si las credenciales estan correctamente escritas.');
		ftp_pasv($f, $this->pasv);
		
		$this->connection = $f;		
		return $f;
	}
	
	// Función - Obtener el directorio actual.
	public function GetDir()
	{
		$f = $this->Connect();
		$d = ftp_pwd($f);
		
		return $d;
	}
	
	// Función - Ir a un directorio.
	// - $dir: Ruta del directorio a ir.
	public function ToDir($dir)
	{
		$f = $this->Connect();
		$r = ftp_chdir($f, $dir);
		
		return $r;
	}
	
	// Función - Escribir un archivo.
	// - $to: Ruta del archivo de destino.
	// - $data: Datos/Bits del archivo.
	public function Write($to, $data)
	{
		$file = Io::SaveTemporal($data);
		return $this->Upload($file, $to);
	}
	
	// Función - Subir un archivo.
	// - $file: Ruta del archivo a subir.
	// - $to: Ruta del archivo de destino.
	public function Upload($file, $to)
	{
		$f = $this->Connect();
		$r = ftp_put($f, $to, $file, FTP_BINARY);
		
		return $r;
	}
	
	// Función - Leer/Bajar un archivo.
	// - $file: Ruta del archivo que queremos.
	// - $to: Ruta del archivo donde guardar/bajar.	
	public function Read($file, $to = '')
	{
		if(empty($to))
			$to = BIT . 'Temp' . DS . Core::Random(20);
			
		$f = $this->Connect();
		ftp_get($f, $to, $file, FTP_BINARY);
		
		return Io::Read($to);
	}
	
	// Función - Eliminar un archivo.
	// - $file: Ruta del archivo que queremos eliminar.
	public function Delete($file)
	{
		$f = $this->Connect();
		$r = ftp_delete($f, $file);
		
		return $r;
	}
	
	// Función - Crear un directorio.
	// - $dir: Ruta del directorio que queremos crear.
	public function NewDir($dir)
	{
		$f = $this->Connect();
		$r = ftp_mkdir($f, $dir);
		
		return $r;
	}
	
	// Función - Eliminar un directorio y sus archivos.
	// - $dir: Ruta del directorio que queremos eliminar.
	public function DeleteDir($dir)
	{
		$f = $this->Connect();
		$r = ftp_rmdir($f, $dir);
		
		return $r;
	}
	
	// Función - Ejecutar un comando FTP.
	// - $c: Comando a ejecutar.
	public function Command($c)
	{
		$f = $this->Connect();
		$r = ftp_exec($f, $c);
		
		return $r;
	}
	
	// Función - Cambiar los permisos de un archivo/directorio.
	// - $file: Ruta del archivo a cambiar permisos.
	// - $mode (Int): Permisos.
	public function Chmod($file, $mode = 0777)
	{
		$f = $this->Connect();
		$r = ftp_chmod($f, $mode, $file);
		
		return $r == false ? false : true;
	}
}
?>