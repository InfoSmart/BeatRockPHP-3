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
if(!defined("BEATROCK"))
	exit;	

class Site
{
	// Función - Obtener la configuración del sitio.
	public static function getConfiguration()
	{
		// Ejecutando consulta para la configuración.
		$sql = query("SELECT * FROM {DA}site_config");
		
		while($row = MySQL::fetch_assoc())
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
	public static function updateConf($var, $value)
	{
		global $site;
		
		query_update('site_config', Array(
			'result' => $value
		), Array(
			"var = '$var'"
		));
		
		$site[$var] = $value;
	}
	
	// Función - Agregar una nueva visita.
	public static function addVisit()
	{
		// Obteniendo el host del usuario y ejecutando consulta de verificación.
		$h = Client::Get("host");
		$n = query_rows("SELECT null FROM {DA}site_visits WHERE ip = '" . IP . "' OR host = '" . $h . "' OR phpid = '" . session_id() . "' LIMIT 1");
		
		// Este usuario ya nos ha visitado.
		if($n !== 0 OR Core::theSession("visit_me") == "true")
			return;
			
		// Navegador web: Normal/Escritorio.
		$type = "desktop";
			
		// Navegador web: Móvil.
		if(Core::IsMobile())
			$type = "mobile";
			
		// Navegador web: BOT/Robot.
		if(Core::IsBOT())
			$type = "bot";
		
		// Insertar en las visitas.
		query_insert('site_visits', Array(
			'ip' => IP,
			'host' => $h,
			'agent' => FilterText(AGENT),
			'browser' => Client::Get("browser"),
			'referer' => FilterText(FROM),
			'phpid' => session_id(),
			'type' => $type,
			'date' => time()
		));
		
		// Ya nos ha visitado y agregar una visita a la tabla de configuración del sitio.
		Core::theSession("visit_me", "true");
		query("UPDATE {DA}site_config SET result = result + 1 WHERE var = 'site_visits' LIMIT 1");
	}
	
	// Función - Checar cronometros.
	public static function checkTimers()
	{
		// Ejecutar consulta para obtener los cronometros.
		$q = query("SELECT * FROM {DA}site_timers");
		
		while($row = MySQL::fetch_assoc())
		{
			// Algo aquí anda mal, continuar con el próximo...
			if($row['time'] == "0")
				continue;
			// Aún no le toca ejecutarse, continuar con el próximo...
			if($row['nexttime'] >= time())
				continue;
				
			// Ejecutar cronometro.
			self::doTimer($row['action']);
			// Definiendo próxima ejecución.
			$next = Core::Time($row['time']);
			
			// Actualizar cronometro.
			query_update('site_timers', Array(
				'nexttime' => $next,
			), Array(
				"id = '$row[id]'"
			));
		}
	}
	
	// Función - Ejecutar cronometro.
	// - $a: Cronometro.
	public static function doTimer($a)
	{
		if(empty($a))
			return;
			
		switch($a)
		{
			case "optimize_db":
				MySQL::Optimize();
			break;
			
			case "maintenance_db":
				query("TRUNCATE TABLE {DA}site_visits");
				query("TRUNCATE TABLE {DA}site_errors");
				query("TRUNCATE TABLE {DA}site_logs");
			break;
			
			case "backup_db":
				MySQL::Backup();
			break;
			
			case "backup_app":
				BitRock::Backup();
			break;
			
			case "backup_total":
				BitRock::Backup(true);
			break;
			
			case "maintenance":
				Io::EmptyDir(Array(BIT . 'Logs', BIT . 'Backups', BIT . 'Temp', BIT . 'Cache'));
			break;
		}
		
		BitRock::log("Se ha ejecutado el cronometro '$a' con éxito.");
	}
	
	// Función - Obtener datos.
	// - $a (countrys, maps, news): Tipo de datos a obtener.
	// - $limit (Int): Limite de valores a obtener.
	public static function Get($a = 'countrys', $limit = 0)
	{
		// Tipo inválido.
		if($a !== 'countrys' AND $a !== 'maps' AND $a !== 'news')
			return false;	
			
		// Estructurar y ejecutar consulta.
		$q = "SELECT * FROM {DA}site_$a ORDER BY ";
		
		if($a == 'countrys')
			$q .= 'name ASC';
		else
			$q .= 'id DESC';
			
		if($limit !== 0)
			$q .= " LIMIT $limit";
			
		return query($q);
	}
	
	// Función - Obtener noticia.
	// - $id (Int): ID de la noticia.
	public static function getNew($id)
	{
		$q = query("SELECT * FROM {DA}site_news WHERE id = '$id' LIMIT 1");		
		return MySQL::num_rows() > 0 ? $q : false;
	}
	
	// Función - Guardar logs actuales.
	public static function saveLog()
	{
		// Obteniendo los logs.
		$logs = BitRock::$logs['all']['text'];
		
		// No hay logs que guardar.
		if(empty($logs))
			return;
			
		query_insert('site_logs', Array(
			'logs' => FilterText($logs, false),
			'phpid' => session_id(),
			'path' => FilterText(PATH),
			'date' => time()
		));
	}
	
	// Función - Obtener traducciones.
	// - $lang: Código de lenguaje.
	public static function getTranslations($lang = "")
	{		
		if(empty($lang))
			$lang = LANG;
			
		$q = query("SELECT * FROM {DA}site_translate WHERE language = '$lang'");
		return MySQL::num_rows() > 0 ? $q : false;
	}
	
	// Función - Obtener traducciones.
	// - $lang: Código de lenguaje.
	public static function getTranslation($lang = "")
	{		
		if(empty($lang))
			$lang = LANG;
			
		$q = query("SELECT * FROM {DA}site_translate WHERE language = '$lang'");
		$result = Array();
		
		if(MySQL::num_rows() > 0)
		{
			while($row = mysql_fetch_assoc($q))
				$result[$row['var']] = $row['translated'];
		}
		
		return $result;
	}
	
	
	public static function getCache($page)
	{
		$q = query("SELECT * FROM {DA}site_cache WHERE page = '$page' LIMIT 1");
		return MySQL::num_rows() > 0 ? mysql_fetch_assoc($q) : false;
	}
	
	// Función - Obtener página de la petición.
	// - $request: Página solicitada.
	public static function getPage($request)
	{
		// Separando de parametros externos.
		$srequest = explode("?", $request);
		// Página a solicitar.
		$page = $srequest[0];

		if(empty($page))
			$page = "index";
		
		// Ejecutar consulta.
		$q = query("SELECT * FROM {DA}site_pages WHERE request = '$page' LIMIT 1");
		
		// Página encontrada.
		if(MySQL::num_rows() > 0)
		{
			// Obteniendo los datos de la página.
			$result = mysql_fetch_assoc($q);
			
			// Al parecer hay parametros externos.
			if(!empty($srequest[1]))
			{
				$params = explode("&", $srequest[1]);
				
				foreach($params as $p)
				{
					$val = explode("=", $p);
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