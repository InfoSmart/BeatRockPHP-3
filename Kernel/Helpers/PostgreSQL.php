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

## --------------------------------------------------
##        Módulo PostgreSQL
## --------------------------------------------------
## Este módulo contiene las funciones y herramientas
## necesarias para interactuar con el servidor MySQL.
## --------------------------------------------------

class SQL extends BaseSQL
{
	###############################################################
	## Conexión al servidor MySQL.
	## - $host: 		Host de conexión.
	## - $username: 	Nombre de usuario.
	## - $password: 	Contraseña.
	## - $dbname: 		Nombre de la base de datos.
	## - $port (int): 	Puerto del servidor. (Predeterminado: 3306)
	###############################################################
	static function Connect($host = '', $username = '', $password = '', $dbname = '', $port = 3306)
	{
		global $config;
		$sql = $config['sql'];
		
		Lang::SetSection('helper.sql');
		self::Disconnect();
		
		if( empty($host) OR empty($username) )
		{			
			$host 		= $sql['host'];
			$username 	= $sql['user'];
			$password 	= $sql['pass'];
			$dbname 	= $sql['name'];	
			$port		= $sql['port'];		
		}

		if( empty($host) OR empty($dbname) )
			return false;

		$conn = pg_connect("host=$host user=$username password=$password port=$port");

		if( !$conn )
			self::Error('sql.connect', pg_last_error($conn));

		if( CHARSET == 'UTF-8' )
			pg_query($conn, "set client_encoding to 'UNICODE'");

		self::$server 		= $conn;
		self::$connected 	= true;

		self::select_db($dbname);

		if( $sql['repair'] )
			self::Repair();

		$test = pg_query($conn, "SELECT null FROM $sql[prefix]site_config");
		
		if( !$test )
			self::Recover($dbname, 2);

		Reg('%connection.correct%', 'sql');
		return $conn;
	}

	###############################################################
	## Selecciona la base de datos a interactuar.
	## - $dbname: Nombre de la base de datos.
	###############################################################
	static function select_db($dbname)
	{
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		pg_query('\connect ' . pg_escape_string($dbname)) or self::Recover($dbname);
	}
	
	###############################################################
	## Ejecutar una consulta.
	## - $query: 			Consulta.
	## - $cache (bool): 	¿Almacenar en caché?
	## - $free 	(bool): 	¿Liberar memoria al terminar?
	###############################################################
	static function query($query, $cache = false, $free = false)
	{
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::query($q, $cache, $free);

			return $result;
		}
		
		$query 				= Keys($query, array('DA' => DB_PREFIX, 'DP' => DB_PREFIX));
		self::$last_query 	= $query;	
		$result 			= pg_query(self::$server, $query) or self::Error('sql.query');

		++self::$querys;

		if( $free OR self::$free_result )
		{
			self::Free($result);
			self::$last_resource 	= null;
		}
		else
			self::$last_resource 	= $result;

		gc_collect_cycles();
		
