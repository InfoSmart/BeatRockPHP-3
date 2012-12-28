<?
#####################################################
## 					 BeatRock
#####################################################
## Framework avanzado de procesamiento para PHP.
#####################################################
## InfoSmart © 2012 Todos los derechos reservados.
## http://www.infosmart.mx/
#####################################################
## http://beatrock.infosmart.mx/
#####################################################

// Acción ilegal.
if(!defined('BEATROCK'))
	exit;

## --------------------------------------------------
## Controlador Site
## --------------------------------------------------
## Contiene las funciones y herramientas
## necesarias para interactuar el sitio y la tabla
## site_config de su base de datos.
## --------------------------------------------------

class Site
{
	// Obtener la configuración del sitio.
	function __construct()
	{
		global $site;

		Query('site_config', true)->Select(array('var', 'result'))->Run();
		$site 	= array();
		
		while($row = fetch_assoc())
		{
			$i = 0;
			
			foreach($row as $param => $value)
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

		free_result();
		return $site;
	}
	
	// Actualizar configuración del sitio.
	// - $var: Variable.
	// - $value: Nuevo valor.
	static function Update($var, $value)
	{
		global $site;

		Query('site_config')->Update(array('result' => $value))->Add('var', $var)->Run();		
		$site[$var] = $value;
	}
	
	// Agregar visita al sitio.
	static function Visit()
	{
		global $site;

		$host 		= Client::Get('host');
		$browser 	= Client::Get('browser');
		$type 		= 'desktop';
			
		if(Core::IsMobile())
			$type = 'mobile';
			
		if(Core::IsBOT())
			$type = 'bot';

		if($site['register_all_visits'] == 'true')
		{
			Insert('site_visits_total', array(
				'ip' 		=> IP,
				'host' 		=> $host,
				'agent' 	=> _F(AGENT),
				'browser' 	=> $browser,
				'path' 		=> _F(PATH_NOW),
				'referer' 	=> _F(FROM),
				'phpid' 	=> session_id(),
				'type' 		=> $type,
				'date' 		=> time()
			));
		}

		if(_SESSION('visit') == 'true')
			return;

		$rows = Query('site_visits')->Select('null')->Add('ip', IP)->Add('host', $host, 'OR')->Add('phpid', session_id(), 'OR')->Limit()->Rows();
		
		if($rows !== 0)
			return;

		Insert('site_visits', array(
			'ip' 		=> IP,
			'host' 		=> $host,
			'agent' 	=> _F(AGENT),
			'browser' 	=> $browser,
			'referer' 	=> _F(FROM),
			'phpid'		=> session_id(),
			'type' 		=> $type,
			'date' 		=> time()
		));
		
		_SESSION('visit', 'true');
		q("UPDATE site_config SET result = result + 1 WHERE var = 'site_visits' LIMIT 1");
	}

	// Actualizar información de la visita actual.
	static function UpdateMy($params)
	{
		Query('site_visits')->Update($params)->Add('ip', IP)->Run();
	}

	// Proteger el sitio.
	static function Protect()
	{
		global $site;

		if($site['ddos_time'] == '0')
			return false;
		
		$row = Query('site_visits')->Select()->Add('ip', IP)->Limit()->Assoc();

		if($row['last'] >= (time() - $site['ddos_time']))
		{
			$row['last_warnings'] = ($row['last_warnings'] + 1);

			self::UpdateMy(array(
				'last_warnings'	=> $row['last_warnings'],
				'last_forgive'	=> '0'
			));
		}
		else
		{
			if($row['last_warnings'] > 0)
			{
				$forgive 				= round($site['ddos_warnings'] / 3);
				$row['last_forgive'] 	= ($row['last_forgive'] + 1);

				self::UpdateMy(array(
					'last_forgive'	=> $row['last_forgive']
				));

				if($row['last_forgive'] >= $forgive)
				{
					self::UpdateMy(array(
						'last_warnings'	=> '0',
						'last_forgive'	=> '0'
					));
				}
			}

			if($row['last_warnings'] >= $site['ddos_warnings'])
			{
				$blackip = Core::LoadJSON(ROOT . 'black_ip.json');

				if(!is_numeric(array_search(IP, $blackip)))
				{
					$blackip[] 		= IP;
					$new_blackip 	= json_encode($blackip);

					file_put_contents(ROOT . 'black_ip.json', $new_blackip);
				}

				if($site['ddos_htaccess'] == 'true')
				{
					$htaccess = file_get_contents(ROOT . '.htaccess');

					if(!Contains($htaccess, 'deny from ' . IP))
						file_put_contents(ROOT . '.htaccess', "deny from " . IP . PHP_EOL, FILE_APPEND | LOCK_EX);
				}

				if(!empty($site['ddos_redirect']))
					Core::Redirect($site['ddos_redirect']);
				
				header('HTTP/1.0 503 Service Temporarily Unavailable');
				header('Connection: close');
			}

			self::UpdateMy(array('last' => time()));
		}
	}
	
	// Checar cronometros.
	static function CheckTimers()
	{
		$q = Query('site_timers')->Select()->Run();
		
		while($row = fetch_assoc($q))
		{
			if($row['time'] == '0' OR $row['nexttime'] >= time())
				continue;
				
			self::Timer($row['action']);
			$next = Core::Time($row['time']);

			Query('site_timers')->Update(array('nexttime' => $next))->Add('id', $row['id'])->Run();
		}
	}
	
	// Ejecutar cronometro.
	// - $a: Cronometro.
	static function Timer($a)
	{
		if(empty($a))
			return;
			
		switch($a)
		{
			case 'optimize_db':
				MySQL::Optimize();
			break;
			
			case 'maintenance_db':
				Query('site_visits')->Truncate()->Run();
				Query('site_visits_total')->Truncate()->Run();
				Query('site_errors')->Truncate()->Run();
				Query('site_logs')->Truncate()->Run();
			break;
			
			case 'backup_db':
				MySQL::Backup();
			break;
			
			case 'backup_app':
				Bit::Backup();
			break;
			
			case 'backup_total':
				Bit::Backup(true);
			break;
			
			case 'maintenance':
				Io::EmptyDir(array(
					BIT . 'Logs', 
					BIT . 'Backups', 
					BIT . 'Temp', 
					BIT . 'Cache',
					BIT . 'Temp'
				));
			break;
		}
		
		Reg('%timer.correct%' . $a);
	}
	
	// Obtener datos.
	// - $a (countrys, maps, news): Tipo de datos a obtener.
	// - $limit (Int): Limite de valores a obtener.
	static function Get($a = 'countrys', $limit = 0)
	{
		$q = Query('site_' . $a)->Select();

		if($a == 'countrys')
			$q->Order('name', 'ASC');
		else
			$q->Order('id', 'DESC');		
			
		if($limit !== 0)
			$q->Limit($limit);
			
		return $q->Run();
	}
	
	// Obtener noticia.
	// - $id (Int): ID de la noticia.
	static function GetNew($id)
	{
		$q = Query('site_news')->Select()->Add('id', $id)->Limit()->Run();	
		return (num_rows() > 0) ? $q : false;
	}
	
	// Guardar logs actuales.
	static function SaveLog()
	{
		$logs = Bit::$logs['all']['text'];
		
		if(empty($logs))
			return;

		Insert('site_logs', array(
			'logs' 		=> _F($logs, false),
			'phpid' 	=> session_id(),
			'path' 		=> _F(PATH),
			'date' 		=> time()
		));
	}
	
	// Obtener Caché de página.
	// - $page: Página.
	static function GetCache($page)
	{
		$q = Query('site_cache')->Select(array('id','page','time'))->Add('page', $page)->Limit()->Run();
		return (num_rows() > 0) ? fetch_assoc($q) : false;
	}

	// Preparar la caché para MySQL.
	static function PrepareCache()
	{
		global $site, $config;

		if(!extension_loaded('mysqlnd_qc') OR empty($site['cache_tables']))
			return;

		$tables = explode(',', $site['cache_tables']);

		foreach($tables as $table)
		{
			$table = trim(DB_PREFIX . $table);
			mysqlnd_qc_set_cache_condition(MYSQLND_QC_CONDITION_META_SCHEMA_PATTERN, $config['mysql']['name'] . '.' . $table);
		}
	}
}
?>