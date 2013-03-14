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
 * @package 	Socket
 * Permite crear una conexión de tipo Socket.
 *
*/

# Acción ilegal.
if( !defined('BEATROCK') )
	exit;

class Socket extends Base
{
	public $socket = null;

	function Error($code, $message = '')
	{
		if ( empty($message) AND $this->Connected() )
			$message = socket_strerror(socket_last_error($this->socket));

		return parent::Error($code, $message);
	}

	function __construct($host, $port = 80, $timeout = 0, $showError = false)
	{
		$this->Disconnect();
		set_time_limit($timeout);

		$socket 	= socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or $this->Error('socket.create');
		$connect 	= socket_connect($socket, $host, $port);

		if ( !$connect AND $showError )
			$this->Error('socket.connect');

		Reg();
		$this->socket = $socket;

		return $this;
	}

	function Connected()
	{
		if ( $this->socket == null )
			return false;

		return true;
	}

	function Disconnect()
	{
		if ( !$this->Connected() )
			return;

		socket_close($this->socket);

		$this->socket = null;
	}

	function Send($data, $waitResponse = false)
	{
		if ( !$this->Connected() )
			$this->Error('socket.need.connection');

		$len = strlen($data);
		$off = 0;

		while ( $off < $len )
		{
			$send = socket_write($this->socket, substr($data, $off), $len - $off);

			if ( !$send )
				break;

			$off += $send;
		}

		if ( $off < $len )
			$this->Error('socket.send', 'Ha ocurrido un problema al enviar el paquete al servidor: ' . $data);

		if ( !$waitResponse )
			return true;

		$bytes = @socket_recv($this->socket, $data, 2048, 0);
		return $data;
	}
}
?>