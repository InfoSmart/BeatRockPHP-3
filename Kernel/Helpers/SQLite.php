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
##        Módulo SQLite
## --------------------------------------------------
## Este módulo contiene las funciones y herramientas
## necesarias para interactuar con el servidor MySQL.
## --------------------------------------------------

class SQL extends BaseSQL
{
	###############################################################
	## Conexión al servidor SQLite.
	# - $dbname: Ruta a la base de datos.
	###############################################################
	static function Connect($dbname = '')
	{
		global $config;
		$sql = $config['sql'];

		Lang::SetSection('mod.sql');
		self::Destroy();
		
		if( empty($dbname) )
			$dbname = $sql['name'];

		if( empty($dbname) )
			return false;

		$conn = new SQLite3($dbname) or self::Error('sql.connect');

		self::$server 		= $conn;
		self::$connected 	= true;

		if( $sql['repair'] )
			self::Repair();

		Reg('%connection.correct%', 'sql');
		return $conn;
	}

	###############################################################
	## Ejecutar una consulta.
	## - $query: 			Consulta.
	###############################################################
	static function query($query, $cache = false, $free = false)
	{
		if( !self::Ready() )
			return self::Error('sql.need.connection');

		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::query($q);

			return $result;
		}
		
		$query 				= Keys($query, array('DA' => DB_PREFIX, 'DP' => DB_PREFIX));
		self::$last_query 	= $query;		
		$result 			= self::$server->query($query) or self::Error('sql.query');

		++self::$querys;
		self::$last_resource = $result;

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
		if( !self::Ready() )
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

			$query 	= str_ireplace('null', 'COUNT(*) as count', $query);
			$sql 	= self::query($query);
		}
		else
			$sql 	= self::$last_resource;

		$result = $sql->fetchArray();
		return $result['count'];
	}

	###############################################################
	## Obtener los valores de una consulta.
	## - $query: 	Consulta.
	###############################################################
	static function Assoc($query = '')
	{
		if( !self::Ready() )
			return self::Error('sql.need.connection');

		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::Assoc($q);

			return $result;
		}

		if( empty($query) AND !self::FirstQuery() )
			return self::Error('sql.query.need');

		if( is_string($query) )
		{
			if( !Contains($q, 'SELECT', true) )
				return self::Error('sql.query.novalid');

			$sql 	= self::query($query);
			$result = ( self::Rows($sql) > 0 ) ? $sql->fetchArray(SQLITE3_ASSOC) : false;
		}
		else
		{
			$sql 	= self::$last_resource;
			$result = $sql->fetchArray(SQLITE3_ASSOC);
		}		

		return $result;
	}

	###############################################################
	## Obtener los valores de una consulta.
	## - $query: 	Consulta.
	###############################################################
	static function Object($query = '')
	{
		$assoc 	= self::Assoc($query);
		$result = new stdClass();

		foreach( $assoc as $key => $value )
			$result->{$key} = $value;		

		return $result;
	}

	###############################################################
	## Obtener los valores de una consulta.
	## - $query: 	Consulta.
	###############################################################
	static function Array($query = '')
	{
		return self::Assoc($query);
	}

	###############################################################
	## Obtener un valor especifico de una consulta.
	## - $query: Consulta.
	###############################################################
	static function Get($query = '')
	{
		if( !self::Ready() )
			return self::Error('sql.need.connection');

		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::Get($q);

			return $result;
		}
		
		$query 				= Keys($query, array('DA' => DB_PREFIX, 'DP' => DB_PREFIX));	
		self::$last_query 	= $query;		
		$result 			= self::$server->querySingle($query) or self::Error('sqlite.query');

		++self::$querys;
		gc_collect_cycles();
		
		Reg('%query.correct%', 'sql');
		return $result;
	}

	###############################################################
	## Insertar datos en una tabla.
	## - $table: 			Tabla.
	## - $data (array): 	Datos.
	###############################################################
	static function Insert($table, $data)
	{
		if( !self::Ready() )
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
		if( !self::Ready() )
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
		if( !self::Ready() )
			return self::Error('sql.need.connection');

		$sql = self::query($q);

		if( !$sql )
			return false;

		$result = array(
			'resource' 	=> $sql,
			'assoc' 	=> $sql->fetchArray(SQLITE3_ASSOC),
			'rows' 		=> $sql->numColumns()
		);

		return $result;
	}

	###############################################################
	## Obtener las filas que han sido afectadas en la última consulta.
	###############################################################
	static function Affected()
	{
		if( !self::Ready() )
			return self::Error('sql.need.connection');

		return self::$server->changes();
	}

	###############################################################
	## Obtener la última ID insertada en la base de datos.
	###############################################################
	static function LastID()
	{
		if( !self::Ready() )
			return self::Error('sql.need.connection');

		return self::$server->lastInsertRowID();
	}

	###############################################################
	## Aplicar filtro anti SQL Inyection a una cadena.
	## - $str: 	Cadena.
	###############################################################
	static function Escape($str)
	{
		if( !self::Ready() )
			return self::Error('sql.need.connection');

		return self::$server->escapeString($str);
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
		
		if(empty($q))
			$q = self::$last_resource;
			
		$r = self::fetch_assoc($q);
		return $r[$row];
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