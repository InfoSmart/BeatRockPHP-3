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

class Site
{
	// Función - Obtener la configuración del sitio.
	public static function GetConfig()
	{
		$sql = query('SELECT var,result FROM {DA}site_config');
		
		while($row = fetch_assoc())
		{
			$i = 0;
			
			foreach($row as $param => $value)
			{
				$i++;
				
				if($i == 1)
					$p = $value;
				else
					$v = $value;
			}
			
			$site[$p] = $v;
		}
		
		return $site;
	}
	
	// Función - Actualizar configuración del sitio.
	// - $var: Variable.
	// - $value: Nuevo valor.
	public static function Update($var, $value)
	{
		global $site;
		
		Update('site_config', Array(
			'result' => $value
		), Array(
			'var = "'.$var.'"'
		));
		
		$site[$var] = $value;
	}
	
	// Función - Agregar una nueva visita.
	public static function AddVisit()
	{
		if(Core::theSession('visit_me') == 'true')
			return;

		$h = Client::Get('host');
		$n = Rows("SELECT null FROM {DA}site_visits WHERE ip = '".IP."' OR host = '$h' OR phpid = '".session_id()."' LIMIT 1");
		
		if($n !== 0)
			return;
			
		$type = 'desktop';
			
		if(Core::IsMobile())
			$type = 'mobile';
			
		if(Core::IsBOT())
			$type = 'bot';
		
		Insert('site_visits', Array(
			'ip' => IP,
			'host' => $h,
			'agent' => _f(AGENT),
			'browser' => Client::Get('browser'),
			'referer' => _f(FROM),
			'phpid' => session_id(),
			'type' => $type,
			'date' => time()
		));
		
		Core::theSession('visit_me', 'true');
		query("UPDATE {DA}site_config SET result = result + 1 WHERE var = 'site_visits' LIMIT 1");
	}
	
	// Función - Checar cronometros.
	public static function CheckTimers()
	{
		$q = query("SELECT id,action,time,nexttime FROM {DA}site_timers");
		
		while($row = fetch_assoc())
		{
			if($row['time'] == '0')
				continue;

			if($row['nexttime'] >= time())
				continue;
				
			self::DoTimer($row['action']);
			$next = Core::Time($row['time']);
			
			Update('site_timers', Array(
				'nexttime' => $next,
			), Array(
				"id = '$row[id]'"
			));
		}
	}
	
	// Función - Ejecutar cronometro.
	// - $a: Cronometro.
	public static function DoTimer($a)
	{
		if(empty($a))
			return;
			
		switch($a)
		{
			case 'optimize_db':
				MySQL::Optimize();
			break;
			
			case 'maintenance_db':
				query('TRUNCATE TABLE {DA}site_visits');
				query('TRUNCATE TABLE {DA}site_errors');
				query('TRUNCATE TABLE {DA}site_logs');
			break;
			
			case 'backup_db':
				MySQL::Backup();
			break;
			
			case 'backup_app':
				BitRock::Backup();
			break;
			
			case 'backup_total':
				BitRock::Backup(true);
			break;
			
			case 'maintenance':
				Io::EmptyDir(Array(BIT . 'Logs', BIT . 'Backups', BIT . 'Temp', BIT . 'Cache'));
			break;
		}
		
		BitRock::log('Se ha ejecutado el cronometro "'.$a.'" con éxito.');
	}
	
	// Función - Obtener datos.
	// - $a (countrys, maps, news): Tipo de datos a obtener.
	// - $limit (Int): Limite de valores a obtener.
	public static function Get($a = 'countrys', $limit = 0)
	{
		if($a !== 'countrys' AND $a !== 'maps' AND $a !== 'news')
			return false;	
			
		$q = 'SELECT * FROM {DA}site_$a ORDER BY ';
		$q .= $a == 'countrys' ? 'name ASC' : 'id DESC';
			
		if($limit !== 0)
			$q .= ' LIMIT ' . $limit;
			
		return query($q);
	}
	
	// Función - Obtener noticia.
	// - $id (Int): ID de la noticia.
	public static function GetNew($id)
	{
		$q = query("SELECT * FROM {DA}site_news WHERE id = '$id' LIMIT 1");		
		return num_rows() > 0 ? $q : false;
	}
	
	// Función - Guardar logs actuales.
	public static function SaveLog()
	{
		$logs = BitRock::$logs['all']['text'];
		
		if(empty($logs))
			return;
			
		Insert('site_logs', Array(
			'logs' => _f($logs, false),
			'phpid' => session_id(),
			'path' => _f(PATH),
			'date' => time()
		));
	}
	
	// Función - Obtener traducciones.
	// - $lang: Código de lenguaje.
	public static function GetTranslations($lang = '')
	{		
		if(empty($lang))
			$lang = LANG;
			
		$q = query("SELECT var,original,translated,language FROM {DA}site_translate WHERE language = '$lang'");
		return num_rows() > 0 ? $q : false;
	}
	
	// Función - Obtener traducciones.
	// - $lang: Código de lenguaje.
	public static function GetTranslation($lang = '')
	{		
		if(empty($lang))
			$lang = LANG;
			
		$q = query("SELECT id,var,original,translated,language FROM {DA}site_translate WHERE language = '$lang'");
		
		if(num_rows() > 0)
		{
			while($row = fetch_assoc($q))
				$result[$row['var']] = $row['translated'];
		}
		
		return $result;
	}	
	
	// Función - Obtener Caché de página.
	// - $page: Página.
	public static function GetCache($page)
	{
		$q = query("SELECT id,page,time FROM {DA}site_cache WHERE page = '$page' LIMIT 1");
		return num_rows() > 0 ? fetch_assoc($q) : false;
	}
	
	// Función - Obtener página de la petición.
	// - $request: Página solicitada.
	public static function GetPage($request)
	{
		$srequest = explode('?', $request);
		$page = $srequest[0];

		if(empty($page))
			$page = 'index';
		
		$q = query("SELECT * FROM {DA}site_pages WHERE request = '$page' LIMIT 1");
		
		if(num_rows() > 0)
		{
			$result = fetch_assoc($q);
			
			if(!empty($srequest[1]))
			{
				$params = explode('&', $srequest[1]);
				
				foreach($params as $p)
				{
					$val = explode('=', $p);
					$result['params'][$val[0]] = $val[1];
				}
			}
		}
		else
			return false;
		
		return $result;
	}	
	
	/*####################################################
	##	FUNCIONES PERSONALIZADAS						##
	####################################################*/
}
?>