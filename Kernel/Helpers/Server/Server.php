<?
/**
 * BeatRock
 *
 * Framework para el desarrollo de aplicaciones web.
 *
 * @author 		Iván Bravo <webmaster@infosmart.mx> @Kolesias123 & Simon Samtleben <web@lemmingzshadow.net>
 * @copyright 	InfoSmart 2013. Todos los derechos reservados.
 * @license 	http://creativecommons.org/licenses/by-sa/2.5/mx/  Creative Commons "Atribución-Licenciamiento Recíproco"
 * @link 		http://beatrock.infosmart.mx/ - http://lemmingzshadow.net/
 * @version 	3.0
 *
 * @package 	Server
 * Permite la creación de un servidor de tipo Sockets o WebSockets
 *
*/

# Acción ilegal.
if ( !defined('BEATROCK') )
	exit;

/*
* Compatibilidad con WebSockets:
* https://github.com/lemmingzshadow/php-websocket/blob/master/server/lib/WebSocket/
*/

class Server
{
	public $logFile 	= './server.log.txt';
	public $debug 		= false;

	public $host 		= '127.0.0.1';
	public $port 		= 1212;

	public $socket 		= null;
	public $webSocket 	= false;

	public $checkOrigin 	= false;
	public $allowedOrigins 	= array();

	public $aClients 	= array();
	public $aSockets 	= array();
	public $ipStorage 	= array();

	public $online 		= 0;
	public $connections = 0;

	public $lastPingCheck 	= 0;
	public $checkPing 		= 8;

	public $lastPrintStats 	= 0;
	public $printStats 		= 20;

	public $idleTimeout 	= 30;

	public $maxConnections 		= 100;
	public $maxConnectionsPerIp = 2;
	public $maxOnline 			= 10;

	public $packetSplit;
	public $packetKey 	= 'packet';
	public $packetValue = 'value';

	public function Error($code, $message)
	{
		// TODO
		echo '[ERROR] ' . $code . ' - ' . $message;
		exit;
	}

	//######################################################################
	// CONSOLA
	//######################################################################

	public function Write($message, $onlyDebug = false)
	{
		$message = '[' . date('H:i:s') . '] ' . $message . PHP_EOL;
		@file_put_contents($this->logFile, $message, FILE_APPEND);

		if ( $onlyDebug AND !$this->debug )
			return;

		//$message = iconv('UTF-8', "ASCII//TRANSLIT//IGNORE", $message);
		echo $message;
		@ob_flush();
	}

	public function BreakLine($line = false)
	{
		if ( $line )
			echo '--------------------------------------------------------------', PHP_EOL;
		else
			echo PHP_EOL;

		@ob_flush();
	}

	//######################################################################
	// SERVIDOR
	//######################################################################

	/**
	 * ¿El servidor esta preparado?
	 */
	private function Prepared()
	{
		if ( $this->socket == null )
			return false;

		return true;
	}

	/**
	 * Destruye el servidor.
	 */
	public function Kill()
	{
		# Debe haber un servidor ya preparado.
		if ( !$this->Prepared() )
			return;

		# [Evento] Antes de matar al servidor.
		$this->PreKill();

		# Matamos al servidor.
		stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);

		# [Eveneto] Después de matar al servidor.
		$this->PostKill();

