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

class MySQL
{
	static $connection 		= null;
	static $connected 		= false;

	static $querys 			= 0;
	static $last_query 		= '';
	static $last_resource 	= null;

	static $cache 			= array();
	static $free_result 	= false;
	
	// Lanzar error.
	// - $code: Código del error.
	// - $function: Función causante.
	// - $message: Mensaje del error.
	static function Error($code, $function, $message = '')
	{		
		if(empty($message))
			$message = mysql_error();

		Lang::SetSection('mod.mysql');
		
		BitRock::SetStatus($message, __FILE__, array('function' => $function, 'query' => self::$last_query));
		BitRock::LaunchError($code);
		
		return false;
	}
	
	// ¿Hay alguna conexión activa?
	static function Ready()
	{
		Lang::SetSection('mod.mysql');

		if(self::$connection == null OR !self::$connected)
			return false;
			
		return true;
	}
	
	// ¿Ya se ha hecho una consulta?
	static function ReadyQuery()
	{
		return !empty(self::$last_query) ? true : false;
	}
	
	// Destruir conexión activa.
	static function Crash()
	{
		if(!self::Ready())
			return;
		
		mysql_close();
		Reg('%connection.out%');

		self::$connection 	= null;
		self::$connected 	= false;
		self::$last_query 	= '';
	}
	
	// Conexión al servidor MySQL.
	// - $host: Host de conexión.
	// - $username: Nombre de usuario.
	// - $password: Contraseña.
	// - $dbname: Nombre de la base de datos.
	// - $port: Puerto del servidor. (Predeterminado: 3306)
	static function Connect($host = '', $username = '', $password = '', $dbname = '', $port = 3306)
	{
		global $config;
		$mysql = $config['mysql'];
		
		Lang::SetSection('mod.mysql');
		self::Crash();
		
		if(empty($host) OR empty($username))
		{			
			$host 		= $mysql['host'];
			$username 	= $mysql['user'];
			$password 	= $mysql['pass'];
			$dbname 	= $mysql['name'];	
			$port		= $mysql['port'];		
		}

		if(empty($host) OR empty($dbname))
			return false;
			
		$sql = mysql_connect("$host:$port", $username, $password) or self::Error('mysql.connect', __FUNCTION__);

		self::$connection 	= $sql;
		self::$connected 	= true;	

		self::select_db($dbname);
		$test 	= mysql_query("SELECT null FROM $mysql[prefix]site_config");
		
		if(!$test)
			self::Recover($dbname, 2);

		Reg('%connection.correct%', 'mysql');			
			
		if($config['mysql']['repair'])
			self::Repair();
	}

	// Cambiar de base de datos en ejecución.
	// - $dbname: Nombre de la base de datos.
	static function select_db($dbname)
	{
		if(!self::Ready())
			return self::Error('mysql.need.connection', __FUNCTION__);

		mysql_select_db(self::$connection, $dbname) or self::Recover($dbname);
	}
	
	// Ejecutar consulta en el servidor MySQL.
	// - $q: Consulta a ejecutar.
	static function query($q, $free = false)
	{
		if(!self::Ready())
			return self::Error('mysql.need.connection', __FUNCTION__);

		if(is_array($q))
		{
			foreach($q as $query)
				$result[] = self::query($query);

			return $result;
		}
		
		$q 					= str_ireplace('{DA}', DB_PREFIX, $q);		
		self::$last_query 	= $q;
		
		$sql = mysql_query($q) or self::Error('mysql.query', __FUNCTION__);
		
		++self::$querys;

		if($free OR self::$free_result)
		{
			self::free_result($sql);

			self::$last_resource 	= null;
			self::$free_result 		= false;
		}
		else
			self::$last_resource = $sql;

		gc_collect_cycles();
		
		Reg('%query.correct%', 'mysql');
		return $sql;
	}
	