		Reg('%query.correct%', 'sql');
		return $result;
	}
	
	###############################################################
	## Obtener el numero de valores de una consulta.
	## - $query: 	Consulta.
	###############################################################
	static function Rows($query = '')
	{
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::Rows($q);

			return $result;
		}

		if( empty($query) AND !self::FirstQuery() )
			return self::Error('sql.query.need');

		if( is_string($query) )
		{
			if( !Contains($query, 'SELECT', true) )
				return self::Error('sql.query.novalid');
		
			$sql 	= self::query($query);
		}
		else
			$sql 	= self::$last_resource;

		$result = pg_num_rows($sql);

		if( self::$free_result )
			self::Free();

		return $result;
	}
	
	###############################################################
	## Obtener los valores de una consulta.
	## - $query: 	Consulta.
	###############################################################
	static function Assoc($query = '')
	{
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::Assoc($q);

			return $result;
		}

		if( empty($query) AND !self::FirstQuery() )
			return self::Error('sql.query.need');

		if( is_string($query) AND !empty($query) )
		{
			if( !Contains($query, 'SELECT', true) )
				return self::Error('sql.query.novalid');
		
			$sql 	= self::query($q);
			$result = ( self::Rows($sql) > 0 ) ? pg_fetch_assoc($sql) : false;
		}
		else
		{
			$sql 	= self::$last_resource;
			$result = pg_fetch_assoc($sql);
		}

		if( self::$free_result )
			self::Free();
		
		return $result;
	}

	###############################################################
	## Obtener los valores de una consulta.
	## - $query: 	Consulta.
	###############################################################
	static function Object($query = '')
	{
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::Object($q);

			return $result;
		}

		if( empty($query) AND !self::FirstQuery() )
			return self::Error('sql.query.need');

		if( is_string($query) AND !empty($query) )
		{
			if( !Contains($query, 'SELECT', true) )
				return self::Error('sql.query.novalid');
		
			$sql 	= self::query($q);
			$result = ( self::Rows($sql) > 0 ) ? pg_fetch_object($sql) : false;
		}
		else
		{
			$sql 	= self::$last_resource;
			$result = pg_fetch_object($sql);
		}

		if( self::$free_result )
			self::Free();
		
		return $result;
	}

	###############################################################
	## Obtener los valores de una consulta.
	## - $query: 	Consulta.
	###############################################################
	static function GetArray($query = '')
	{
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::GetArray($q);

			return $result;
		}

		if( empty($query) AND !self::FirstQuery() )
			return self::Error('sql.query.need');

		if( is_string($query) AND !empty($query) )
		{
			if( !Contains($query, 'SELECT', true) )
				return self::Error('sql.query.novalid');
		
			$sql 	= self::query($q);
			$result = ( self::Rows($sql) > 0 ) ? pg_fetch_array($sql) : false;
		}
		else
		{
			$sql 	= self::$last_resource;
			$result = pg_fetch_array($sql);
		}

		if( self::$free_result )
			self::Free();
		
		return $result;
	}

	###############################################################
	## Obtener los valores de una consulta.
	## - $query: 	Consulta.
	###############################################################
	static function Row($query = '')
	{
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::Row($q);

			return $result;
		}

		if( empty($query) AND !self::FirstQuery() )
			return self::Error('sql.query.need');

		if( is_string($query) AND !empty($query) )
		{
			if( !Contains($query, 'SELECT', true) )
				return self::Error('sql.query.novalid');
		
			$sql 	= self::query($q);
			$result = ( self::Rows($sql) > 0 ) ? pg_fetch_row($sql) : false;
		}
		else
		{
			$sql 	= self::$last_resource;
			$result = pg_fetch_row($sql);
		}

		if( self::$free_result )
			self::Free();
		
		return $result;
	}
		
	###############################################################
	## Obtener un valor especifico de una consulta.
	## - $query: Consulta.
	###############################################################
	static function Get($query)
	{
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::Get($q);

			return $result;
		}

		preg_match("/SELECT ([^<]+) FROM/is", $query, $params);

		if( !Contains($query, 'SELECT', true) OR empty($params[1]) OR $params[1] == '*' OR strtolower($params[1]) == 'null' )
			return self::Error('sql.query.novalid');

		$pp 	= explode(',', $params[1]);	
		$row 	= self::Assoc($query);
		$result = false;

		if( !$row )
			return false;

		if( count($pp) > 1 )
		{
			foreach( $pp as $param )
				$result[$param] = $row[$param];
		}
		else
			$result = $row[$params[1]];

		if( self::$free_result )
			self::Free();

		return $result;
	}
	
	###############################################################
	## Insertar datos en una tabla.
	## - $table: 			Tabla.
	## - $data (array): 	Datos.
	###############################################################
	static function Insert($table, $data)
	{
		if( !self::Connected() )
			return self::Error('sql.need.connection');
		
		if( !is_array($data) )
			return false;
			
		$values = array_values($data);
		$keys 	= array_keys($data);
		
		return self::query("INSERT INTO {DP}$table (" . implode(',', $keys) . ") VALUES ('" . implode('\',\'', $values) . "')");
	}
	
	###############################################################
	## Actualizar los datos de una tabla.
	## - $table: 			Tabla.
	## - $updates (array): 	Datos a actualizar.
	## - $where (array): 	Condiciones a cumplir.
	## - $limt (int): 		Limite de columnas a actualizar.
	###############################################################
	static function Update($table, $updates, $where = '', $limit = 1)
	{
		if( !self::Connected() )
			return self::Error('sql.need.connection');
		
		if( !is_array($updates) )
			return false;
		
		$query 	= "UPDATE {DP}$table SET ";
		$i 		= 0;
		
		foreach( $updates as $key => $value )
		{
			$i++;			
			$query .= "$key = '$value'";
			
			if( count($updates) !== $i )
				$query .= ',';
		}
		
		if( !empty($where) )
		{
			$query .= ' WHERE ';
			
			foreach( $where as $key )
				$query .= "  $key";
		}
		
		if( $limit !== 0 )
			$query .= " LIMIT $limit";
		
		return self::query($query);
	}

	###############################################################
	## Obtener toda la información de una consulta.
	## - $query: 	Consulta a ejecutar.
	###############################################################
	static function Data($query)
	{
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		$sql = self::query($query);

		if( !$sql )
			return false;

		$result = array(
			'resource' 	=> $sql,
			'assoc' 	=> pg_fetch_assoc($sql),
			'rows' 		=> pg_num_rows($sql)
		);

		if( self::$free_result )
			self::Free();

		return $result;
	}

	###############################################################
	## Obtener las filas que han sido afectadas en la última consulta.
	###############################################################
	static function Affected()
	{
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		if( !self::FirstQuery() )
			return self::Error('sql.query.need');

		return pg_affected_rows(self::$last_resource);
	}

	###############################################################
	## Obtener la última ID insertada en la base de datos.
	###############################################################
	static function LastID()
	{
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		# TODO: ¡Completarlo!
		return false;
	}

	###############################################################
	## Aplicar filtro anti SQL Inyection a una cadena.
	## - $str: 	Cadena.
	###############################################################
	static function Escape($str)
	{
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		return self::$server->escape_string($str);
	}

	###############################################################
	## Liberar la memoria de la última consulta realizada.
	## - $query: 	Recurso de la consulta.
	###############################################################
	static function Free($query = '')
	{
		if( empty($query) AND !self::FirstQuery() )
			return self::Error('sql.query.need');
		
		if( empty($query) )
			$query = self::$last_resource;

		if( self::$free_result )
			self::$free_result = false;

		return $query->free();
	}
	
	###############################################################
	## Obtener un valor especifico de un recurso MySQL o la última consulta hecha.
	## - $row: 		Valor a obtener.
	## - $query: 	Recurso de la consulta.
	###############################################################
	static function GetData($row, $query = '')
	{
		if( empty($query) AND !self::FirstQuery() )
			return self::Error('sql.query.need');
		
		if( empty($query) )
			$query = self::$last_resource;
			
		$result = self::Assoc($query);
		return $result[$row];
	}
	
	###############################################################
	## Cambia el motor de las tablas especificadas.
	## - $engine (MyISAM, INNODB): 	Nuevo motor.
	## - $tables (array): 			Tablas
	###############################################################
	static function Engine($engine = 'MYISAM', $tables = '')
	{		
		if( $engine !== 'MYISAM' AND $engine !== 'INNODB' )
			return self::Error('sql.engine');
			
		if( empty($tables) )
		{
			$query = self::query('SHOW TABLES');
			
			while( $tmp = self::GetArray($query) )
				self::query("ALTER TABLE $tmp[0] ENGINE = $engine");
		}
		else if( is_array($tables) )
		{
			foreach( $tables as $t )
				self::query("ALTER TABLE $t ENGINE = $engine");
		}
		
		Reg("%engine_correct% $engine");
	}
	
	###############################################################
	## Optimiza las tablas especificadas.
	## - $tables (array): 	Tablas.
	###############################################################
	static function Optimize($tables = '')
	{
		if( empty($tables) )
		{
			$query = self::query('SHOW TABLES');
			
			while( $tmp = self::GetArray($query) )
				self::query("OPTIMIZE TABLE $tmp[0]");
		}
		else if( is_array($tables) )
		{
			foreach( $tables as $t )
				self::query("OPTIMIZE TABLE $t");
		}
		
		Reg('%optimize_correct%');
	}
	
	###############################################################
	## Reparar las tablas especificadas.
	## - $tables (array): 	Tablas.
	###############################################################
	static function Repair($tables = '')
	{
		if( empty($tables) )
		{
			$query = self::query('SHOW TABLES');
			
			while( $tmp = self::GetArray($query) )
				self::query("REPAIR TABLE $tmp[0]");
		}
		else if( is_array($tables) )
		{
			foreach( $tables as $t )
				self::query("REPAIR TABLE $t");
		}
		
		Reg('%repair.correct%');
	}

	###############################################################
	## Examina las tablas.
	###############################################################
	static function Examine()
	{
		$result = array();
		$query 	= self::query('SHOW TABLES');
		
		while( $row = self::Row($query) )
		{
			$fix = str_replace('_', ' ', $row[0]);

			$columns 		= self::query("SHOW COLUMNS FROM $row[0]");
			$row_columns 	= array();
			
			if( self::Rows($columns) > 0 )
			{
				while( $col = self::Assoc($columns) )
					$row_columns[] = $col['Field'];
			}

			$row[0] = str_replace(DA, '', $row[0]);

			$tables[] = array(
				'name' 			=> $row[0], 
				'name_fix' 		=> $fix, 
				'translated' 	=> Core::Translate($fix),
				'fields' 		=> $row_columns
			);
		}

		$result = array(
			'tables' 	=> $tables,
			'count' 	=> count($tables)
		);

		return $result;
	}
	
	###############################################################
	## Recuperar/Restaurar la base de datos.
	## - $dbname: 	Nombre de la base de datos.
	## - $step: 	Paso.
	###############################################################
	static function Recover($dbname, $step = 1)
	{
		global $config;
		$backup = _SESSION('backup_db');
		
		if( !$config['server']['backup'] OR empty($backup) )
			self::Error('sql.recovery', '%backup.disable%');
		
		if( $type == 1 )
		{
			self::$server->query("CREATE DATABASE IF NOT EXISTS $dbname");
			self::$server->select_db($dbname) or self::Error('sql.recovery', '%error.db%');
			
			Reg('%backup.createdb%');
			self::Recover($dbname, 2);
		}
		else
		{			
			$backup = explode(";\n", $backup);
			
			foreach( $backup as $query )
			{
				$query 	= trim($query);

				if( empty($query) )
					continue;
					
				$result = self::$server->query($query) or self::Error('sql.recovery', '%error.backup.query% ' . $query);
			}
			
			Reg('%backup.correct%');
		}

		Core::SendRecover();
	}
	
	###############################################################
	## Hacer un backup de la base de datos.
	## - $tables (array): 	Tablas a recuperar.
	## - $out (bool): 		Retornar la copia en texto plano, de otra manera retornar el nombre del archivo.
	###############################################################
	static function Backup($tables = '', $out = false)
	{
		global $site;

		if( empty($tables) )
		{
			$query = self::query('SHOW TABLES');
			
			while( $row = $query->fetch_row() )
				$tables[] = $row[0];
		}
		else
			$tables = ( is_array($tables) ) ? $tables : explode(',', $tables);
			
		foreach( $tables as $table )
		{
			$result 	= self::query("SELECT * FROM $table");
			$num_fields = $result->field_count;
    
			$return 	.= "DROP TABLE IF EXISTS $table;";
			$row2 		= self::query("SHOW CREATE TABLE $table")->fetch_row();
			$return		.= "\n\n". $row2[1] . ";\n\n";
    
			for ( $i = 0; $i < $num_fields; $i++ ) 
			{
				while( $row = $result->fetch_row() )
				{
					$return.= "INSERT INTO $table VALUES(";
				
					for( $j=0; $j < $num_fields; $j++ ) 
					{
						$row[$j] = addslashes($row[$j]);
						$row[$j] = str_replace("\n","\\n", $row[$j]);
					
						if( isset($row[$j]) )
							$return.= '"' . $row[$j] . '"' ;
						else
							$return.= '""';
						
						if( $j < ($num_fields-1) )
							$return.= ',';
					}
				
					$return.= ");\n";
				}
			}
		
			$return.="\n\n\n";
		}
		
		if( empty($return) )
			return false;
			
		$return = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $return);
		Reg('%backup.create%');
			
		if( !$out )
		{
			$bname = 'DB-Backup-' . date('d_m_Y') . '-' . time() . '.sql';
			Io::SaveBackup($bname, $return);

			if( $site['site_backups_servers'] == 'true' )
				Bit::Send_FTPBackup(BIT . 'Backups' . DS . $bname, $bname);
			
			return $bname;
		}
		
		return $return;
	}
}
?>