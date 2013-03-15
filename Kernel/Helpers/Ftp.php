<?php
/**
 * BeatRock
 *
 * Framework para el desarrollo de aplicaciones web.
 *
 * @author 		Iván Bravo <webmaster@infosmart.mx> @Kolesias123
 * @copyright 	InfoSmart 2013. Todos los derechos reservados.
 * @license 	http://creativecommons.org/licenses/by-sa/2.5/mx/  Creative Commons "Atribución-Licenciamiento Recíproco"
 * @link 		http://beatrock.infosmart.mx/
 * @version 	3.0
 *
 * @package 	Ftp
 * Permite la conexión con un servidor Ftp.
 *
*/

# Acción ilegal
if ( !defined('BEATROCK') )
	exit;

class Ftp extends Base
{
	public $server 		= NULL;
	public $host 		= '';
	public $username 	= '';
	public $password 	= '';
	public $port 		= 21;
	public $ssl 		= false;
	public $pasv 		= true;

	/**
	 * ¿Nos hemos conectado al servidor Ftp?
	 * @return boolean Devuelve SI o NO.
	 */
	function Connected($e = false)
	{
		return ( $this->server == NULL ) ? false : true;
	}

	/**
	 * Desconectarse del servidor Ftp.
	 */
	function Disconnect()
	{
		# Necesitamos habernos conectado.
		if ( !$this->Connected() )
			return;

		# Nos desconectamos.
		ftp_quit($this->server);

		# Reiniciamos variables.
		$this->server 		= null;
		$this->host 		= '';
		$this->username 	= '';
		$this->password 	= '';
		$this->port 		= 21;
		$this->ssl 			= false;
		$this->pasv 		= true;
	}

	/**
	 * Conexión al servidor Ftp.
	 * @param string  $host     Host de conexión
	 * @param string  $username Nombre de usuario.
	 * @param string  $password Contraseña
	 * @param integer $port     Puerto del servidor.
	 * @param boolean $ssl      ¿Conexión SSL?
	 * @param boolean $pasv     ¿Modo pasivo?
	 */
	function __construct($host, $username, $password, $port = 21, $ssl = false, $pasv = true)
	{
		Lang::SetSection('mod.ftp');
		parent::__construct($this);

		# Desconectamos cualquier servidor activo.
		$this->Disconnect();

		# El puerto es inválido, usamos el predeterminado.
		if ( !is_numeric($port) )
			$port = 21;

		# La conexión SSL es inválida, no la usamos.
		if ( !is_bool($ssl) )
			$ssl = false;

		# El modo pasivo es inválido, la usamos.
		if ( !is_bool($pasv) )
			$pasv = true;

		# Establecemos las variables.
		$this->host 	= $host;
		$this->username = $username;
		$this->password = $password;
		$this->port 	= $port;
		$this->ssl 		= $ssl;
		$this->pasv 	= $pasv;

		Reg('%ftp.connect% '.$host.'.');
	}

	/**
	 * Realiza la conexión al servidor (si es la primera vez)
	 * @return resource Conexión FTP.
	 */
	function Connect()
	{
		Lang::SetSection('mod.ftp');

		# La conexión se ha establecido.
		if ( $this->server !== NULL )
			return $this->server;

		# Nos conectamos.
		$ftp = ( $this->ssl ) ? ftp_ssl_connect($this->host, $this->port) or $this->Error('ftp.connect') : ftp_connect($this->host, $this->port) or $this->Error('ftp.connect');

		# Iniciamos sesión.
		ftp_login($ftp, $this->username, $this->password) or $this->Error('ftp.connect', '%error.ftp.login%');
		ftp_pasv($ftp, $this->pasv);

		$this->server = $ftp;
		return $ftp;
	}

	/**
	 * Obtiene el directorio actual.
	 */
	function GetDir()
	{
		$f = $this->Connect();
		$d = ftp_pwd($f);

		return $d;
	}

