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
	public static $mem = null;

	// Función privada - Lanzar error.
	// - $function: Función causante.
	// - $msg: Mensaje del error.
	public static function Error($code, $function, $msg = '')
	{
		BitRock::setStatus($msg, __FILE__, Array('function' => $function));
		BitRock::launchError($code);
		
		return false;
	}

	// Función privada - ¿Hay alguna conexión activa?
	public static function Ready()
	{
		return self::$mem == null ? false : true;
	}

	// Función - Destruir conexión activa.
	public static function Crash()
	{
		if(!self::Ready())
			return;

		self::$mem->close();
		BitRock::log('Se ha desconectado del servidor Memcache correctamente.');

		self::$mem = null;
	}

	// Función - Limpiar todo el caché.
	public static function Clean()
	{
		if(!self::Ready())
			return;

		self::$mem->flush();
	}

	// Función - Inicialización y conexión al servidor Memcache.
	public static function Init()
	{
		global $config;
		$mem = $config['memcache'];

		if(empty($mem['host']) OR !is_numeric($mem['port']))
			return false;

		$memcache = new Memcache();
		$memcache->connect($mem['host'], $mem['port']) or self::Error('11m', __FUNCTION__);

		self::$mem = $memcache;

		BitRock::log('Se ha establecido una conexión al servidor MemCache en '.$mem['host'].' correctamente.', 'memcache');
		$session_save = "tcp://$mem[host]:$mem[port]?persisten=1&weight=2&timeout=3&retry_interval=10,  ,tcp://$mem[host]:$mem[port]  ";

		ini_set('session.save_handler', 'memcache');
		ini_set('session.save_path', $session_save);
	}

	// Función - Establecer un nuevo parametro de caché.
	public static function Set($param, $value, $expire = 0)
	{
		if(!self::Ready())
			return self::Error('13m', __FUNCTION__);

		self::$mem->set($param, $value, 0, $expire) or self::Error('12m', __FUNCTION__, 'No se ha podido guardar "'.$value.'".');
	}

	// Función - Establecer un nuevo parametro de caché con uso de Alias.
	public static function SetM($param, $value, $expire = 0)
	{
		if(!self::Ready())
			return self::Error('13m', __FUNCTION__);

		global $site;
		$a = !empty($site['cookie_alias']) ? $site['cookie_alias'] : $_SESSION[ROOT]['cookie_alias'];

		self::$mem->set($a . $param, $value, MEMCACHE_COMPRESSED, $expire) or self::Error('12m', __FUNCTION__, 'No se ha podido guardar "'.$value.'".');
	}

	// Función - Obtener un parametro de la caché.
	public static function Get($param)
	{
		if(!self::Ready())
			return self::Error('13m', __FUNCTION__);

		//global $site;
		//$a = !empty($site['cookie_alias']) ? $site['cookie_alias'] : $_SESSION[ROOT]['cookie_alias'];

		return self::$mem->get($param);
	}

	// Función - Obtener un parametro de la caché con uso de Alias.
	public static function GetM($param)
	{
		if(!self::Ready())
			return self::Error('13m', __FUNCTION__);

		global $site;
		$a = !empty($site['cookie_alias']) ? $site['cookie_alias'] : $_SESSION[ROOT]['cookie_alias'];

		return self::$mem->get($a . $param);
	}

	// Función - Eliminar una parametro de la caché.
	public static function Delete($param)
	{
		if(!self::Ready())
			return self::Error('13m', __FUNCTION__);

		return self::$mem->delete($param);
	}
}
?>