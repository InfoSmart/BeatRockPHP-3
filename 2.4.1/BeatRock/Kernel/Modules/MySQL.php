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

class MySQL
{
	private static $connection = null;
	private static $connected = false;
	public static $querys = 0;
	public static $last_query = "";
	public static $last_resource = null;
	private static $cache = Array();
	
	// Función privada - Lanzar error.
	// - $function: Función causante.
	// - $msg: Mensaje del error.
	private static function Error($code, $function, $msg = '')
	{		
		if(empty($msg))
			$msg = mysql_error();
		
		BitRock::setStatus($msg, __FILE__, Array('function' => $function, 'query' => self::$last_query));
		BitRock::launchError($code);
		
		return false;
	}
	
	// Función privada - ¿Hay alguna conexión activa?
	public static function Ready()
	{
		if(self::$connection == null OR !self::$connected)
			return false;
			
		return true;
	}
	
	// Función privada - ¿Ya se ha hecho una consulta?
	private static function isQuery()
	{
		return !empty(self::$last_query) ? true : false;
	}
	
	// Función - Destruir conexión activa.
	public static function Crash()
	{
		if(!self::Ready())
			return;
		
		mysql_close();
		BitRock::log('Se ha desconectado del servidor MySQL correctamente.');

		self::$connection = null;
		self::$connected = false;
		self::$last_query = "";
	}
	
	// Función - Conexión al servidor MySQL.
	// - $host: Host de conexión.
	// - $username: Nombre de usuario.
	// - $password: Contraseña.
	// - $dbname: Nombre de la base de datos.
	// - $port: Puerto del servidor. (Predeterminado: 3306)
	public static function Connect($host = '', $username = '', $password = '', $dbname = '', $port = 3306)
	{
		global $config;
		$mysql = $config['mysql'];
		
		self::Crash();
		
		if(empty($host) OR empty($username))
		{			
			$host = $mysql['host'];
			$username = $mysql['user'];
			$password = $mysql['pass'];
			$dbname = $mysql['name'];			
		}
		
		if(empty($host))
			return;
			
		$sql = mysql_connect("$host:$port", $username, $password) or self::Error('01m', __FUNCTION__);
		
		BitRock::log('Se ha establecido una conexión al servidor MySQL en '.$host.' correctamente.', 'mysql');
		self::$connection = $sql;

		mysql_select_db($dbname, $sql) or self::Recover($dbname);
		
		if(@mysql_query("SELECT null FROM $mysql[alias]site_config") == false)
			self::Recover($dbname, 2);
			
		self::$connected = true;
		
		if($config['mysql']['optimize'])
			self::Optimize();
			
		if($config['mysql']['repair'])
			self::Repair();
	}
	
	// Función - Ejecutar consulta en el servidor MySQL.
	// - $q: Consulta a ejecutar.
	public static function query($q)
	{
		if(!self::Ready())
			return self::Error("02m", __FUNCTION__);

		if(is_array($q))
		{
			foreach($q as $query)
				$result[] = self::query($query);

			return $result;
		}
		
		$q = str_ireplace('{DA}', DB_ALIAS, $q);		
		self::$last_query = $q;
		
		$sql = mysql_query($q) or self::Error('03m', __FUNCTION__);
		
		self::$querys++;
		self::$last_resource = $sql;
		
		BitRock::log('Se ha ejecutado la consulta "'.$q.'" dentro del servidor MySQL.', 'mysql');
		return $sql;
	}
	
	// Función - Obtener numero de valores de una consulta MySQL.
	// - $q: Consulta a ejecutar.
	public static function query_rows($q)
	{
		if(!self::Ready())
			return self::Error('02m', __FUNCTION__);

		if(is_array($q))
		{
			foreach($q as $query)
				$result[] = self::query_rows($query);

			return $result;
		}

		if(!Contains($q, 'SELECT', true))
			return self::Error('07m', __FUNCTION__);
		
		$sql = self::query($q);
		return mysql_num_rows($sql);
	}
	
