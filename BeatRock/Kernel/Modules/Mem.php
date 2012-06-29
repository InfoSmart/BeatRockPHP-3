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

class Mem
{
	static $mem = false;

	// Lanzar error.
	// - $function: Función causante.
	// - $message: Mensaje del error.
	static function Error($code, $function, $message = '')
	{
		Lang::SetSection('mod.mem');

		BitRock::SetStatus($message, __FILE__, array('function' => $function));
		BitRock::LaunchError($code);

		return false;
	}

	// ¿Hay alguna conexión activa?
	static function Ready()
	{
		return (self::$mem == false) ? false : true;
	}

	// Destruir conexión activa.
	static function Crash()
	{
		if(!self::Ready())
			return;

		self::$mem->close();
		self::$mem = false;

		Reg('%connection.out%');
	}

	// Limpiar todo el caché.
	static function Clean()
	{
		if(!self::Ready())
			return;

		self::$mem->flush();
	}

	// Inicialización y conexión al servidor Memcache.
	static function Init()
	{
		Lang::SetSection('mod.mem');
		
		global $config;
		$mem = $config['memcache'];

		if(empty($mem['host']) OR !is_numeric($mem['port']))
			return false;

		$memcache = new Memcache;
		$memcache->connect($mem['host'], $mem['port']) or self::Error('memcache.connect', __FUNCTION__);

		if($memcache->getServerStatus == 0)
			self::Error('memcache.connect', __FUNCTION__);

		self::$mem = $memcache;

		Reg('%connection.correct% ' . $mem['host'], 'memcache');
		$session_save = "tcp://$mem[host]:$mem[port]?persisten=1&weight=2&timeout=3&retry_interval=10,  ,tcp://$mem[host]:$mem[port]  ";

		ini_set('session.save_handler', 'memcache');
		ini_set('session.save_path', $session_save);
	}

	// Establecer un nuevo parametro de caché.
	static function Set($param, $value, $expire = 0)
	{
		if(!self::Ready())
			return self::Error('memcache.need.connection', __FUNCTION__);

		self::$mem->set($param, $value, MEMCACHE_COMPRESSED, $expire) or self::Error('memcache.save', __FUNCTION__, '%error.save% "'.$value.'".');
	}

	// Establecer un nuevo parametro de caché con uso de Alias.
	static function SetM($param, $value, $expire = 0)
	{
		if(!self::Ready())
			return self::Error('memcache.need.connection', __FUNCTION__);

		global $site;
		$prefix = (!empty($site['cookie_alias'])) ? $site['cookie_alias'] : $_SESSION[ROOT]['cookie_alias'];

		self::$mem->set($prefix . $param, $value, MEMCACHE_COMPRESSED, $expire) or self::Error('memcache.save', __FUNCTION__, '%error.save% "'.$value.'".');
	}

	// Obtener un parametro de la caché.
	static function Get($param)
	{
		if(!self::Ready())
			return self::Error('memcache.need.connection', __FUNCTION__);

		return self::$mem->get($param);
	}

	// Obtener un parametro de la caché con uso de Alias.
	static function GetM($param)
	{
		if(!self::Ready())
			return self::Error('memcache.need.connection', __FUNCTION__);

		global $site;
		$prefix = !empty($site['cookie_alias']) ? $site['cookie_alias'] : $_SESSION[ROOT]['cookie_alias'];

		return self::$mem->get($prefix . $param);
	}

	// Eliminar una parametro de la caché.
	static function Delete($param)
	{
		if(!self::Ready())
			return self::Error('memcache.need.connection', __FUNCTION__);

		return self::$mem->delete($param);
	}

	// Eliminar una parametro de la caché con uso de Alias.
	static function DeleteM($param)
	{
		if(!self::Ready())
			return self::Error('13m', __FUNCTION__);

		global $site;
		$prefix = (!empty($site['cookie_alias'])) ? $site['cookie_alias'] : $_SESSION[ROOT]['cookie_alias'];

		return self::$mem->delete($prefix . $param);
	}
}
?>