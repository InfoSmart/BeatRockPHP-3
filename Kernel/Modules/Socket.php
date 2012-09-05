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

class Socket
{
	static $socket 		= null;	
	static $server 		= false;
	static $actions 	= array();
	
	// Función privada - Lanzar error.
	// - $code: Código de error.
	// - $function: Función causante.
	// - $msg: Mensaje del error.
	static function Error($code, $function, $message = '')
	{		
		if(empty($message) AND is_resource(self::$socket))
			$message = socket_strerror(socket_last_error(self::$socket));
		
		BitRock::SetStatus($message, __FILE__, array('function' => $function));
		BitRock::LaunchError($code);
		
		return false;
	}
	
	// Función privada - ¿Hay alguna conexión activa?
	static function Ready()
	{
		if(self::$socket == null OR !is_resource(self::$socket))
			return false;
			
		return true;
	}
	
	// Función - Destruir conexión activa.
	static function Crash()
	{
		if(!self::Ready())
			return;
		
		socket_close(self::$socket);
		Reg('Se ha desconectado del servidor Socket correctamente.');
		
		self::$socket 	= null;
		self::$server 		= false;
		self::$actions 		= array();
	}
	
	// Función - Conectarse y preparar un servidor Socket.
	// - $host: Host de conexión.
	// - $port (Int): Puerto del servidor.
	// - $timeout (Int): Tiempo de ejecución limite.
	// - $e (Bool): ¿Mostrar error en caso de que el servidor se encuentre apagado?
	static function Connect($host, $port = 80, $timeout = 0, $e = false)
	{
		self::Crash();		
		set_time_limit($timeout);
		
		$s = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or self::Error('01s', __FUNCTION__);		
		$r = socket_connect($s, $host, $port);
		
		if($r == false AND $e == true)
			self::Error('02s', __FUNCTION__);

		Reg("Se ha establecido una conexión al servidor Socket en '$host:$port' correctamente.");		
		self::$socket = $s;
		
		return $r;
	}
	
	// Función - Enviar datos al servidor.
	// - $data: Datos a enviar.
	// - $response (Bool): ¿Queremos esperar una respuesta?
	static function Send($data, $response = false)
	{
		if(!self::Ready())
			self::Error('03s', __FUNCTION__);
			
		$len = strlen($data);
		$off = 0;
		
		while($off < $len)
		{
			$send = socket_write(self::$socket, substr($data, $off), $len - $off);

			if(!$send)
				break;

			$off += $send;
		}
		
		if($off < $len)
			self::Error('04s', __FUNCTION__, 'Ha ocurrido un error al mandar '.$data.' al servidor.');
		
		if(!$response)
			return true;
		
		$bytes = @socket_recv(self::$socket, $data, 2048, 0);
		return $data;
	}

	// Función - Preparar un servidor interno.
	// - $local: Dirección para la conexión.
	// - $port (Int): Puerto de escucha.
	// - $timeout (Int): Tiempo de ejecución limite.
	static function Listen($local = '127.0.0.1', $port = 1212, $timeout = 0, $ctimeout = 5)
	{
		return new Server($local, $port, $timeout);
	}
}
?>