	// Obtener numero de valores de una consulta MySQL.
	// - $q: Consulta a ejecutar.
	static function query_rows($q)
	{
		if(!self::Ready())
			return self::Error('mysql.need.connection', __FUNCTION__);

		if(is_array($q))
		{
			foreach($q as $query)
				$result[] = self::query_rows($query);

			return $result;
		}

		if(!Contains($q, 'SELECT', true))
			return self::Error('mysql.query.novalid', __FUNCTION__);
		
		$sql 	= self::query($q);
		$result = mysql_num_rows($sql);

		if(self::$free_result)
		{
			self::free_result();
			self::$free_result = false;
		}

		return $result;
	}
	
	// Obtener los valores de una consulta MySQL.
	// - $q: Consulta a ejecutar.
	static function query_assoc($q)
	{
		if(!self::Ready())
			return self::Error('mysql.need.connection', __FUNCTION__);

		if(is_array($q))
		{
			foreach($q as $query)
				$result[] = self::query_assoc($query);

			return $result;
		}

		if(!Contains($q, 'SELECT', true))
			return self::Error('mysql.query.novalid', __FUNCTION__);
		
		$sql 	= self::query($q);
		$result = self::num_rows($sql) > 0 ? mysql_fetch_assoc($sql) : false;

		if(self::$free_result)
		{
			self::free_result();
			self::$free_result = false;
		}
		
		return $result;
	}
	
	// Obtener un dato especifico de una consulta MySQL.
	// - $q: Consulta a ejecutar.
	// - $row: Dato a obtener.
	static function query_get($q)
	{
		if(!self::Ready())
			return self::Error('mysql.need.connection', __FUNCTION__);

		if(is_array($q))
		{
			foreach($q as $query)
				$result[] = self::query_get($query);

			return $result;
		}

		preg_match("/SELECT ([^<]+) FROM/is", $q, $params);

		if(!Contains($q, 'SELECT', true) OR empty($params[1]) OR $params[1] == '*' OR $params[1] == 'null')
			return self::Error("mysql.query.novalid", __FUNCTION__);

		$pp 	= explode(',', $params[1]);			
		$row 	= self::query_assoc($q);	

		if($row == false)
			$result = false;
		else
		{
			if(count($pp) > 1)
			{
				foreach($pp as $param)
					$result[] = $row[$param];
			}
			else
				$result = $row[$params[1]];
		}

		if(self::$free_result)
		{
			self::free_result();
			self::$free_result = false;
		}

		return $result;
	}
	
	// Insertar datos en la base de datos.
	// - $table: Tabla a insertar los datos.
	// - $data (Array): Datos a insertar.
	static function query_insert($table, $data)
	{
		if(!self::Ready())
			return self::Error('mysql.need.connection', __FUNCTION__);
		
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
			return self::Error('mysql.need.connection', __FUNCTION__);
		
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
			return self::Error('mysql.need.connection', __FUNCTION__);

		$sql = self::query($q);

		if($sql == false)
			return false;

		$result = array(
			'resource' 	=> $sql,
			'assoc' 	=> mysql_fetch_assoc($sql),
			'rows' 		=> mysql_num_rows($sql)
		);

		if(self::$free_result)
		{
			self::free_result();
			self::$free_result = false;
		}

		return $result;
	}

	// Obtener las filas que han sido afectadas en la última consulta.
	static function affected_rows()
	{
		if(!self::Ready())
			return self::Error('mysql.need.connection', __FUNCTION__);

		return mysql_affected_rows();
	}

	// Obtener la última ID insertada en la base de datos.
	static function last_id()
	{
		if(!self::Ready())
			return self::Error('mysql.need.connection', __FUNCTION__);

		return mysql_insert_id();
	}

	// Filtrar una cadena para su uso en las consultas.
	// - $str: Cadena.
	static function escape_string($str)
	{
		if(!self::Ready())
			return self::Error('mysql.need.connection', __FUNCTION__);

		return mysql_escape_string($str);
	}
	
