<?
#####################################################
## 					 BeatRock
#####################################################
## Framework avanzado de procesamiento para PHP.
#####################################################
## InfoSmart © 2013 Todos los derechos reservados.
## http://www.infosmart.mx/
#####################################################
## http://beatrock.infosmart.mx/
#####################################################

# Acción ilegal.
if( !defined('BEATROCK') )
	exit;

###############################################################
## Controlador Site
###############################################################
## Contiene las funciones y herramientas
## necesarias para interactuar el sitio y la tabla
## site_config de su base de datos.
###############################################################

class Site extends BaseStatic
{
	###############################################################
	## Obtener la configuración del sitio.
	###############################################################
	function __construct()
	{
		global $site;

		Query('site_config', true)->Select(array('var', 'result'))->Run();
		$site = array();
		
		while( $row = Assoc() )
		{
			$i = 0;
			
			foreach( $row as $param => $value )
			{
				++$i;
				
				if($i == 1)
					$p = $value;
				else
					$v = $value;
			}
			
			$site[$p] = $v;
		}

		$site['name'] = '';
		self::PrepareCache();

		Free();
		return $site;
	}
	
	###############################################################
	## Actualizar configuración del sitio.
	## - $var: 		Variable.
	## - $value: 	Nuevo valor.
	###############################################################
	static function Update($var, $value)
	{
		global $site;

		Query('site_config')->Update(array('result' => $value))->Add('var', $var)->Run();		
		$site[$var] = $value;
	}
	
	###############################################################
	## Agregar visita al sitio.
	###############################################################
	static function Visit()
	{
		global $site;

		# Información acerca de la visita.
		$host 		= Client::Get('host');
		$browser 	= Client::Get('browser');
		$type 		= 'desktop';
		
		# ¿Nos esta visitando un dispositivo móvil?
		if( Client::IsMobile() )
			$type = 'mobile';
				
		# ¿No esta visitando un robot/araña?
		if( Client::IsBOT() )
			$type = 'bot';

		# Registrar todas las visitas.
		if( $site['register_all_visits'] == 'true' )
		{
			Insert('site_visits_total', array(
				'ip' 		=> IP,
				'host' 		=> $host,
				'agent' 	=> _f(AGENT),
				'browser' 	=> $browser,
				'path' 		=> _f(PATH_NOW),
				'referer' 	=> _f(FROM),
				'phpid' 	=> session_id(),
				'type' 		=> $type,
				'date' 		=> time()
			));
		}

		# ¿Esta visita ya ha sido registrada?
		$rows = Query('site_visits')->Select('null')->Add('ip', IP)->Add('host', $host, 'OR')->Add('phpid', session_id(), 'OR')->Limit()->Rows();
			
		# Al parecer no.
		if( $rows == 0 )
		{
			Insert('site_visits', array(
				'ip' 		=> IP,
				'host' 		=> $host,
				'agent' 	=> _f(AGENT),
				'browser' 	=> $browser,
				'referer' 	=> _f(FROM),
				'phpid'		=> session_id(),
				'type' 		=> $type,
				'date' 		=> time(),
				'last'		=> time()
			));
			
			q("UPDATE {DP}site_config SET result = result + 1 WHERE var = 'site_visits' LIMIT 1");
		}

		# Si, actualizamos la información de la visita.
		else
			Query('site_visits')->Update(array('last' => time(), 'host' => $host, 'agent' => _f(AGENT), 'browser' => $browser, 'referer' => _f(FROM)))->Add('ip', IP)->Add('host', $host, 'OR')->Add('phpid', session_id(), 'OR')->Limit()->Run();
	}

	###############################################################
	## Actualizar información de la visita actual.
	###############################################################
	static function UpdateMyVisit($params)
	{
		Query('site_visits')->Update($params)->Add('ip', IP)->Run();
	}
	
	###############################################################
	## Checar los cronometros.
	###############################################################
	static function CheckTimers()
	{
		$q = Query('site_timers')->Select()->Run();
		
		while( $row = Assoc() )
		{
			if( $row['time'] == '0' OR $row['nexttime'] >= time() )
				continue;
				
			self::Timer($row['action']);
			$next = Core::Time($row['time']);

			Query('site_timers')->Update(array('nexttime' => $next))->Add('id', $row['id'])->Run();
		}
	}
	
	###############################################################
	## Ejecuta un cronometro.
	## - $a: 	Cronometro.
	###############################################################
	static function Timer($action)
	{
		if( empty($action) )
			return false;
			
		if( !file_exists(APP . 'Timers' . DS . $action . '.php') )
			return false;

		include APP . 'Timers' . DS . $action . '.php';		
		Reg('%timer.correct%' . $action);
	}
	
	###############################################################
	## Obtener datos de una tabla.
	## - $table (countrys, maps, news): 	Tabla de donde se obtendrá.
	## - $limit (int): 						Limite de valores a obtener.
	###############################################################
	static function Get($table = 'countrys', $limit = 0)
	{
		$q = Query('site_' . $table)->Select();

		if( $table == 'countrys' )
			$q->Order('name', 'ASC');
		else
			$q->Order('id', 'DESC');		
			
		if($limit !== 0)
			$q->Limit($limit);
			
		return $q->Run();
	}
	
	###############################################################
	## Obtener noticia.
	## - $id (int): 	ID de la noticia.
	###############################################################
	static function GetNew($id)
	{
		$q = Query('site_news')->Select()->Add('id', $id)->Limit()->Run();
		return ( Rows() > 0 ) ? $q : false;
	}
	
	###############################################################
	## Guardar logs actuales en la base de datos.
	###############################################################
	static function SaveLog()
	{
		$logs = Bit::$logs['all']['text'];
		
		if( empty($logs) )
			return;

		Insert('site_logs', array(
			'logs' 		=> _f($logs, false),
			'phpid' 	=> session_id(),
			'path' 		=> _f(PATH),
			'date' 		=> time()
		));
	}
	
	###############################################################
	## Obtener información de caché de una página.
	## - $page: 	Página.
	###############################################################
	static function GetCache($page)
	{
		$q = Query('site_cache')->Select(array('id','page','time'))->Add('page', $page)->Limit()->Run();
		return ( Rows($q) > 0 ) ? Assoc($q) : false;
	}

	###############################################################
	## Prepara el uso de caché para las tablas especificadas.
	###############################################################
	static function PrepareCache()
	{
		global $site, $config;

		if( !extension_loaded('mysqlnd_qc') OR empty($site['cache_tables']) )
			return;

		$tables = explode(',', $site['cache_tables']);

		foreach( $tables as $table )
		{
			$table = trim(DB_PREFIX . $table);
			mysqlnd_qc_set_cache_condition(MYSQLND_QC_CONDITION_META_SCHEMA_PATTERN, $config['mysql']['name'] . '.' . $table);
		}
	}
}
?>