	/**
	 * Obtiene una lista detalla de los archivo en el directorio.
	 */
	function GetFileList($path)
	{
		$f 			= $this->Connect();
		$rfiles 	= ftp_rawlist($f, $path);

		foreach ( $rfiles as $line )
		{
			preg_match("#([drwx\-]+)([\s]+)([0-9]+)([\s]+)([a-zA-Z0-9\.]+)([\s]+)([a-zA-Z0-9\.]+)([\s]+)([0-9]+)([\s]+)([a-zA-Z]+)([\s ]+)([0-9]+)([\s]+)([0-9]+):([0-9]+)([\s]+)([a-zA-Z0-9\.\-\_ ]+)#si", $line, $out);

			if ( $out[3] !== 1 AND ($out[18] == "." || $out[18] == "..") )
				continue;

			$files[$out[18]]['rights'] 			= $out[1];
            $files[$out[18]]['type'] 			= ( $out[3] == 1 ) ? 'file': 'folder';
            $files[$out[18]]['owner_id'] 		= $out[5];
            $files[$out[18]]['owner'] 			= $out[7];
            $files[$out[18]]['date_modified'] 	= $out[11].' '.$out[13] . ' '.$out[13].':'.$out[16].'';
		}

		return $files;
	}

	/**
	 * Cambia la ubicación actual.
	 * @param string $dir Directorio a donde ir.
	 */
	function ToDir($dir)
	{
		$f = $this->Connect();
		$r = ftp_chdir($f, $dir);

		return $r;
	}

	/**
	 * Vuelve al directorio anterior.
	 */
	function BackDir()
	{
		$f = $this->Connect();
		$r = ftp_cdup($f);

		return $r;
	}

	/**
	 * Escribe un archivo.
	 * @param string $to   Ruta del archivo en la conexión FTP.
	 * @param string $data Datos del archivo.
	 */
	function Write($to, $data)
	{
		$file = Io::SaveTemporal($data);
		return $this->Upload($file, $to);
	}

	/**
	 * Sube un archivo.
	 * @param string $file Ruta del archivo a subir.
	 * @param string $to   Ruta del archivo en la conexión FTP.
	 */
	function Upload($file, $to)
	{
		$f = $this->Connect();
		ftp_alloc($f, filesize($file));
		$r = ftp_put($f, $to, $file, FTP_BINARY);

		return $r;
	}

	/**
	 * Lee y/o baja un archivo.
	 * @param string $file Ruta del archivo en la conexión FTP.
	 * @param string $to   Ruta en donde descargar el archivo.
	 */
	function Read($file, $to = '')
	{

		# No hay ruta donde descargar, solo queremos leerlo.
		if ( empty($to) )
			$to = BIT . 'Temp' . DS . Core::Random(20);

		$f = $this->Connect();
		ftp_get($f, $to, $file, FTP_BINARY);

		return Io::Read($to);
	}

	/**
	 * Elimina un archivo.
	 * @param string $file Ruta del archivo en la conexión FTP.
	 */
	function Delete($file)
	{
		$f = $this->Connect();
		$r = ftp_delete($f, $file);

		return $r;
	}

	/**
	 * Crea un directorio.
	 * @param string $dir Ruta del directorio en la conexión FTP.
	 */
	function NewDir($dir)
	{
		$f = $this->Connect();
		$r = ftp_mkdir($f, $dir);

		return $r;
	}

	/**
	 * Elimina un directorio y todos sus archivos dentro.
	 * @param string $dir Ruta del directorio en la conexión FTP.
	 */
	function DeleteDir($dir)
	{
		$f 		= $this->Connect();
		$files 	= $this->GetFileList($dir);

		if ( count($files) > 0 )
		{
			foreach ( $files as $file => $data )
			{
				if ( $data['type'] == 'file' )
					$this->Delete($dir . $file);
				else
					$this->DeleteDir($dir . $file . '/');
			}
		}

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