	// Obtener numero de valores de un recurso MySQL o la última consulta hecha.
	// - $q: Recurso de una consulta.
	static function num_rows($q = '')
	{
		if(empty($q) AND !self::ReadyQuery())
			return self::Error('mysql.query.need', __FUNCTION__);			
		
		if(empty($q))
			$q = self::$last_resource;
			
		return mysql_num_rows($q);
	}
	
	// Obtener los valores de un recurso MySQL o la última consulta hecha.
	// - $q: Recurso de la consulta.
	static function fetch_assoc($q = '')
	{
		if(empty($q) AND !self::ReadyQuery())
			return self::Error('mysql.query.need', __FUNCTION__);
		
		if(empty($q))
			$q = self::$last_resource;
			
		$result = mysql_fetch_assoc($q);

		if(self::$free_result)
		{
			self::free_result();
			self::$free_result = false;
		}

		return $result;
	}

	// Obtener los valores de un recurso MySQL o la última consulta hecha.
	// - $q: Recurso de la consulta.
	static function fetch_object($q = '')
	{
		if(empty($q) AND !self::ReadyQuery())
			return self::Error('mysql.query.need', __FUNCTION__);
		
		if(empty($q))
			$q = self::$last_resource;
			
		$result = mysql_fetch_object($q);

		if(self::$free_result)
		{
			self::free_result();
			self::$free_result = false;
		}

		return $result;
	}

	// Obtener los valores de un recurso MySQL o la última consulta hecha.
	// - $q: Recurso de la consulta.
	static function fetch_array($q = '')
	{
		if(empty($q) AND !self::ReadyQuery())
			return self::Error('mysql.query.need', __FUNCTION__);
		
		if(empty($q))
			$q = self::$last_resource;
			
		$result = mysql_fetch_array($q);

		if(self::$free_result)
		{
			self::free_result();
			self::$free_result = false;
		}

		return $result;
	}

	// Obtener los valores de un recurso MySQL o la última consulta hecha.
	// - $q: Recurso de la consulta.
	static function fetch_row($q = '')
	{
		if(empty($q) AND !self::ReadyQuery())
			return self::Error('mysql.query.need', __FUNCTION__);
		
		if(empty($q))
			$q = self::$last_resource;
			
		$result = mysql_fetch_row($q);

		if(self::$free_result)
		{
			self::free_result();
			self::$free_result = false;
		}

		return $result;
	}

	// Liberar la memoria de la última consulta realizada.
	// - $q: Recurso de la consulta.
	static function free_result($q = '')
	{
		if(empty($q) AND !self::ReadyQuery())
			return self::Error('mysql.query.need', __FUNCTION__);
		
		if(empty($q))
			$q = self::$last_resource;

		return mysql_free_result($q);
	}
	
	// Obtener un dato especifico de un recurso MySQL o la última consulta hecha.
	// - $row: Dato a obtener.
	// - $q: Recurso de la consulta.
	static function get($row, $q = '')
	{
		if(empty($q) AND !self::ReadyQuery())
			return self::Error('mysql.query.need', __FUNCTION__);
		
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
		if($engine !== 'MYISAM' AND $engine !== 'INNODB')
			return self::Error('mysql.engine', __FUNCTION__);
			
		if(empty($tables))
		{
			$query = self::query('SHOW TABLES');
			
			while($tmp = self::fetch_array($query))
				self::query("ALTER TABLE $tmp[0] ENGINE = $engine");
		}
		else if(is_array($tables))
		{
			foreach($tables as $t)
				self::query("ALTER TABLE $t ENGINE = $engine");
		}
		
		Reg("%engine_correct% $engine");
	}
	
	// Optimizar las tablas.
	// - $tables (Array): Tablas a optimizar.
	static function Optimize($tables = '')
	{
		if(empty($tables))
		{
			$query = self::query('SHOW TABLES');
			
			while($tmp = self::fetch_array($query))
				self::query("OPTIMIZE TABLE $tmp[0]");
		}
		else if(is_array($tables))
		{
			foreach($tables as $t)
				self::query("OPTIMIZE TABLE $t");
		}
		
		Reg('%optimize_correct%');
	}
	
