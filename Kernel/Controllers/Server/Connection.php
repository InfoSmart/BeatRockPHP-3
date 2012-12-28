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

class Connection
{
	// Socket de la conexión.
	public $socket = null;
	// Última actividad.
	public $last 	= 0;
	// ID de la conexión.
	public $id 	= 0;
	
	// Función - Preparar una nueva conexión.
	function __construct($socket, $id)
	{
		$this->socket 	= $socket;
		$this->id 		= $id;
		$this->last 	= time();
	}

	// Función privada - ¿Hay alguna conexión activa?
	function Ready()
	{
		if($this->socket == null OR !is_resource($this->socket))
			return false;
			
		return true;
	}
	
	// Función - Destruir conexión activa.
	function Kill()
	{
		if(!$this->Ready())
			return;

		socket_shutdown($this->socket);
		socket_close($this->socket);
		
		Server::Write('CONEXIÓN #' . $this->id . ' CERRADA.');
		$this->socket = null;
	}

	// Función - Liberar conexión activa.
	function Clean()
	{
		if(!$this->Ready())
			return;
		
		socket_close($this->socket);
	}
	
	// Función - Enviar datos.
	// - $data: Datos a enviar.
	// - $response (Bool): ¿Queremos esperar una respuesta?
	function Send($data, $response = false)
	{
		if(!$this->Ready())
			return false;
			
		$len = strlen($data);
		$off = 0;		
		
		while($off < $len)
		{
			$send = socket_write($this->socket, substr($data, $off), $len - $off);

			if(!$send)
				break;
			
			$off += $send;
		}
		
		if($off < $len)
		{
			Server::Write('Ha ocurrido un error al intentar enviar los datos '.$data.'.');
			return false;
		}			

		Server::Write("Se ha enviado '$data' a la conexión #" . $this->id . ".");
			
		if(!$response)
		{
			$this->Clean();
			return true;
		}
		
		$bytes = @socket_recv($this->socket, $data, 2048, 0);
		$this->Clean();

		return $data;
	}
}
?>