		# Reiniciamos variables importantes.
		$this->socket 		= null;
		$this->webSocket 	= false;
		$this->aClients 	= array();
		$this->aSockets 	= array();
		$this->ipStorage 	= array();
		$this->online 		= 0;
		$this->connections 	= 0;
	}

	/**
	 * Crea un nuevo servidor.
	 * @param string  $host        Host/Dirección ip para acceder al servidor.
	 * @param integer $port        Puerto para acceder al servidor.
	 * @param boolean $webSocket   ¿Compatible con WebSockets?
	 */
	public function __construct($host = '127.0.0.1', $port = 1212, $webSocket = false)
	{
		# Sin tiempo limite.
		set_time_limit(0);
		# Imprimir logs al momento que se crean.
		ob_implicit_flush(true);

		# Matamos cualquier servidor activo.
		$this->Kill();
		$this->Write('Preparando servidor...');

		# Creamos el Socket.
		$url 		= 'tcp://' . $host . ':' . $port;
		$context 	= stream_context_create();
		$socket 	= stream_socket_server($url, $errno, $err, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);

		# Hubo un problema al crear el Socket.
		if ( !$socket )
			$this->Error('server.socket.create', $err);

		$this->Write('Socket establecido.');

		# Establecemos el Socket del servidor.
		$this->socket 		= $socket;
		$this->aSockets[] 	= $socket;

		# Establecemos la ubicación del servidor.
		$this->host = $host;
		$this->port = $port;

		# Establecemos más información.
		$this->lastPingCheck 	= time();
		$this->lastPrintStats 	= time();
		$this->webSocket 		= $webSocket;

		return $this;
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		$this->Kill();
	}

	/**
	 * Inicia el servidor.
	 */
	public function Start()
	{
		# Debe haber un servidor ya preparado.
		if ( !$this->Prepared() )
			$this->Error('server.need');

		$this->Write('Recibiendo conexiones desde el puerto: ' . $this->port);
		$this->Write('SERVIDOR INICIADO.');

		# ¡Bucle!
		while( true )
			$this->Check();
	}

	/**
	 * Verifica si hay nuevas conexiones o paquetes entrantes.
	 */
	private function Check()
	{
		# Debe haber un servidor ya preparado.
		if ( !$this->Prepared() )
			$this->Error('server.need');

		# Imprimimos estadisticas y verificamos Ping.
		$this->Stats();
		$write = $except = NULL;

		# Obtenemos nuevas conexiones/paquetes.
		$updates = $this->aSockets;
		@stream_select($updates, $write, $except, 0, 5000);

		# Aún no hay nuevas conexiones/paquetes.
		if ( !$updates )
			return;

		# ¡Si hay!
		foreach ( $updates as $socket )
		{
			# Se trata de una nueva conexión.
			if ( $socket == $this->socket )
				$this->NewConnection();

			# Se trata de un paquete.
			else
				$this->NewPacket($socket);
		}
	}

	/**
	 * Crea un nuevo cliente.
	 * @param resource $socket  Socket del cliente.
	 * @param string $clientId  Identificación.
	 * @param object $server    Servidor.
	 */
	public function CreateClient($socket, $clientId)
	{
		return new ServerClient($socket, $clientId, $this);
	}

	/**
	 * Recibe una nueva conexión.
	 */
	private function NewConnection()
	{
		# Debe haber un servidor ya preparado.
		if ( !$this->Prepared() )
			$this->Error('server.need');

		# Limite de conexiones superado.
		# [Evento] No más conexiones.
		if ( $this->maxConnections !== 0 AND $this->connections > $this->maxConnections )
			return $this->NoMoreConnections();

		# Limite de clientes online superado.
		# [Evento] Servidor lleno.
		if ( $this->maxOnline !== 0 AND $this->online > $this->maxOnline )
			return $this->FullServer();

		# Obtenemos el socket de entrada.
		$incoming = stream_socket_accept($this->socket);

		# ¡Uy! ¿Se le fue el internet?
		if ( !$incoming )
			return $this->Error('socket.accept', $incoming);

		# Obtenemos una ID única.
		$clientId 	= uniqid();
		# Creamos el nuevo cliente.
		$client 	= $this->CreateClient($incoming, $clientId);

		# Algo ocurrio mal...
		if( $client->socket == NULL )
			return $this->Write('No ha sido posible crear un nuevo cliente.');

		# Guardamos el nuevo cliente.
		$this->aClients[$clientId] 	= $client;
		$this->aSockets[$clientId] 	= $incoming;

		# Agregamos la dirección IP.
		$this->Write('NUEVA CONEXIÓN ENTRANTE #' . $this->connections . ' - ID ASIGNADA: ' . $clientId);
		$this->AddIP($client->GetIp());

		# ¡Esta dirección IP ha superado el limite de conexiones online por IP!
		# [Evento] Limite de conexiones superado.
		if ( $this->GetIPConnections($client->GetIp()) > $this->maxConnectionsPerIp )
			return $client->onLimitIPReached();

		# Una conexión más.
		++$this->connections;

		# El limite de conexiones ha sido superado, advertir.
		# [Evento] Advertir de no más conexiones.
		if ( $this->maxConnections !== 0 AND $this->connections > $this->maxConnections )
			$this->AlertNoMoreConnections();
	}

	/**
	 * Recibe un nuevo paquete.
	 * @param resource $socket Socket del cliente.
	 */
	private function NewPacket($socket)
	{
		# Debe haber un servidor ya preparado.
		if ( !$this->Prepared() )
			$this->Error('server.need');

		# Obtenemos el cliente que envio el paquete.
		$client = $this->GetClientBySocket($socket);

		# ¡Esto no es un cliente!
		if ( !is_object($client) )
			return;

		# Leemos el buffer
		$buffer = $this->ReadBuffer($socket);
		$bytes 	= strlen($buffer);

		# Algo sucedio mal...
		if ( $bytes == 0 )
			return $client->Disconnect();

		else if ( $buffer == false )
			return $this->DestroyClient($client);

		# El cliente acaba de presentar actividad.
		$client->Active();

		# Consola.
		$client->Write('Recibiendo datos (' . trim($buffer) . ')', true);
		$this->BreakLine();

		# Ejecutar el paquete.
		$client->Packet($buffer); 			// En el cliente
		$this->Packet($buffer, $client); 	// En el servidor
	}

	/**
	 * Lee el buffer de entrada.
	 * @param resource $socket Socket
	 */
	public function ReadBuffer($socket)
	{
		$buffer 					= '';
		$buffsize 					= 8192;
		$metadata['unread_bytes'] 	= 0;

		do
		{
			if ( feof($socket) )
				return false;

			$result = fread($socket, $buffsize);

			if ( !$result OR feof($socket) )
				return false;

			$buffer 	.= $result;
			$metadata 	= stream_get_meta_data($socket);
			$buffsize 	= ( $metadata['unread_bytes'] > $buffsize ) ? $buffsize : $metadata['unread_bytes'];
		}
		while( $metadata['unread_bytes'] > 0 );

		return $buffer;
	}

	//######################################################################
	// ESTADISTICAS
	//######################################################################

	/**
	 * Verifica el ping de los clientes e imprime estadisticas
	 * en la consola.
	 */
	public function Stats()
	{
		# Debe haber un servidor ya preparado.
		if ( !$this->Prepared() )
			$this->Error('server.need');

		# ¿Cuantos clientes estan online?
		$count = count($this->aClients);

		# ¡Hora de checar Ping!
		if ( $this->lastPingCheck <= (time() - $this->checkPing) )
		{
			$count 					= $this->CheckPing();
			$this->lastPingCheck 	= time();
		}

		# ¡Hora de imprimir las estadisticas!
		if ( $this->lastPrintStats <= (time() - $this->printStats) )
		{
			$this->PrintStats($count);
			$this->lastPrintStats 	= time();
		}

		# Clientes online.
		$this->online = $count;
	}

	/**
	 * Checa el ping de los clientes.
	 */
	public function CheckPing()
	{
		# Debe haber un servidor ya preparado.
		if ( !$this->Prepared() )
			$this->Error('server.need');

		# Verificamos cliente por cliente.
		foreach ( $this->aClients as $id => $client )
		{
			# Obtenemos su Socket.
			$socket = $client->GetSocket();

			# ¡El cliente ha sido destruido/desconectao!
			if ( $socket == null )
			{
				$this->Write('LA CONEXIÓN ' . $id . ' SE HA DESCONECTADO.');

				# Intentamos destruir el cliente.
				$result = $this->DestroyClientById($id, false);

				# Destrucción fallida, lo eliminamos de nuestras listas.
				if ( !$result )
				{
					unset( $this->aClients[$id] );
					unset( $this->aSockets[$id] );
				}

				# Siguiente.
				continue;
			}

			# El cliente no ha tenido actividad
			# y el tiempo de inactividad esta establecido.
			if ( $client->GetLastActive() <= (time() - $this->idleTimeout) AND $this->idleTimeout !== 0 )
			{
				$this->Write('LA CONEXIÓN ' . $id . ' HA SIDO EXPULSADA POR INACTIVIDAD.');

				# Intentamos destruir el cliente.
				$this->DestroyClient($client);

				# Destrucción fallida, lo eliminamos de nuestras listas.
				if ( !$result )
				{
					unset( $this->aClients[$id] );
					unset( $this->aSockets[$id] );
				}

				# Siguiente.
				continue;
			}
		}

		# Clientes sobrevivientes (online).
		return count($this->aClients);
	}

	/**
	 * Imprime las estadisticas.
	 * @param string $online Clientes online.
	 */
	public function PrintStats($online = '')
	{
		# No hay un número nuevo de clientes online, usar el actual.
		if ( empty($online) )
			$online = $this->online;

		$this->Write($online . ' conexiones online - ' . $this->connections . ' conexiones aceptadas - ' . round(memory_get_usage() / 1024,1) . ' KB de Memoria.');
	}

	//######################################################################
	// CONEXIONES
	//######################################################################

	/**
	 * Envia un paquete a todos los clientes conectados.
	 * @param string $data Paquete
	 */
	public function SendAll($data)
	{
		# Enviamos el paquete a cada cliente.
		foreach ( $this->aClients as $client )
			$client->Send($data, false);
	}

	/**
	 * Obtiene el cliente mediante su Socket.
	 * @param resource $socket Socket
	 */
	public function GetClientBySocket($socket)
	{
		# Checamos cliente por cliente.
		foreach ( $this->aClients as $client )
		{
			# ¡Encontramos al cliente que tiene este Socket!
			if ( $client->socket == $socket )
				return $client;
		}

		# El cliente no existe.
		return false;
	}

	/**
	 * Obtiene el cliente mediante su Id.
	 * @param string $id Identificación.
	 */
	public function GetClientByID($id)
	{
		# El cliente no es un objeto, no existe.
		if ( !is_object($this->aClients[$id]) )
			return false;

		return $this->aClients[$id];
	}

	/**
	 * Obtiene el Socket mediante la Id de un cliente.
	 * @param string $id Identificación.
	 */
	public function GetSocketByID($id)
	{
		# El Socket no es un recurso, no es válido.
		if ( !is_resource($this->aClients[$id]->socket) )
			return false;

		return $this->aClients[$id]->socket;
	}

	/**
	 * Destruye un cliente.
	 * @param object  $client     Cliente
	 * @param boolean $disconnect ¿Intentar desconectarlo?
	 */
	public function DestroyClient($client, $disconnect = true)
	{
		# Obtenemos su Identificación e Ip.
		$id = $client->GetID();
		$ip = $client->GetIp();

		# Intentamos desconectarlo.
		if ( $disconnect )
			$client->Disconnect(false);

		# Removemos esta Ip.
		$this->RemoveIP($ip);

		# Lo quitamos de nuestras listas.
		unset($this->aClients[$id]);
		unset($this->aSockets[$id]);
	}

	/**
	 * Destruye un cliente mediante su Socket.
	 * @param resource  $socket    Socket
	 * @param boolean $disconnect  ¿Intentar desconectarlo?
	 */
	public function DestroyClientBySocket($socket, $disconnect = true)
	{
		# Obtenemos el cliente que tiene este Socket.
		$client = $this->GetClientBySocket($socket);

		# No se encontro...
		if ( !$client )
			return false;

		# Destruimos al cliente.
		$this->DestroyClient($client, $disconnect);
	}

	/**
	 * Destruye un cliente mediante su Identificación.
	 * @param string  $id         Identificación.
	 * @param boolean $disconnect ¿Intentar desconectarlo?
	 */
	public function DestroyClientById($id, $disconnect = true)
	{
		# Obtenemos el cliente que tiene esta Id.
		$client = $this->GetClientByID($id);

		# No se encontro...
		if ( !$client )
			return false;

		# Destruimos al cliente.
		$this->DestroyClient($client, $disconnect);
	}

	/**
	 * Agrega una Ip a la base de conexiones.
	 * @param string $ip Dirección Ip.
	 */
	public function AddIP($ip)
	{
		# Es la primera vez que se conecta, establecer a 0 su contador.
		if ( !is_numeric($this->ipStorage[$ip]) )
			$this->ipStorage[$ip] = 0;

		# Esta Ip tiene otra conexión más.
		++$this->ipStorage[$ip];
	}

	/**
	 * Remueve una Ip de la base de conexiones.
	 * @param string $ip Dirección Ip.
	 */
	public function RemoveIP($ip)
	{
		# Una conexión de esta Ip menos.
		--$this->ipStorage[$ip];

		# Esta Ip ya no tiene ninguna conexión, eliminarla.
		if ( $this->ipStorage[$ip] <= 0 )
			unset($this->ipStorage[$ip]);
	}

	/**
	 * Obtiene el número de conexiones online de una Ip.
	 * @param string $ip Dirección Ip.
	 */
	public function GetIPConnections($ip)
	{
		# No existe, establecer a 0 su contador.
		if ( !is_numeric($this->ipStorage[$ip]) )
			$this->ipStorage[$ip] = 0;

		return $this->ipStorage[$ip];
	}

	//######################################################################
	// EVENTOS
	//######################################################################

	/**
	 * [Evento]
	 * Se ejecuta antes de matar al servidor.
	 */
	public function PreKill()
	{

	}

	/**
	 * [Evento]
	 * Se ejecuta después de matar al servidor.
	 */
	public function PostKill()
	{
		$this->Write('SERVIDOR APAGADO CORRECTAMENTE');
	}

	/**
	 * [Evento]
	 * Se ejecuta cuando el servidor esta lleno.
	 */
	public function FullServer()
	{

	}

	/**
	 * [Evento]
	 * Se ejecuta cuando ya no se aceptan más conexiones.
	 */
	public function NoMoreConnections()
	{
		return false;
	}

	/**
	 * [Evento]
	 * Se ejecuta cuando se debe alerta de ya no aceptar más conexiones.
	 */
	public function AlertNoMoreConnections()
	{
		$this->Write('Limite de conexiones superada, ya no se aceptarán más conexiones.');
	}

	//######################################################################
	// PAQUETES
	//######################################################################

	/**
	 * Ejecuta un paquete.
	 * @param string $packet Paquete recibido.
	 * @param object $client Cliente que lo envio.
	 */
	public function Packet($packet, $client)
	{

	}

	//######################################################################
	// UTILIDADES
	//######################################################################

	public function SetLogFile($path)
	{
		$this->logFile = $path;
		return $this;
	}

	public function SetDebug($value = false)
	{
		$this->debug = $value;
		return $this;
	}

	public function GetOnline($checkPing = false)
	{
		$online = $this->online;

		if ( $checkPing )
			$online = $this->CheckPing();

		return $online;
	}

	public function SetMaxConnections($value = 100)
	{
		$this->maxConnections = $value;
		return $this;
	}

	public function SetMaxConnectionsPerIp($value = 2)
	{
		$this->maxConnectionsPerIp = $value;
		return $this;
	}

	public function SetMaxOnline($value = 60)
	{
		$this->maxOnline = $value;
		return $this;
	}

	/**
	 * ¿Es un servidor para WebSockets?
	 */
	public function IsWebSocket()
	{
		return $this->webSocket;
	}

	/**
	 * ¿Esta permitido este dominio?
	 * @param string $domain Dominio.
	 */
	public function AllowedOrigin($domain = '')
	{
		# Dominio vacio, retornar si la verificación esta activada.
		if ( empty($domain) )
			return $this->checkOrigin;

		# Quitamos cosas innecesarias...
		$domain = str_replace('http://', '', 	$domain);
		$domain = str_replace('https://', '', 	$domain);
		$domain = str_replace('www.', '', 		$domain);
		$domain = str_replace('/', '', 			$domain);

		# ¿Este dominio se encuentra en la lista blanca?
		return isset($this->allowedOrigins[$domain]);
	}

	/**
	 * Establece si estará permitido verificar el dominio de origen.
	 * @param boolean $value Valor
	 */
	public function SetCheckOrigin($value = false)
	{
		$this->checkOrigin = $value;
		return $this;
	}

	/**
	 * Agrega un dominio a la lista blanca de origenes.
	 * @param string $domain Dominio.
	 */
	public function AddAllowedOrigin($domain)
	{
		# Ya ha sido agregado.
		if ( isset($this->allowedOrigins[$domain]) )
			return $this;

		$this->allowedOrigins[$domain] = true;
		return $this;
	}

	/**
	 * Remueve un dominio de la lista blanca de origenes.
	 * @param string $domain Dominio.
	 */
	public function RemoveAllowedOrigin($domain)
	{
		unset($this->allowedOrigins[$domain]);
		return $this;
	}
}
?>