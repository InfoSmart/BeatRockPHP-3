<?
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
 * @package 	Example
 * Permite crear un sistema de usuarios avanzado.
 *
*/

# Acción ilegal.
if ( !defined('BEATROCK') )
	exit;

class Curl extends Base
{
	public $cURL 		= null;
	private $host 		= '';
	private $params 	= array();

	public $errno 		= 0;
	public $error 		= '';

	public $fileSupport = false;

	/**
	 * ¿Ya estamos preparados?
	 */
	function Prepared()
	{
		return ( empty($this->address) ) ? false : true;
	}

	/**
	 * Destruya la instancia actual.
	 */
	function Destroy()
	{
		if ( !$this->Prepared() )
			return false;

		$this->address 	= null;
		$this->params 	= array();

		curl_close($this->cURL);
	}

	/**
	 * Prepara una nueva conexión cURL.
	 * @param string $host   	Dirección de conexión
	 * @param array $params 	Opciones
	 */
	function __construct($host, $params = array())
	{
		Lang::SetSection('helper.curl');
		$this->Destroy();

		# Agente vacio, usamos el del navegador.
		if ( empty($params['agent']) )
			$params['agent'] 	= AGENT;

		# No se ha definido un timeout, usar 60 segundos.
		if ( !is_numeric($params['timeout']) )
			$params['timeout'] 	= 60;

		# No usar cookies
		//if ( !is_bool($params['cookies']) )
		//	$params['cookies'] 	= false;

		# Parametros para configurar la conexión.
		if ( !is_array($params['params']) )
			$params['params'] 	= array();

		# Sin cabeceras.
		if ( empty($params['header']) OR !is_array($params['header']) )
			$params['header'] 	= array();

		# Cabeceras predeterminadas.
		$params['header'][] = 'Accept-Language: ' . $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$params['header'][] = 'Accept-Charset: UTF-8, ISO-8859-1,ISO-8859-15';
		$params['header'][] = 'Expect: ';

		# Establecemos los valores.
		$this->address 	= $host;
		$this->params 	= $params;
		$this->cURL 	= $this->Connect();

		Reg('%configuration%');
	}

	/**
	 * Destructor
	 */
	function __destruct()
	{
		$this->Destroy();
	}

	/**
	 * Realiza la conexión cURL.
	 * @return cURL Manipulador de cURL
	 */
	function Connect()
	{
		# Aún no estamos preparados.
		if ( !$this->Prepared() )
			$this->Error('curl.instance');

		# Parametros
		$params = $this->params;

		# Usaremos COOKIES
		if ( $params['cookies'] == true OR is_array($params['cookies']) )
		{
			$file 		= BIT . 'Temp' . DS . 'cookie.' . time() . '.txt';
			$cookies 	= '';

			# Usar las COOKIES definidas.
			if ( is_array($params['cookies']) )
				$COOKIE = $params['cookies'];

			# Usar las COOKIES del navegador.
			else
			{
				global $_COOKIE;
				$COOKIE = $_COOKIE;
			}

			# Ponemos las cookies en texto.
			foreach ( $COOKIE as $key => $value )
			{
				# ¿Esta cookie es un array?
				if ( is_array($value) )
					continue;

				$cookies .= "$key=$value; ";
			}
		}

		# Iniciamos la conexión.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 				$this->address);
		curl_setopt($ch, CURLOPT_USERAGENT, 		$params['agent']);
		curl_setopt($ch, CURLOPT_TIMEOUT, 			$params['timeout']);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 	$params['timeout']);
		curl_setopt($ch, CURLOPT_HTTPHEADER, 		$params['header']);
		curl_setopt($ch, CURLOPT_HEADER, 			false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 	false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 	false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 	true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 	true);

		# Usaremos COOKIES.
		if ( !empty($cookies) )
		{
			curl_setopt($ch, CURLOPT_COOKIE, 		$cookies);
			curl_setopt($ch, CURLOPT_COOKIEFILE, 	$file);
		}

		# Establecer opciones extras para la conexión.
		if ( is_array($params['params']) )
		{
			foreach ( $params['params'] as $key => $value )
			{
				# Las llaves deben ser de tipo entero.
				if ( !is_integer($key) )
					continue;

				# Establecemos la opción.
				curl_setopt($ch, $key, $value);
			}
		}

		return $ch;
	}

	/**
	 * Reconectarse.
	 */
	function Reconnect()
	{
		curl_close($this->cURL);
		$this->cURL = $this->Connect();
	}

	/**
	 * Obtiene las cabeceras de la conexión.
	 */
	function Headers()
	{
		curl_setopt($this->cURL, CURLOPT_POST, 			false);
		curl_setopt($this->cURL, CURLINFO_HEADER_OUT, 	true);

		$re = curl_getinfo($this->cURL, CURLINFO_HEADER_OUT);

		$this->errno = curl_errno($this->cURL);
		$this->error = curl_error($this->cURL);

		return $re;
	}

	/**
	 * Obtiene la información de una conexión.
	 */
	function Info()
	{
		curl_setopt($this->cURL, CURLOPT_POST, false);
		$re = curl_getinfo($this->cURL);

		$this->errno = curl_errno($this->cURL);
		$this->error = curl_error($this->cURL);

		return $re;
	}

	/**
	 * Lanza una petición de tipo GET a la conexión.
	 */
	function Get()
	{
		curl_setopt($this->cURL, CURLOPT_POST, false);
		$re = curl_exec($this->cURL);

		$this->errno = curl_errno($this->cURL);
		$this->error = curl_error($this->cURL);

		return $re;
	}

	/**
	 * Lanza una petición de tipo POST a la conexión
	 * @param array $data Información a enviar
	 */
	function Post($data)
	{
		# No hay información a enviar
		# ¿entonces para que usas POST?
		if ( !is_array($data) )
			return false;

		# Soporte para el envio de archivos.
		if ( $this->fileSupport == true )
		{
			$post 				= $data;
			$this->fileSupport 	= false;
		}
		else
			$post = http_build_query($data, null, '&');

		curl_setopt($this->cURL, CURLOPT_POST, 			true);
		curl_setopt($this->cURL, CURLOPT_POSTFIELDS, 	$post);
		$re = curl_exec($this->cURL);

		$this->errno = curl_errno($this->cURL);
		$this->error = curl_error($this->cURL);

		Reg('%datasend.correct%');
		return $re;
	}
}
?>