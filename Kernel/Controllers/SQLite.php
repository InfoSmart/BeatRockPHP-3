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
##        Módulo SQLite
## --------------------------------------------------
## Este módulo contiene las funciones y herramientas
## necesarias para interactuar con el servidor MySQL.
## --------------------------------------------------

class SQL
{
	static $server 			= null;
	static $connected 		= false;

	static $querys 			= 0;
	static $last_query 		= '';
	static $last_resource 	= null;

	static $free_result 	= false;

	// Lanzar error.
	// - $code: Código del error.
	// - $function: Función causante.
	// - $message: Mensaje del error.
	static function Error($code, $function, $message = '')
	{	
		if(empty($message))
			$message = self::$server->lastErrorMsg();
		else
			$message .= ' - ('. self::$server->lastErrorMsg() .')';

		Lang::SetSection('mod.sqlite');
		
		Bit::Status($message, __FILE__, array('function' => $function, 'query' => self::$last_query));
		Bit::LaunchError($code);
		
		return false;
	}

	// ¿Hay alguna conexión activa?
	static function Ready()
	{
		Lang::SetSection('mod.sqlite');

		if(!self::$connected)
			return false;
			
		return true;
	}

	// ¿Ya se ha hecho una consulta?
	static function ReadyQuery()
	{
		return !empty(self::$last_query) ? true : false;
	}

	// Destruir conexión activa.
	static function Destroy()
	{
		if(!self::Ready())
			return;
		
		self::$server->close();
		Reg('%connection.out%');

		self::$server 		= null;
		self::$connected 	= false;
		self::$last_query 	= '';
	}

	// Conexión al servidor SQLite.
	// - $dbname: Ruta a la base de datos.
	static function Init($dbname = '')
	{
		global $config;
		$sql = $config['sql'];

		Lang::SetSection('mod.sqlite');
		self::Destroy();
		
		if(empty($dbname))
			$dbname = $sql['name'];	

		if(empty($dbname))
			return false;

		$conn = new SQLite3($dbname) or self::Error('sqlite.connect', __FUNCTION__);

		self::$server 		= $conn;
		self::$connected 	= true;

		if($sql['repair'])
			self::Repair();

		//$test = $conn->query("SELECT null FROM $sql[prefix]site_config");
		
		//if(!$test)
		//	self::Recover($dbname, 2);

		Reg('%connection.correct%', 'sql');
		return $conn;
	}

	// Ejecutar consulta en el servidor SQLite.
	// - $query: Consulta a ejecutar.
	static function query($query)
	{
		if(!self::Ready())
			return self::Error('sqlite.need.connection', __FUNCTION__);

		if(is_array($query))
		{
			foreach($query as $q)
				$result[] = self::query($q);

			return $result;
		}
		
		$query 				= str_ireplace('{DA}', DB_PREFIX, $query);		
		self::$last_query 	= $query;
		
		$result = self::$server->query($query) or self::Error('sqlite.query', __FUNCTION__);		
		++self::$querys;

		self::$last_resource = $result;

		gc_collect_cycles();
		
		Reg('%query.correct%', 'sql');
		return $result;
	}

	// Obtener numero de valores de una consulta SQLite.
	// - $q: Consulta a ejecutar.
	static function query_rows($q)
	{
		if(!self::Ready())
			return self::Error('sqlite.need.connection', __FUNCTION__);

		if(is_array($q))
		{
			foreach($q as $query)
				$result[] = self::query_rows($query);

			return $result;
		}

		if(!Contains($q, 'SELECT', true))
			return self::Error('sqlite.query.novalid', __FUNCTION__);
		
		$sql 	= self::query($q);
		$result = $sql->numColumns();

		return $result;
	}

	// Obtener los valores de una consulta SQLite.
	// - $q: Consulta a ejecutar.
	static function query_assoc($q)
	{
		if(!self::Ready())
			return self::Error('sqlite.need.connection', __FUNCTION__);

		if(is_array($q))
		{
			foreach($q as $query)
				$result[] = self::query_assoc($query);

			return $result;
		}

		if(!Contains($q, 'SELECT', true))
			return self::Error('sqlite.query.novalid', __FUNCTION__);
		
		$sql 	= self::query($q);
		$result = (self::num_rows($sql) > 0) ? $sql->fetchArray(SQLITE3_ASSOC) : false;

		return $result;
	}