	// Reparar las tablas.
	// - $tables (Array): Tablas a reparar.
	static function Repair($tables = '')
	{
		if(empty($tables))
		{
			$query = self::query('SHOW TABLES');
			
			while($tmp = self::fetch_array($query))
				self::query("REPAIR TABLE $tmp[0]");
		}
		else if(is_array($tables))
		{
			foreach($tables as $t)
				self::query("REPAIR TABLE $t");
		}
		
		Reg('%repair.correct%');
	}

	// Examinar la base de datos.
	static function Examine()
	{
		$result = array();
		$query 	= self::query('SHOW TABLES');
		
		while($row = self::fetch_row($query))
		{
			$fix = str_replace('_', ' ', $row[0]);

			$r 	= query("SHOW COLUMNS FROM $row[0]");
			$rc = array();
			
			if(self::num_rows($r) > 0)
			{
				while($roww = self::fetch_assoc($r))
					$rc[] = $roww['Field'];
			}

			$row[0] = str_replace(DA, "", $row[0]);

			$tables[] = array(
				'name' 			=> $row[0], 
				'name_fix' 		=> $fix, 
				'translated' 	=> Core::Translate($fix),
				'fields' 		=> $rc
			);
		}

		$result = array(
			'tables' 	=> $tables,
			'count' 	=> count($tables)
		);

		return $result;
	}
	
	// Recuperar/Restaurar la base de datos.
	// - $dbname: Nombre de la base de datos.
	// - $type: Paso/Tipo.
	static function Recover($dbname, $type = 1)
	{
		global $config;
		$ab = Core::theSession('backup_db');
		
		if(!$config['server']['backup'] OR empty($ab))
			self::Error('mysql.recovery', __FUNCTION__, '%backup.disable%');
		
		if($type == 1)
		{
			mysql_query("CREATE DATABASE IF NOT EXISTS $dbname");
			mysql_select_db($dbname) or self::Error('mysql.recovery', __FUNCTION__, '%error.db%');
			
			Reg('%backup.createdb%');
			self::Recover($dbname, 2);
		}
		else
		{			
			$ab = explode(';', $ab);
			
			foreach($ab as $q)
			{				
				if(empty($q))
					continue;
					
				mysql_query(trim($q)) or self::Error('mysql.recovery', __FUNCTION__, '%error.backup.query% ' . $q);
			}
			
			Reg('%backup.correct%');
		}
	}
	
	// Hacer un backup de la base de datos.
	// - $tables (array): Tablas a recuperar.
	// - $out (Bool): Retornar la copia en texto plano, de otra manera retornar el nombre del archivo.
	static function Backup($tables = '', $out = false)
	{
		if(empty($tables))
		{
			$query = self::query('SHOW TABLES');
			
			while($row = mysql_fetch_row($query))
				$tables[] = $row[0];
		}
		else
			$tables = is_array($tables) ? $tables : explode(',', $tables);
			
		foreach($tables as $table)
		{
			$result = self::query("SELECT * FROM $table");
			$num_fields = mysql_num_fields($result);
    
			$return .= "DROP TABLE IF EXISTS $table;";
			$row2 = mysql_fetch_row(self::query("SHOW CREATE TABLE $table"));
			$return.= "\n\n". $row2[1] . ";\n\n";
    
			for ($i = 0; $i < $num_fields; $i++) 
			{
				while($row = mysql_fetch_row($result))
				{
					$return.= "INSERT INTO $table VALUES(";
				
					for($j=0; $j<$num_fields; $j++) 
					{
						$row[$j] = addslashes($row[$j]);
						//$row[$j] = preg_replace("\n","\\n",$row[$j]);
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
			
			return $bname;
		}
		
		return $return;
	}
}
?>