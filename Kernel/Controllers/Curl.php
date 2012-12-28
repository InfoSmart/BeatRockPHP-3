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

class Curl
{
	private $host 		= '';
	private $params 	= array();

	public $errno 		= 0;
	public $error 		= '';

	public $fileSupport = false;
	
	// Lanzar error.
	// - $function: Función causante.
	// - $message: Mensaje del error.
	function Error($code, $function, $message = '')
	{
		Bit::Status($message, __FILE__, array('function' => $function));
		Bit::LaunchError($code);
	}
	
	// ¿Hay alguna conexión activa?
	function Ready()
	{
		return empty($this->host) ? false : true;
	}
	
	// Destruir conexión activa.
	function Destroy()
	{
		if(!$this->Ready())
			return false;
			
		$this->host 	= null;
		$this->params 	= [];
	}
	
	// Preparar una conexión cURL.
	// - $host: Host de la conexión.
	// - $params (Array): Opciones para la conexión.
	function __construct($host, $params = [])
	{
		Lang::SetSection('mod.curl');
		$this->Destroy();
		
		if(empty($params['agent']))
			$params['agent'] 	= AGENT;
			
		if(!is_numeric($params['timeout']))
			$params['timeout'] 	= 60;
			
		if(!is_bool($params['cookies']))
			$params['cookies'] 	= false;
			
		if(!is_array($params['params']))
			$params['params'] 	= [];
			
		if(empty($params['header']) OR !is_array($params['header']))
			$params['header'] 	= [];
			
		$params['header'][] = 'Accept-Language: ' . $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$params['header'][] = 'Accept-Charset: UTF-8, ISO-8859-1,ISO-8859-15';
		$params['header'][] = 'Expect: ';
		
		$this->host 	= $host;
		$this->params 	= $params;
		
		Reg('%configuration%');
	}

	function __destruct()
	{
		curl_close($this->host);
	}
	
	// Realizar una conexión cURL.
	function Connect()
	{
		if(!$this->Ready())
			$this->Error('curl.instance', __FUNCTION__);
			
		$params = $this->params;
		
		if($params['cookies'] == true OR is_array($params['cookies']))
		{			
			$file 		= BIT . 'Temp' . DS . 'cookie.' . time() . '.txt';
			$cookies 	= '';

			if(is_array($params['cookies']))
				$COOKIE = $params['cookies'];
			else
			{
				global $_COOKIE;
				$COOKIE = $_COOKIE;
			}
			
			foreach($COOKIE as $param => $value)
			{
				if(is_array($value))
					continue;
					
				$cookies .= "$param=$value; ";
			}
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->host);
		curl_setopt($ch, CURLOPT_USERAGENT, $params['agent']);
		curl_setopt($ch, CURLOPT_TIMEOUT, $params['timeout']);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $params['timeout']);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $params['header']);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		
		if(!empty($cookies))
		{
			curl_setopt($ch, CURLOPT_COOKIE, $cookies);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $file);
		}
		
		if(is_array($params['params']))
		{
			foreach($params['params'] as $param => $value)
			{
				if(!is_integer($param))
					continue;
					
				curl_setopt($ch, $param, $value);
			}
		}
		
		return $ch;
	}
	
	// Obtener las cabeceras de la conexión.
	function Headers()
	{
		$ch = $this->Connect();
		curl_setopt($ch, CURLOPT_POST, false);
		curl_exec($ch);
		$re = curl_getinfo($ch, CURLINFO_HEADER_OUT);
	
		$this->errno = curl_errno($ch);
		$this->error = curl_error($ch);
		
		return $re;
	}

	// Obtener la respuesta de la conexión.
	function Get()
	{
		$ch = $this->Connect();
		curl_setopt($ch, CURLOPT_POST, false);
		$re = curl_exec($ch);

		$this->errno = curl_errno($ch);
		$this->error = curl_error($ch);

		return $re;
	}
	
	// Enviar información a la conexión.
	// - $data (Array): Información a enviar.
	function Post($data)
	{
		if(!is_array($data))
			return false;

		if($this->fileSupport == true)
		{
			$post 				= $data;
			$this->fileSupport 	= false;
		}
		else
			$post = http_build_query($data, null, '&');
		
		$ch = $this->Connect();
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$re = curl_exec($ch);

		$this->errno = curl_errno($ch);
		$this->error = curl_error($ch);

		Reg('%datasend.correct%');
		return $re;
	}
}
?>