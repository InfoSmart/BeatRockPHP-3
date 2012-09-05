<?php
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
##        Módulo Site
## --------------------------------------------------
## Este módulo contiene las funciones y herramientas
## necesarias para interactuar con la base de datos.
## Incluya sus funciones dentro de un nuevo módulo
## en la carpeta 'Site'.
## --------------------------------------------------

class Site
{
	// Obtener la configuración del sitio.
	static function GetConfig()
	{
		global $site;

		$sql 	= query('SELECT var,result FROM {DA}site_config');
		$site 	= array();
		
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

		free_result();
		return $site;
	}
	
	// Actualizar configuración del sitio.
	// - $var: Variable.
	// - $value: Nuevo valor.
	static function Update($var, $value)
	{
		global $site;
		
		Update('site_config', array(
			'result' => $value
		), array(
			'var = "'.$var.'"'
		));
		
		$site[$var] = $value;
	}
	
	// Agregar una nueva visita.
	static function AddVisit()
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
				'agent' 	=> _f(AGENT),
				'browser' 	=> $browser,
				'path' 		=> _f(PATH_NOW),
				'referer' 	=> _f(FROM),
				'phpid' 	=> session_id(),
				'type' 		=> $type,
				'date' 		=> time()
			));
		}

		if(_SESSION('visit') == 'true')
			return;
		
		$n = Rows("SELECT null FROM {DA}site_visits WHERE ip = '".IP."' OR host = '$host' OR phpid = '".session_id()."' LIMIT 1");
		
		if($n !== 0)
			return;

		Insert('site_visits', array(
			'ip' 		=> IP,
			'host' 		=> $host,
			'agent' 	=> _f(AGENT),
			'browser' 	=> $browser,
			'referer' 	=> _f(FROM),
			'phpid'		=> session_id(),
			'type' 		=> $type,
			'date' 		=> time()
		));
		
		_SESSION('visit', 'true');
		query("UPDATE {DA}site_config SET result = result + 1 WHERE var = 'site_visits' LIMIT 1");
	}
	
	// Checar cronometros.
	static function CheckTimers()
	{
		$q = query("SELECT * FROM {DA}site_timers");
		
		while($row = fetch_assoc($q))
		{
			if($row['time'] == '0' OR $row['nexttime'] >= time())
				continue;
				
			self::DoTimer($row['action']);
			$next = Core::Time($row['time']);
			
			Update('site_timers', array(
				'nexttime' => $next,
			), array(
				"id = '$row[id]'"
			));
		}
	}
	
	// Ejecutar cronometro.
	// - $a: Cronometro.
	static function DoTimer($a)
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
				query('TRUNCATE TABLE {DA}site_visits_total');
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
		$q = 'SELECT * FROM {DA}site_'.$a.' ORDER BY ';
		$q .= ($a == 'countrys') ? 'name ASC' : 'id DESC';
			
		if($limit !== 0)
			$q .= ' LIMIT ' . $limit;
			
		return query($q);
	}
	
	// Obtener noticia.
	// - $id (Int): ID de la noticia.
	static function GetNew($id)
	{
		$q = query("SELECT * FROM {DA}site_news WHERE id = '$id' LIMIT 1");		
		return (num_rows() > 0) ? $q : false;
	}
	
	// Guardar logs actuales.
	static function SaveLog()
	{
		$logs = BitRock::$logs['all']['text'];
		
		if(empty($logs))
			return;
			
		Insert('site_logs', array(
			'logs' 		=> _f($logs, false),
			'phpid' 	=> session_id(),
			'path' 		=> _f(PATH),
			'date' 		=> time()
		));
	}
	
	// Obtener Caché de página.
	// - $page: Página.
	static function GetCache($page)
	{
		$q = query("SELECT id,page,time FROM {DA}site_cache WHERE page = '$page' LIMIT 1");
		return (num_rows() > 0) ? fetch_assoc($q) : false;
	}
}
?>