	// Función - Obtener los valores de una consulta MySQL.
	// - $q: Consulta a ejecutar.
	public static function query_assoc($q)
	{
		if(!self::Ready())
			return self::Error('02m', __FUNCTION__);

		if(is_array($q))
		{
			foreach($q as $query)
				$result[] = self::query_assoc($query);

			return $result;
		}

		if(!Contains($q, 'SELECT', true))
			return self::Error('07m', __FUNCTION__);
		
		$sql = self::query($q);
		$result = self::num_rows($sql) > 0 ? mysql_fetch_assoc($sql) : false;
		
		return $result;
	}
	
	// Función - Obtener un dato especifico de una consulta MySQL.
	// - $q: Consulta a ejecutar.
	// - $row: Dato a obtener.
	public static function query_get($q)
	{
		if(!self::Ready())
			return self::Error('02m', __FUNCTION__);

		if(is_array($q))
		{
			foreach($q as $query)
				$result[] = self::query_get($query);

			return $result;
		}

		preg_match("/SELECT ([^<]+) FROM/is", $q, $params);

		if(!Contains($q, 'SELECT', true) OR empty($params[1]) OR $params[1] == '*' OR $params[1] == 'null')
			return self::Error("07m", __FUNCTION__);

		$pp = explode(',', $params[1]);			
		$row = self::query_assoc($q);	

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

		return $result;
	}
	
	// Función - Insertar datos en la base de datos.
	// - $table: Tabla a insertar los datos.
	// - $data (Array): Datos a insertar.
	public static function query_insert($table, $data)
	{
		if(!self::Ready())
			return self::Error('02m', __FUNCTION__);
		
		if(!is_array($data))
			return false;
			
		$values = array_values($data);
		$keys = array_keys($data);
		
		return self::query("INSERT INTO {DA}$table (" . implode(',', $keys) . ") VALUES ('" . implode('\',\'', $values) . "')");
	}
	