	// Obtener un dato especifico de una consulta SQLite.
	// - $q: Consulta a ejecutar.
	// - $row: Dato a obtener.
	static function query_get($q)
	{
		if(!self::Ready())
			return self::Error('sqlite.need.connection', __FUNCTION__);

		if(is_array($q))
		{
			foreach($q as $query)
				$result[] = self::query_get($query);

			return $result;
		}
		
		$query 				= str_ireplace('{DA}', DB_PREFIX, $query);		
		self::$last_query 	= $query;
		
		$result = self::$server->querySingle($query) or self::Error('sqlite.query', __FUNCTION__);		
		++self::$querys;

		gc_collect_cycles();
		
		Reg('%query.correct%', 'sql');
		return $result;
	}

	// Insertar datos en la base de datos.
	// - $table: Tabla a insertar los datos.
	// - $data (Array): Datos a insertar.
	static function query_insert($table, $data)
	{
		if(!self::Ready())
			return self::Error('sqlite.need.connection', __FUNCTION__);
		
		if(!is_array($data))
			return false;
			
		$values = array_values($data);
		$keys 	= array_keys($data);
		
		return self::query("INSERT INTO {DA}$table (" . implode(',', $keys) . ") VALUES ('" . implode('\',\'', $values) . "')");
	}

	// Actualizar datos en la base de datos.
	// - $table: Tabla a insertar los datos.
	// - $updates (Array): Datos a actualizar.
	// - $where (Array): Condiciones a cumplir.
	// - $limt (Int): Limite de columnas a actualizar.
	static function query_update($table, $updates, $where = '', $limit = 1)
	{
		if(!self::Ready())
			return self::Error('sqlite.need.connection', __FUNCTION__);
		
		if(!is_array($updates))
			return false;
		
		$query = "UPDATE {DA}$table SET ";
		$i = 0;
		
		foreach($updates as $key => $value)
		{
			$i++;			
			$query .= "$key = '$value'";
			
			if(count($updates) !== $i)
				$query .= ",";
		}
		
		if(!empty($where))
		{
			$query .= " WHERE ";
			
			foreach($where as $key)
				$query .= "  $key";
		}
		
		if($limit !== 0)
			$query .= " LIMIT $limit";
		
		return self::query($query);
	}

	// Obtener toda la información de una consulta.
	// - $q: Consulta a ejecutar.
	static function query_data($q)
	{
		if(!self::Ready())
			return self::Error('sqlite.need.connection', __FUNCTION__);

		$sql = self::query($q);

		if($sql == false)
			return false;

		$result = array(
			'resource' 	=> $sql,
			'assoc' 	=> $sql->fetchArray(SQLITE3_ASSOC),
			'rows' 		=> $sql->numColumns()
		);

		return $result;
	}

	// Obtener las filas que han sido afectadas en la última consulta.
	static function affected_rows()
	{
		if(!self::Ready())
			return self::Error('sqlite.need.connection', __FUNCTION__);

		return self::$server->changes();
	}

	// Obtener la última ID insertada en la base de datos.
	static function last_id()
	{
		if(!self::Ready())
			return self::Error('sqlite.need.connection', __FUNCTION__);

		return self::$server->lastInsertRowID();
	}

	// Filtrar una cadena para su uso en las consultas.
	// - $str: Cadena.
	static function escape_string($str)
	{
		if(!self::Ready())
			return self::Error('sqlite.need.connection', __FUNCTION__);

		return self::$server->escapeString($str);
	}

	// Obtener numero de valores de un recurso SQLite o la última consulta hecha.
	// - $q: Recurso de una consulta.
	static function num_rows($q = '')
	{
		if(empty($q) AND !self::ReadyQuery())
			return self::Error('sqlite.query.need', __FUNCTION__);			
		
		if(empty($q))
			$q = self::$last_resource;
			
		return $q->numColumns();
	}

	// Obtener los valores de un recurso SQLite o la última consulta hecha.
	// - $q: Recurso de la consulta.
	static function fetch_assoc($q = '')
	{
		return self::fetch_array($q);
	}

	// Obtener los valores de un recurso SQLite o la última consulta hecha.
	// - $q: Recurso de la consulta.
	static function fetch_object($q = '')
	{
		$result = self::fetch_assoc($q);
		$final 	= new stdClass();

		foreach($result as $key => $value)
			$final->{$key} = $value;

		return $final;
	}

