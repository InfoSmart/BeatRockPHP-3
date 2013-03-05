<?
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart © 2013 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

# Acción ilegal.
if( !defined('BEATROCK') )
	exit;	

class Mem extends BaseStatic
{
	static $server = null;

	###############################################################
	## ¿Ya nos hemos conectado al servidor?
	###############################################################
	static function Connected()
	{
		return ( self::$server == null ) ? false : true;
	}

	###############################################################
	## Desconectarse del servidor.
	###############################################################
	static function Disconnect()
	{
		# No hemos establecido una conexión.
		if( !self::Connected() )
			return;

		# Cerrar y borrar.
		self::$server->close();
		self::$server = null;

		Reg('%connection.out%');
	}

	###############################################################
	## Limpiar la caché.
	###############################################################
	static function Clean()
	{
		# No hemos establecido una conexión.
		if( !self::Connected() )
			return;

		self::$server->flush();
	}

	###############################################################
	## Conexión al servidor Memcache
	###############################################################
	function __construct()
	{
		Lang::SetSection('helper.mem');
		
		global $config;
		$mem = $config['memcache'];

		# El Host/Puerto estan vacios o la conexión esta desactivada.
		if( empty($mem['host']) OR !is_numeric($mem['port']) OR !$mem['enabled'] )
			return false;

		# No se ha cargado la extensión memcache
		if( !extension_loaded('memcache') )
			self::Error('memcache.no.extension');

		# Establecemos la conexión al servidor.
		$memcache = new Memcache;
		$memcache->connect($mem['host'], $mem['port']) or self::Error('memcache.connect', __FUNCTION__);

		# Al parecer hubo un error.
		if( $memcache->getServerStatus == 0 )
			self::Error('memcache.connect');

		self::$server = $memcache;

		Reg('%connection.correct% ' . $mem['host'], 'memcache');

		# Establecemos al servidor como el nuevo lugar para guardar las sesiones ($_SESSION)
		$session_save = "tcp://$mem[host]:$mem[port]?persistent=1&weight=2&timeout=3&retry_interval=10,  ,tcp://$mem[host]:$mem[port]  ";

		ini_set('session.save_handler', 'memcache');
		ini_set('session.save_path', $session_save);
	}

	###############################################################
	## Guardar un nuevo objeto en el servidor.
	## - $key: 				Nombre del objeto.
	## - $value: 			Valor
	## - $expire (int): 	Tiempo en segundos o Unix de expiración.
	## - $prefix (bool):	¿Usar prefijo del sitio?
	###############################################################
	static function Set($key, $value, $expire = 0, $prefix = false)
	{
		# No hemos establecido una conexión.
		if( !self::Connected() )
			return self::Error('memcache.need.connection');

		# Usar prefijo.
		if( $prefix )
		{
			global $site;

			# Encontramos el prefijo y la ajustamos en la llave.
			$prefix = ( !empty($site['cookie_alias']) ) ? $site['cookie_alias'] : $_SESSION[ROOT]['cookie_alias'];
			$key 	= $prefix . $key;
		}

		# Guardamos en el servidor.
		$result = self::$server->set($key, $value, MEMCACHE_COMPRESSED, $expire);

		# Al parecer ocurrio un error.
		if( !$result )
			self::Error('memcache.save', '%error.save% "'.$value.'".');
	}

	###############################################################
	## Obtiene un objeto guardado del servidor.
	## - $key: 	Nombre del objeto.
	###############################################################
	static function Get($key)
	{
		# No hemos establecido una conexión.
		if( !self::Connected() )
			return self::Error('memcache.need.connection');

		# Obtenemos el objeto.
		$result = self::$server->get($key);

		# No hemos podido encontrar el objeto
		# Intentar con prefijo.
		if( empty($result) )
		{
			global $site;

			# Encontramos el prefijo y la ajustamos en la llave.
			$prefix = ( !empty($site['cookie_alias']) ) ? $site['cookie_alias'] : $_SESSION[ROOT]['cookie_alias'];
			$key 	= $prefix . $key;

			# Intentamos obtener el objeto.
			$result = self::$server->get($key);
		}

		return ( empty($result) ) ? false : $result;
	}

	###############################################################
	## Elimina un objeto guardado en el servidor
	## - $key: 	Nombre del objeto.
	###############################################################
	static function Delete($key)
	{
		# No hemos establecido una conexión.
		if( !self::Connected() )
			return self::Error('memcache.need.connection');

		# Eliminamos el objeto.
		$result = self::$server->delete($key);

		# No hemos podido encontrar el objeto.
		# Intentar con prefijo.
		if( !$result )
		{
			global $site;

			# Encontramos el prefijo y la ajustamos en la llave.
			$prefix = ( !empty($site['cookie_alias']) ) ? $site['cookie_alias'] : $_SESSION[ROOT]['cookie_alias'];
			$key 	= $prefix . $key;

			# Intentamos eliminar el objeto.
			$result = self::$server->delete($key);
		}

		return $result;
	}
}
?>