	// Función - Actualizar datos en la base de datos.
	// - $table: Tabla a insertar los datos.
	// - $updates (Array): Datos a actualizar.
	// - $where (Array): Condiciones a cumplir.
	// - $limt (Int): Limite de columnas a actualizar.
	public static function query_update($table, $updates, $where = '', $limit = 1)
	{
		if(!self::Ready())
			return self::Error("02m", __FUNCTION__);
		
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

	// Función - Obtener toda la información de una consulta.
	// - $q: Consulta a ejecutar.
	public static function query_data($q)
	{
		if(!self::Ready())
			return self::Error("02m", __FUNCTION__);

		$sql = self::query($q);

		if($sql == false)
			return false;

		return Array(
			'resource' => $sql,
			'assoc' => mysql_fetch_assoc($sql),
			'rows' => mysql_num_rows($sql)
		);
	}
	
	// Función - Obtener numero de valores de un recurso MySQL o la última consulta hecha.
	// - $q: Recurso de una consulta.
	public static function num_rows($q = '')
	{
		if(empty($q) AND !self::isQuery())
			return self::Error("04m", __FUNCTION__);			
		
		if(empty($q))
			$q = self::$last_resource;
			
		return mysql_num_rows($q);
	}
	
	// Función - Obtener los valores de un recurso MySQL o la última consulta hecha.
	// - $q: Recurso de la consulta.
	public static function fetch_assoc($q = '')
	{
		if(empty($q) AND !self::isQuery())
			return self::Error("04m", __FUNCTION__);
		
		if(empty($q))
			$q = self::$last_resource;
			
		return mysql_fetch_assoc($q);
	}
	
	// Función - Obtener un dato especifico de un recurso MySQL o la última consulta hecha.
	// - $row: Dato a obtener.
	// - $q: Recurso de la consulta.
	public static function get($row, $q = '')
	{
		if(empty($q) AND !self::isQuery())
			return self::Error("04m", __FUNCTION__);
		
		if(empty($q))
			$q = self::$last_resource;
			
		$r = self::fetch_assoc($q);
		return $r[$row];
	}
	
	// Función - Cambiar el motor de las tablas.
	// - $engine (MyISAM, INNODB): Motor a cambiar.
	// - $tables (Array): Tablas a cambiar.
	public static function Engine($engine = 'MYISAM', $tables = '')
	{		
		if($engine !== 'MYISAM' AND $engine !== 'INNODB')
			return self::Error("05m", __FUNCTION__);
			
		if(empty($tables))
		{
			$query = self::query("SHOW TABLES");
			
			while($tmp = mysql_fetch_array($query))
				self::query("ALTER TABLE $tmp[0] ENGINE = $engine");
		}
		else if(is_array($tables))
		{
			foreach($tables as $t)
				self::query("ALTER TABLE $t ENGINE = $engine");
		}
		
		BitRock::log("Se han aplicado cambios en el motor de la base de datos a $engine");
	}
	
	// Función - Optimizar las tablas.
	// - $tables (Array): Tablas a optimizar.
	public static function Optimize($tables = '')
	{
		if(empty($tables))
		{
			$query = self::query("SHOW TABLES");
			
			while($tmp = mysql_fetch_array($query))
				self::query("OPTIMIZE TABLE $tmp[0]");
		}
		else if(is_array($tables))
		{
			foreach($tables as $t)
				self::query("OPTIMIZE TABLE $t");
		}
		
		BitRock::log("Se ha optimizado la base de datos correctamente.");
	}
	
	// Función - Reparar las tablas.
	// - $tables (Array): Tablas a reparar.
	public static function Repair($tables = '')
	{
		if(empty($tables))
		{
			$query = self::query("SHOW TABLES");
			
			while($tmp = mysql_fetch_array($query))
				self::query("REPAIR TABLE $tmp[0]");
		}
		else if(is_array($tables))
		{
			foreach($tables as $t)
				self::query("REPAIR TABLE $t");
		}
		
		BitRock::log("Se ha reparado la base de datos correctamente.");
	}

	// Función - Examinar la base de datos.
	public static function Examine()
	{
		$result = Array();
		$query = self::query('SHOW TABLES');
		
		while($row = mysql_fetch_row($query))
		{
			$fix = str_replace("_", " ", $row[0]);

			$r = query('SHOW COLUMNS FROM $row[0]');
			$rc = Array();
			
			if(mysql_num_rows($r) > 0)
			{
				while($roww = mysql_fetch_assoc($r))
					$rc[] = $roww['Field'];
			}

			$row[0] = str_replace(DA, "", $row[0]);

			$tables[] = Array(
				'name' => $row[0], 
				'name_fix' => $fix, 
				'translated' => Core::Translate($fix),
				'fields' => $rc
			);
		}

		$result = Array(
			'tables' => $tables,
			'count' => count($tables)
		);

		return $result;
	}
	
	// Función - Recuperar/Restaurar la base de datos.
	// - $dbname: Nombre de la base de datos.
	// - $type: Paso/Tipo.
	public static function Recover($dbname, $type = 1)
	{
		global $config;
		$ab = Core::theSession('backup_db');
		
		if(!$config['server']['backup'] OR empty($ab))
			self::Error("06m", __FUNCTION__, 'La recuperación avanzada esta desactivada o no fue posible encontrar una copia de la base de datos.');
		
		if($type == 1)
		{
			mysql_query("CREATE DATABASE IF NOT EXISTS $dbname");
			mysql_select_db($dbname) or self::Error("06m", __FUNCTION__, 'No fue posible encontrar la base de datos, por favor asegurese de que esta exista y no se encuentre dañada.');
			
			BitRock::log('Se ha creado la base de datos correctamente, ahora se tratará de importar la información.');
			self::Recover($dbname, 2);
		}
		else
		{			
			$ab = explode(';', $ab);
			
			foreach($ab as $q)
			{				
				if(empty($q))
					continue;
					
				mysql_query(trim($q)) or self::Error('06m', __FUNCTION__, 'No fue posible ejecutar "'.$q.'" durante la restauración de la base de datos.');
			}
			
			BitRock::log('Se ha importado la información a la base de datos correctamente.');
		}
	}
	
	// Función - Hacer un backup de la base de datos.
	// - $tables (Array): Tablas a recuperar.
	// - $out (Bool): Retornar la copia en texto plano, de otra manera retornar el nombre del archivo.
	public static function Backup($tables = '', $out = false)
	{
		if(empty($tables))
		{
			$query = self::query('SHOW TABLES'
				);
			
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
		BitRock::log('Se ha procesado una recuperación de la base de datos correctamente.');
			
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