	// Obtener los valores de un recurso SQLite o la última consulta hecha.
	// - $q: Recurso de la consulta.
	static function fetch_array($q = '')
	{
		if(empty($q) AND !self::ReadyQuery())
			return self::Error('sqlite.query.need', __FUNCTION__);
		
		if(empty($q))
			$q = self::$last_resource;

		return $q->fetchArray(SQLITE3_ASSOC);
	}

	// Obtener los valores de un recurso SQLite o la última consulta hecha.
	// - $q: Recurso de la consulta.
	static function fetch_row($q = '')
	{
		return self::fetch_array($q);
	}

	// Liberar la memoria de la última consulta realizada.
	// - $q: Recurso de la consulta.
	static function free_result($q = '')
	{
		return false;
	}

	// Obtener un dato especifico de un recurso SQLite o la última consulta hecha.
	// - $row: Dato a obtener.
	// - $q: Recurso de la consulta.
	static function get($row, $q = '')
	{
		if(empty($q) AND !self::ReadyQuery())
			return self::Error('sqlite.query.need', __FUNCTION__);
		
		if(empty($q))
			$q = self::$last_resource;
			
		$r = self::fetch_assoc($q);
		return $r[$row];
	}

	// Cambiar el motor de las tablas.
	// - $engine (MyISAM, INNODB): Motor a cambiar.
	// - $tables (Array): Tablas a cambiar.
	static function Engine($engine = 'MYISAM', $tables = '')
	{		
		return false;
	}

	// Optimizar las tablas.
	// - $tables (Array): Tablas a optimizar.
	static function Optimize($tables = '')
	{
		return false;
	}
	
	// Reparar las tablas.
	// - $tables (Array): Tablas a reparar.
	static function Repair($tables = '')
	{
		return false;
	}

	// Examinar la base de datos.
	static function Examine()
	{
		return false;
	}

	// Hacer un backup de la base de datos.
	// - $tables (array): Tablas a recuperar.
	// - $out (Bool): Retornar la copia en texto plano, de otra manera retornar el nombre del archivo.
	static function Backup($tables = '', $out = false)
	{
		global $site;

		if(empty($tables))
		{
			$query = self::query('SELECT * FROM sqlite_master WHERE type = "table" OR type = "index" OR type = "view" OR type = "trigger" ORDER BY type = "trigger", type = "index", type = "view", type = "table"');
			
			while($row = $query->fetchArray(SQLITE3_ASSOC))
				$tables[] = $row[0];
		}
		else
			$tables = (is_array($tables)) ? $tables : explode(',', $tables);

		return false;

		_r($tables);
		exit;
			
		foreach($tables as $table)
		{
			$result 	= self::query("SELECT * FROM $table");
			$num_fields = mysqli_num_fields($result);
    
			$return 	.= "DROP TABLE IF EXISTS $table;";
			$row2 		= mysqli_fetch_row(self::query("SHOW CREATE TABLE $table"));
			$return		.= "\n\n". $row2[1] . ";\n\n";
    
			for ($i = 0; $i < $num_fields; $i++) 
			{
				while($row = mysqli_fetch_row($result))
				{
					$return.= "INSERT INTO $table VALUES(";
				
					for($j=0; $j<$num_fields; $j++) 
					{
						$row[$j] = addslashes($row[$j]);
						$row[$j] = str_replace("\n","\\n",$row[$j]);
					
						if(isset($row[$j]))
							$return.= '"' . $row[$j] . '"' ;
						else
							$return.= '""';
						
						if($j<($num_fields-1))
							$return.= ',';
					}
				
					$return.= ");\n";
				}
			}
		
			$return.="\n\n\n";
		}
		
		if(empty($return))
			return false;
			
		$return = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $return);
		Reg('%backup.create%');
			
		if(!$out)
		{
			$bname = 'DB-Backup-' . date('d_m_Y') . '-' . time() . '.sql';
			Io::SaveBackup($bname, $return);

			if($site['site_backups_servers'] == 'true')
				Bit::Send_FTPBackup(BIT . 'Backups' . DS . $bname, $bname);
			
			return $bname;
		}
		
		return $return;
	}
}