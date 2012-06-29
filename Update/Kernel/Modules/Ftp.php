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
	$connection = null;
	$host 		= '';
	$username 	= '';
	$password 	= '';
	$port 		= 21;
	$ssl 		= false;
	$pasv 		= true;
	
	// Lanzar error.
	// - $code: Código del error.
	// - $function: Función causante.
	// - $message: Mensaje del error.
	function Error($code, $function, $message = '')
	{
		BitRock::SetStatus($message, __FILE__, array('function' => $function));
		BitRock::LaunchError($code);
	}
	
	// ¿Hay alguna conexión activa?
	// - $e: ¿Lanzar error en caso de que no haya una conexión activa?
	function Ready($e = false)
	{
		return (empty($this->host)) ? false : true;
	}
	
	// Función - Destruir conexión activa.
	function Crash()
	{
		if(!$this->Ready())
			return;
		
		ftp_quit($this->connection);
		
		$this->connection 	= null;
		$this->host 		= null;
		$this->username 	= '';
		$this->password 	= '';
		$this->port 		= 21;
		$this->ssl 			= false;
		$this->pasv 		= true;
	}
	
	// Preparar una conexión FTP.
	// - $host: Host para la conexión.
	// - $username: Nombre de usuario.
	// - $password: Contraseña.
	// - $port (Int): Puerto (Predeterminado: 21).
	// - $ssl (Bool): ¿Usar conexión segura?
	// - $pasv (Bool): ¿Modo pasivo?
	function __construct($host, $username, $password, $port = 21, $ssl = false, $pasv = true)
	{
		Lang::SetSection('mod.ftp');
		$this->Crash();
		
		if(!is_numeric($port))
			$port = 21;
		
		if(!is_bool($ssl))
			$ssl = false;
		
		if(!is_bool($pasv))
			$pasv = true;
		
		$this->host 	= $host;
		$this->username = $username;
		$this->password = $password;
		$this->port 	= $port;
		$this->ssl 		= $ssl;
		$this->pasv 	= $pasv;
		
		Reg('%ftp.connect% '.$host.'.');
	}
	
	// Conectarse.
	function Connect()
	{
		if(!$this->Ready())
			$this->Error('ftp.need', __FUNCTION__);

		Lang::SetSection('mod.ftp');
		
		if($this->connection !== null)
			return $this->connection;
		
		$f = ($this->ssl) ? ftp_ssl_connect($this->host, $this->port) or $this->Error('ftp.connect', __FUNCTION__) : ftp_connect($this->host, $this->port) or $this->Error('ftp.connect', __FUNCTION__);

		ftp_login($f, $this->username, $this->password) or $this->Error("ftp.connect", __FUNCTION__, '%error.ftp.login%');
		ftp_pasv($f, $this->pasv);
		
		$this->connection = $f;		
		return $f;
	}
	
	// Obtener el directorio actual.
	function GetDir()
	{
		$f = $this->Connect();
		$d = ftp_pwd($f);
		
		return $d;
	}
	
	// Ir a un directorio.
	// - $dir: Ruta del directorio a ir.
	function ToDir($dir)
	{
		$f = $this->Connect();
		$r = ftp_chdir($f, $dir);
		
		return $r;
	}

	// Volver al directorio padre.
	function BackDir()
	{
		$f = $this->Connect();
		$r = ftp_cdup($f);

		return $r;
	}
	
	// Escribir un archivo.
	// - $to: Ruta del archivo de destino.
	// - $data: Datos/Bits del archivo.
	function Write($to, $data)
	{
		$file = Io::SaveTemporal($data);
		return $this->Upload($file, $to);
	}
	
	// Subir un archivo.
	// - $file: Ruta del archivo a subir.
	// - $to: Ruta del archivo de destino.
	function Upload($file, $to)
	{
		$f = $this->Connect();
		ftp_alloc($f, filesize($file));
		$r = ftp_put($f, $to, $file, FTP_BINARY);
		
		return $r;
	}
	
	//Leer/Bajar un archivo.
	// - $file: Ruta del archivo que queremos.
	// - $to: Ruta del archivo donde guardar/bajar.	
	function Read($file, $to = '')
	{
		if(empty($to))
			$to = BIT . 'Temp' . DS . Core::Random(20);
			
		$f = $this->Connect();
		ftp_get($f, $to, $file, FTP_BINARY);
		
		return Io::Read($to);
	}
	
	// Eliminar un archivo.
	// - $file: Ruta del archivo que queremos eliminar.
	function Delete($file)
	{
		$f = $this->Connect();
		$r = ftp_delete($f, $file);
		
		return $r;
	}
	
	// Crear un directorio.
	// - $dir: Ruta del directorio que queremos crear.
	function NewDir($dir)
	{
		$f = $this->Connect();
		$r = ftp_mkdir($f, $dir);
		
		return $r;
	}
	
	// Eliminar un directorio y sus archivos.
	// - $dir: Ruta del directorio que queremos eliminar.
	function DeleteDir($dir)
	{
		$f = $this->Connect();
		$r = ftp_rmdir($f, $dir);
		
		return $r;
	}
	
	// Ejecutar un comando FTP.
	// - $c: Comando a ejecutar.
	function Command($c)
	{
		$f = $this->Connect();
		$r = ftp_exec($f, $c);
		
		return $r;
	}
	
	// Cambiar los permisos de un archivo/directorio.
	// - $file: Ruta del archivo a cambiar permisos.
	// - $mode (Int): Permisos.
	function Chmod($file, $mode = 0777)
	{
		$f = $this->Connect();
		$r = ftp_chmod($f, $mode, $file);
		
		return $r == false ? false : true;
	}
}
?>