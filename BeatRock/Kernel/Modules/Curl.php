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

class Curl
{
	static $host 	= '';
	static $params 	= array();

	static $errno 	= 0;
	static $error 	= '';
	
	// Lanzar error.
	// - $function: Función causante.
	// - $message: Mensaje del error.
	static function Error($code, $function, $message = '')
	{
		BitRock::SetStatus($message, __FILE__, array('function' => $function));
		BitRock::LaunchError($code);
	}
	
	// ¿Hay alguna conexión activa?
	// - $e (Bool): ¿Lanzar error en caso de que no haya una conexión activa?
	static function Ready($e = false)
	{
		return empty(self::$host) ? false : true;
	}
	
	// Destruir conexión activa.
	static function Crash()
	{
		if(!self::Ready())
			return false;
			
		self::$host = null;
		self::$opts = array();
	}
	
	// Preparar una conexión cURL.
	// - $host: Host de la conexión.
	// - $params (Array): Opciones para la conexión.
	static function Init($host, $params = array())
	{
		Lang::SetSection('mod.curl');
		self::Crash();
		
		if(empty($params['agent']))
			$params['agent'] 	= AGENT;
			
		if(!is_numeric($params['timeout']))
			$params['timeout'] 	= 60;
			
		if(!is_bool($params['cookies']))
			$params['cookies'] 	= false;
			
		if(!is_array($params['params']))
			$params['params'] 	= array();
			
		if(empty($params['header']) OR !is_array($params['header']))
			$params['header'] 	= array();
			
		$params['header'][] = 'Accept-Language: es-es,es;q=0.8';
		$params['header'][] = 'Accept-Charset: ISO-8859-1,ISO-8859-15,UTF-8';
		$params['header'][] = 'Expect: ';
		
		self::$host 	= $host;
		self::$params 	= $opts;
		
		Reg('%configuration%');
	}
	
	// Realizar una conexión cURL.
	static function Connect()
	{
		if(!self::Ready())
			self::Error('curl.instance', __FUNCTION__);
			
		$params = self::$params;
		
		if($params['cookies'])
		{
			global $_COOKIE;
			
			$file = BIT . 'Temp' . DS . 'cookie.' . time() . '.txt';
			$cook = '';
			
			foreach($_COOKIE as $param => $value)
			{
				if(is_array($value))
					continue;
					
				$cook .= "$param=$value; ";
			}
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$host);
		curl_setopt($ch, CURLOPT_USERAGENT, $params['agent']);
		curl_setopt($ch, CURLOPT_TIMEOUT, $params['timeout']);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $params['timeout']);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $params['header']);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		
		if($params['cookies'])
		{
			curl_setopt($ch, CURLOPT_COOKIESESSION, $cook);
			curl_setopt($ch, CURLOPT_COOKIE, $cook);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $file);
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
	
	// Obtener la respuesta de la conexión.
	static function Get()
	{
		$ch = self::Connect();
		curl_setopt($ch, CURLOPT_POST, false);
		$re = curl_exec($ch);

		self::$errno = curl_errno($ch);
		self::$error = curl_error($ch);

		curl_close($ch);
		return $re;
	}
	
	// Obtener las cabeceras de la conexión.
	static function Headers()
	{
		$ch = self::Connect();
		curl_setopt($ch, CURLOPT_POST, false);
		curl_exec($ch);
		$re = curl_getinfo($ch, CURLINFO_HEADER_OUT);
	
		self::$errno = curl_errno($ch);
		self::$error = curl_error($ch);
		
		curl_close($ch);
		return $re;
	}
	
	// Enviar información a la conexión.
	// - $data (Array): Información a enviar.
	static function Post($data)
	{
		if(!is_array($data))
			return false;
			
		foreach($data as $param => $value)
            $datas[] = "{$param}=" . urlencode($value);
			
		$datas = join('&', $datas);
		
		$ch = self::Connect();
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
		$re = curl_exec($ch);

		self::$errno = curl_errno($ch);
		self::$error = curl_error($ch);

		curl_close($ch);
		
		Reg('%datasend.correct%');
		return $re;
	}
}
?>