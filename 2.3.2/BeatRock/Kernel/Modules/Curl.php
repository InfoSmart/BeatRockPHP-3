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

class Curl
{
	private static $host = "";
	private static $opts = Array();
	
	// Función privada - Lanzar error.
	// - $function: Función causante.
	// - $msg: Mensaje del error.
	private static function Error($code, $function, $msg = '')
	{
		BitRock::setStatus($msg, __FILE__, Array('function' => $function));
		BitRock::launchError($code);
	}
	
	// Función privada - ¿Hay alguna conexión activa?
	// - $e (Bool): ¿Lanzar error en caso de que no haya una conexión activa?
	private static function isReady($e = false)
	{
		return empty(self::$host) ? false : true;
	}
	
	// Función - Destruir conexión activa.
	public static function Crash()
	{
		// Si no hay una conexión activa, cancelar.
		if(!self::isReady())
			return false;
			
		// Restaurando variables.
		self::$host = null;
		self::$opts = Array();
	}
	
	// Función - Preparar una conexión cURL.
	// - $host: Host de la conexión.
	// - $opts (Array): Opciones para la conexión.
	public static function Init($host, $opts = Array())
	{
		// Destruir conexión activa.
		self::Crash();
		
		// Si no hay agente definido, usar el nuestro.
		if(empty($opts['agent']))
			$opts['agent'] = AGENT;
			
		// Si no hay tiempo de esperar, usar 100 segundos.
		if(!is_numeric($opts['timeout']))
			$opts['timeout'] = 100;
			
		// Al parecer no queremos enviar cookies.
		if(!is_bool($opts['cookies']))
			$opts['cookies'] = false;
			
		// Opciones de conexión interna vacias...
		if(!is_array($opts['params']))
			$opts['params'] = Array();
			
		// No hay cabeceras definidas.
		if(empty($opts['header']) OR !is_array($opts['header']))
			$opts['header'] = Array();
			
		// Enviar cabeceras recomendadas.
		$opts['header'][] = "Accept-Language: es-es,es;q=0.8";
		$opts['header'][] = "Accept-Charset: ISO-8859-1,ISO-8859-15,UTF-8";
		$opts['header'][] = "Expect: ";
		
		// Definiendo variables de conexión.
		self::$host = $host;
		self::$opts = $opts;
		
		BitRock::log("Se ha definido la configuración para una conexión cURL a '$host'.");
	}
	
	// Función privada - Realizar una conexión cURL.
	private static function Connect()
	{
		// Si no hay una conexión preparada, lanzar error...
		if(!self::isReady())
			self::Error("01c", __FUNCTION__);
			
		$opts = self::$opts;
		
		// Queremos enviar cookies.
		if($opts['cookies'])
		{
			global $_COOKIE;
			
			$file = BIT . 'Temp' . DS . 'cookie.' . time() . '.txt';
			$cook = "";
			
			foreach($_COOKIE as $param => $value)
			{
				if(is_array($value))
					continue;
					
				$cook .= "$param=$value; ";
			}
		}
		
		// Preparando la conexión...
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$host);
		curl_setopt($ch, CURLOPT_USERAGENT, $opts['agent']);
		curl_setopt($ch, CURLOPT_TIMEOUT, $opts['timeout']);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $opts['timeout']);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $opts['header']);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		
		// Usar cookies :B
		if($opts['cookies'])
		{
			curl_setopt($ch, CURLOPT_COOKIESESSION, $cook);
			curl_setopt($ch, CURLOPT_COOKIE, $cook);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $file);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $file);
		}
		
		// Ajustando otros parametros.
		if(is_array($opts['params']))
		{
			foreach($opts['params'] as $param => $value)
			{
				if(!is_integer($param))
					continue;
					
				curl_setopt($ch, $param, $value);
			}
		}
		
		return $ch;
	}
	
	// Función - Obtener la respuesta de la conexión.
	public static function Get()
	{
		$ch = self::Connect();
		curl_setopt($ch, CURLOPT_POST, false);
		$re = curl_exec($ch);
		curl_close($ch);
		
		return $re;
	}
	
	// Función - Obtener las cabeceras de la conexión.
	public static function Headers()
	{
		$ch = self::Connect();
		curl_setopt($ch, CURLOPT_POST, false);
		curl_exec($ch);
		$re = curl_getinfo($ch, CURLINFO_HEADER_OUT);
		curl_close($ch);
		
		return $re;
	}
	
	// Función - Enviar información a la conexión.
	// - $data (Array): Información a enviar.
	public static function Post($data)
	{
		if(!is_array($data))
			return false;
			
		foreach($data as $param => $value)
            $datas[] = "{$param}=" . urlencode($value);
			
		$datas = join("&", $datas);
		
		$ch = self::Connect();
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
		$re = curl_exec($ch);
		curl_close($ch);
		
		BitRock::log("Se han enviado datos ($datas) a la conexión cURL");
		return $re;
	}
}
?>