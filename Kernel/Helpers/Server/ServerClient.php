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
 * @package 	Client
 * Inicializa una nueva clase para la administración de una conexión activa
 * en un servidor.
 *
*/

# Acción ilegal.
if ( !defined('BEATROCK') )
	exit;

const 	JSON = 1,
		NO_HYBI10_ENCODE = 2;

class ServerClient
{
	public $server 			= null;
	public $socket 			= null;
	public $lastActive 		= 0;
	public $id 				= 0;
	public $webHandShake 	= '';

	public $packets = array();

	public $ip;
	public $port;

	//######################################################################
	// CONSOLA
	//######################################################################

	public function Write($message)
	{
		$message = '[' . $this->ip . ':' . $this->port . '] - ' . $message;
		$this->server->Write($message);
	}

	//######################################################################
	// CONEXIÓN
	//######################################################################

	public function Disconnect($destroy = true)
	{
		$this->OnDisconnect();

		stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);

		if ( $destroy AND is_object($this->server) )
			$this->server->DestroyClient($this, false);

		$this->server = null;
		$this->socket = null;

		return false;
	}

	public function Close($status)
	{
		$payload 	= str_split(sprintf('%016b', $status), 8);
		$payload[0] = chr(bindec($payload[0]));
		$payload[1] = chr(bindec($payload[1]));
		$payload 	= implode('', $payload);

		switch ( $status )
		{
			case 1000:
				$payload .= 'normal closure';
			break;

			case 1001:
				$payload .= 'going away';
			break;

			case 1002:
				$payload .= 'protocol error';
			break;

			case 1003:
				$payload .= 'unknown data (opcode)';
			break;

			case 1004:
				$payload .= 'frame too large';
			break;

			case 1007:
				$payload .= 'utf8 expected';
			break;

			case 1008:
				$payload .= 'message violates server policy';
			break;
		}

		if ( !$this->SendHybi10($payload, 'close', false) )
			return false;

		$this->Disconnect();
	}

	public function __construct($socket, $id, $server)
	{
		if ( !is_resource($socket) OR !is_object($server) )
			return;

		$this->server 		= $server;
		$this->socket 		= $socket;
		$this->lastActive	= time();
		$this->id 			= $id;

		$info 		= $this->GetSocketInfo();
		$this->ip 	= $info['ip'];
		$this->port = $info['port'];

		$this->PreparePackets();

		if ( !$this->server->IsWebSocket() )
			$this->OnConnect();

		return true;
	}

	public function __destruct()
	{
		$this->Disconnect();
	}

	public function DoHandShake($data)
	{
		$lines = preg_split("/\r\n/", $data);

		if ( !preg_match('/\AGET (\S+) HTTP\/1.1\z/', $lines[0], $matches) )
		{
            $this->Write('Handshake inválido: ' . $lines[0]);
			$this->SendHttpResponse(400);
            return $this->Disconnect();
        }

        $headers = array();

        foreach ( $lines as $line )
		{
            $line = chop($line);

            if ( preg_match('/\A(\S+): (.*)\z/', $line, $matches) )
                $headers[$matches[1]] = $matches[2];
        }

        if ( !isset($headers['Sec-WebSocket-Version']) || $headers['Sec-WebSocket-Version'] < 6 )
		{
			$this->Write('Versión de WebSocket no compatible.');
			$this->SendHttpResponse(501);
			return $this->Disconnect();
		}

		if ( $this->server->AllowedOrigin() )
		{
			$origin = ( isset($headers['Sec-WebSocket-Origin']) ) ? $headers['Sec-WebSocket-Origin'] : false;
			$origin = ( isset($headers['Origin']) ) ? $headers['Origin'] : $origin;

			if ( $origin === false OR empty($origin) )
			{
				$this->Write('No se proporciono origen.');
				$this->SendHttpResponse(401);
				return $this->Disconnect();
			}

			if( !$this->server->AllowedOrigin($origin) )
			{
				$this->Write('Intento de conexión de origen inválido.');
				$this->SendHttpResponse(401);
				return $this->Disconnect();
			}
		}

		$secKey 	= $headers['Sec-WebSocket-Key'];
		$secAccept 	= base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

		$response  = "HTTP/1.1 101 Switching Protocols\r\n";
		$response .= "Upgrade: websocket\r\n";
		$response .= "Connection: Upgrade\r\n";
		$response .= "Sec-WebSocket-Accept: " . $secAccept . "\r\n";

		if( isset($headers['Sec-WebSocket-Protocol']) && !empty($headers['Sec-WebSocket-Protocol']) )
			$response.= "Sec-WebSocket-Protocol: " . substr($path, 1) . "\r\n";

		$response .= "\r\n";

		if( !$this->Send($response) )
			return $this->Disconnect();

		$this->webHandShake = $response;
		$this->OnConnect();
	}

	public function SendHttpResponse($status)
	{
		$header = 'HTTP/1.1 ';

		switch($status)
		{
			case 400:
				$header .= '400 Bad Request';
			break;

			case 401:
				$header .= '401 Unauthorized';
			break;

			case 403:
				$header .= '403 Forbidden';
			break;

			case 404:
				$header .= '404 Not Found';
			break;

			case 501:
				$header .= '501 Not Implemented';
			break;
		}

		$header .= "\r\n";
		$this->Send($header);
	}

	//######################################################################
	// PAQUETES
	//######################################################################

	public function Packet($packet)
	{
		if ( empty($this->webHandShake) AND $this->server->IsWebSocket() )
			return $this->DoHandShake($packet);

		if ( $this->server->IsWebSocket() )
		{
			$packet 	= $this->hybi10Decode($packet);
			$mPacket 	= json_decode($packet, true);
			$key 		= $mPacket[$this->server->packetKey];
			$value 		= $mPacket[$this->server->packetValue];
		}
		else
		{
			$mPacket 	= explode($this->server->packetSplit, $packet);
			$key 		= $mPacket[0];
			$value 		= $mPacket[1];
		}

		$this->ExecApp($key, $value);
	}

	public function Send($data, $encode = 0)
	{
		$this->Write('Intentando enviar: ' . $data . PHP_EOL);

		if ( $encode == JSON AND is_array($data) )
			$data = json_encode($data);

		if ( $this->server->IsWebSocket() )
			$data = $this->hybi10Encode($data);

		$length = strlen($data);

		for ( $written = 0; $written < $length; $written += $fwrite )
		{
			$fwrite = @fwrite($this->socket, substr($data, $written));

			if ( !$fwrite OR $fwrite == 0 )
			{
				$this->Write('No se ha podido enviar el paquete: ' . $data . PHP_EOL);
				return false;
			}
		}

		return $written;
	}

	public function SendHybi10($data, $type = 'text', $masked = true)
	{
		$data 	= $this->hybi10Encode($data, $type, $masked);
		$result = $this->Send($data, NO_HYBI10_ENCODE);

		if ( !$result )
			return $this->Disconnect();

		return $result;
	}

	public function hybi10Encode($payload, $type = 'text', $masked = true)
	{
		$frameHead 		= array();
		$frame 			= '';
		$payloadLength 	= strlen($payload);

		switch ( $type )
		{
			case 'text':
				// first byte indicates FIN, Text-Frame (10000001):
				$frameHead[0] = 129;
			break;

			case 'close':
				// first byte indicates FIN, Close Frame(10001000):
				$frameHead[0] = 136;
			break;

			case 'ping':
				// first byte indicates FIN, Ping frame (10001001):
				$frameHead[0] = 137;
			break;

			case 'pong':
				// first byte indicates FIN, Pong frame (10001010):
				$frameHead[0] = 138;
			break;
		}

		// set mask and payload length (using 1, 3 or 9 bytes)
		if ( $payloadLength > 65535 )
		{
			$payloadLengthBin 	= str_split(sprintf('%064b', $payloadLength), 8);
			$frameHead[1] 		= ( $masked === true ) ? 255 : 127;

			for ( $i = 0; $i < 8; $i++ )
				$frameHead[$i + 2] = bindec($payloadLengthBin[$i]);

			// most significant bit MUST be 0 (close connection if frame too big)
			if ( $frameHead[2] > 127 )
			{
				$this->Close(1004);
				return false;
			}
		}

		else if ( $payloadLength > 125 )
		{
			$payloadLengthBin 	= str_split(sprintf('%016b', $payloadLength), 8);
			$frameHead[1] 		= ($masked === true) ? 254 : 126;
			$frameHead[2] 		= bindec($payloadLengthBin[0]);
			$frameHead[3] 		= bindec($payloadLengthBin[1]);
		}

		else
			$frameHead[1] = ( $masked === true ) ? $payloadLength + 128 : $payloadLength;

		// convert frame-head to string:
		foreach ( array_keys($frameHead) as $i )
			$frameHead[$i] = chr($frameHead[$i]);

		if ( $masked === true )
		{
			// generate a random mask:
			$mask = array();

			for( $i = 0; $i < 4; $i++ )
				$mask[$i] = chr(rand(0, 255));

			$frameHead = array_merge($frameHead, $mask);
		}

		$frame 			= implode('', $frameHead);

		// append payload to frame:
		$framePayload 	= array();

		for ( $i = 0; $i < $payloadLength; $i++ )
			$frame .= ( $masked === true ) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];

		return $frame;
	}

	public function hybi10Decode($data)
	{
		$payloadLength 		= '';
		$mask 				= '';
		$unmaskedPayload 	= '';
		$decodedData 		= array();

		// estimate frame type:
		$firstByteBinary 	= sprintf('%08b', ord($data[0]));
		$secondByteBinary 	= sprintf('%08b', ord($data[1]));
		$opcode 			= bindec(substr($firstByteBinary, 4, 4));
		$isMasked 			= ($secondByteBinary[0] == '1') ? true : false;
		$payloadLength 		= ord($data[1]) & 127;

		// close connection if unmasked frame is received:
		if ( $isMasked === false )
			return $this->Close(1002);

		switch ( $opcode )
		{
			// text frame:
			case 1:
				$decodedData['type'] = 'text';
			break;

			case 2:
				$decodedData['type'] = 'binary';
			break;

			// connection close frame:
			case 8:
				$decodedData['type'] = 'close';
			break;

			// ping frame:
			case 9:
				$decodedData['type'] = 'ping';
			break;

			// pong frame:
			case 10:
				$decodedData['type'] = 'pong';
			break;

			default:
				// Close connection on unknown opcode:
				return $this->Close(1003);
			break;
		}

		if ( $payloadLength === 126 )
		{
		   $mask 			= substr($data, 4, 4);
		   $payloadOffset 	= 8;
		   $dataLength 		= bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
		}

		elseif( $payloadLength === 127 )
		{
			$mask 			= substr($data, 10, 4);
			$payloadOffset 	= 14;
			$tmp 			= '';

			for ( $i = 0; $i < 8; ++$i )
				$tmp .= sprintf('%08b', ord($data[$i+2]));

			$dataLength = bindec($tmp) + $payloadOffset;
			unset($tmp);
		}

		else
		{
			$mask 			= substr($data, 2, 4);
			$payloadOffset 	= 6;
			$dataLength 	= $payloadLength + $payloadOffset;
		}

		/**
		 * We have to check for large frames here. socket_recv cuts at 1024 bytes
		 * so if websocket-frame is > 1024 bytes we have to wait until whole
		 * data is transferd.
		 */
		if ( strlen($data) < $dataLength )
			return false;

		if ( $isMasked === true )
		{
			for ( $i = $payloadOffset; $i < $dataLength; $i++ )
			{
				$j = $i - $payloadOffset;

				if ( isset($data[$i]) )
					$unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
			}

			$decodedData['payload'] = $unmaskedPayload;
		}
		else
		{
			$payloadOffset 			= $payloadOffset - 4;
			$decodedData['payload'] = substr($data, $payloadOffset);
		}

		return $decodedData;
	}

	//######################################################################
	// UTILIDADES
	//######################################################################

	public function GetSocketInfo()
	{
		$info 	= stream_socket_get_name($this->socket, true);
		$tmp 	= explode(':', $info);

		return array('ip' => $tmp[0], 'port' => $tmp[1]);
	}

	public function GetIp()
	{
		return $this->ip;
	}

	public function GetPort()
	{
		return $this->port;
	}

	public function GetSocket()
	{
		return $this->socket;
	}

	public function GetLastActive()
	{
		return $this->lastActive;
	}

	public function Active($time = '')
	{
		if ( !is_numeric($time) )
			$time = time();

		$this->lastActive = time();
	}

	public function GetID()
	{
		return $this->id;
	}

	//######################################################################
	// EVENTOS
	//######################################################################

	public function onConnect()
	{
		$this->Write($this->ip . ' se ha conectado correctamente.');
	}

	public function onDisconnect()
	{

	}

	public function onLimitIPReached()
	{
		$this->Write('LA CONEXIÓN ' . $this->id . ' HA SUPERADO LAS CONEXIONES POR IP.');
		$this->Disconnect();
	}

	//######################################################################
	// PAQUETES
	//######################################################################

	public function PreparePackets()
	{

	}

	public function on($packet, $action)
	{
		$this->packets[$packet] = $action;
	}

	public function ExecApp($packet, $value)
	{
		if ( !isset($this->packets[$packet]) )
			return;

		$method = $this->packets[$packet];
		$method($value);
	}
}
?>