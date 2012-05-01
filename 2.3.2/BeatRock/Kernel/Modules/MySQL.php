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

class MySQL
{
	private static $c = null;
	private static $v = false;
	public static $q = 0;
	public static $lqq = "";
	private static $lq = null;
	private static $cache = Array();
	
	// Función privada - Lanzar error.
	// - $function: Función causante.
	// - $msg: Mensaje del error.
	private static function Error($code, $function, $msg = '')
	{		
		// Si esta vació el mensaje, usar el último error MySQL.
		if(empty($msg))
			$msg = mysql_error();
		
		BitRock::setStatus($msg, __FILE__, Array('function' => $function, 'query' => self::$lqq));
		BitRock::launchError($code);
		
		return false;
	}
	
	// Función privada - ¿Hay alguna conexión activa?
	public static function isReady()
	{
		if(self::$c == null OR !self::$v)
			return false;
			
		return true;
	}
	
	// Función privada - ¿Ya se ha hecho una consulta?
	private static function isQuery()
	{
		return self::$lq !== null ? true : false;
	}
	
	// Función - Destruir conexión activa.
	public static function Crash()
	{
		// Si no hay una conexión activa, cancelar.
		if(!self::isReady())
			return;
		
		// Cerrando conexión MySQL.
		@mysql_close();
		BitRock::log("Se ha desconectado del servidor MySQL correctamente.");
		
		// Restaurando variables.
		self::$c = null;
		self::$lq = null;
	}
	
	// Función - Conectar a un servidor MySQL.
	// - $host: Host de conexión.
	// - $username: Nombre de usuario.
	// - $password: Contraseña.
	// - $dbname: Nombre de la base de datos.
	// - $port: Puerto del servidor. (Predeterminado: 3306)
	public static function Connect($host = '', $username = '', $password = '', $dbname = '', $port = 3306)
	{
		global $config;
		
		// Destruir conexión activa.
		self::Crash();
		
		// Si los datos estan vacios, usar los del archivo de configuración.
		if(empty($host) OR empty($username))
		{			
			$host = $config['mysql']['host'];
			$username = $config['mysql']['user'];
			$password = $config['mysql']['pass'];
			$dbname = $config['mysql']['name'];			
		}
		
		// Por si ocurrio un error antes de preparar el archivo de configuración.
		if(empty($host))
			return;
			
		// Conectarse al servidor MySQL.
		$sql = @mysql_connect("$host:$port", $username, $password) or self::Error("01m", __FUNCTION__);
		
		BitRock::log("Se ha establecido una conexión al servidor MySQL en '$host' correctamente.", "mysql");
		self::$c = $sql;
		
		// Seleccionar la base de datos.
		@mysql_select_db($dbname, $sql) or self::Recover($dbname);
		
		// Realizar prueba de inicialización.
		if(@mysql_query("SELECT null FROM " . $config[mysql][alias] . "site_config") == false)
			self::Recover($dbname, 2);
			
		self::$v = true;
		
		// Optimizar y reparar la base de datos si así esta ajustado.
		if($config['mysql']['optimize'])
			self::Optimize();
			
		if($config['mysql']['repair'])
			self::Repair();
	}
	
	// Función - Ejecutar consulta en el servidor MySQL.
	// - $q: Consulta a ejecutar.
	// - $cache: ¿Guardar resultados en caché?
	public static function query($q, $cache = false)
	{
		// Si no hay una conexión activa, lanzar error.
		if(!self::isReady())
			return self::Error("02m", __FUNCTION__);
		
		// Queremos guardar la consulta en caché, si existe, retornarla.
		if($cache)
		{
			$h = md5($q);
			
			if(isset(self::$cache[$h]))
				return self::$cache[$h];
		}
		
		// Remplasando {DA} por el Alias y ejecutar consulta.
		$q = str_ireplace('{DA}', DB_ALIAS, $q);
		
		// Última consulta realizada.
		self::$lqq = $q;
		
		// Ejecutando consulta.
		$sql = @mysql_query($q) or self::Error("03m", __FUNCTION__);
		
		// Definir consultas realizadas y la última consulta.
		self::$q++;
		self::$lq = $sql;
		
		// Guardar la consulta en caché.
		if($cache)			
			self::$cache[$h] = $sql;
		
		BitRock::log("Se ha ejecutado la consulta '$q' dentro del servidor MySQL.", "mysql");
		return $sql;
	}
	
	// Función - Obtener numero de valores de una consulta MySQL.
	// - $q: Consulta a ejecutar.
	public static function query_rows($q)
	{
		// Si no hay una conexión activa, lanzar error.
		if(!self::isReady())
			return self::Error("02m", __FUNCTION__);
		
		$sql = self::query($q);
		return mysql_num_rows($sql);
	}
	
	// Función - Obtener los valores de una consulta MySQL.
	// - $q: Consulta a ejecutar.
	public static function query_assoc($q)
	{
		// Si no hay una conexión activa, lanzar error.
		if(!self::isReady())
			return self::Error("02m", __FUNCTION__);
		
		// Lanzar consultar, verificar si hay valores y devolverlos.
		$sql = self::query($q);
		$result = self::num_rows($sql) > 0 ? mysql_fetch_assoc($sql) : false;
		
		return $result;
	}
	
	// Función - Obtener un dato especifico de una consulta MySQL.
	// - $q: Consulta a ejecutar.
	// - $row: Dato a obtener.
	public static function query_get($q, $row)
	{
		// Si no hay una conexión activa, lanzar error.
		if(!self::isReady())
			return self::Error("02m", __FUNCTION__);
			
		$sql = self::query_assoc($q);		
		return $sql !== false ? $sql[$row] : false;		
	}
	
	// Función - Insertar datos en la base de datos.
	// - $table: Tabla a insertar los datos.
	// - $data (Array): Datos a insertar.
	public static function query_insert($table, $data)
	{
		// Si no hay una conexión activa, lanzar error.
		if(!self::isReady())
			return self::Error("02m", __FUNCTION__);
		
		// Si los datos no son un Array, cancelar.
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
		// Si no hay una conexión activa, lanzar error.
		if(!self::isReady())
			return self::Error("02m", __FUNCTION__);
		
		// Si las actualizaciones no son un Array, cancelar.
		if(!is_array($updates))
			return false;
		
		// Estructurar consulta y ejecutarla.
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
		// Si no hay una conexión activa, lanzar error.
		if(!self::isReady())
			return self::Error("02m", __FUNCTION__);

		// Ejecutando consulta.
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
		// Si el recurso esta vacio y la última consulta también, cancelar.
		if(empty($q) AND !self::isQuery())
			return self::Error("04m", __FUNCTION__);			
		
		// El recurso esta vacio, usar la última consulta.
		if(empty($q))
			$q = self::$lq;
			
		return mysql_num_rows($q);
	}
	
	// Función - Obtener los valores de un recurso MySQL o la última consulta hecha.
	// - $q: Recurso de la consulta.
	public static function fetch_assoc($q = '')
	{
		// Si el recurso esta vacio y la última consulta también, cancelar.
		if(empty($q) AND !self::isQuery())
			return self::Error("04m", __FUNCTION__);
		
		// El recurso esta vacio, usar la última consulta.
		if(empty($q))
			$q = self::$lq;
			
		return mysql_fetch_assoc($q);
	}
	
	// Función - Obtener un dato especifico de un recurso MySQL o la última consulta hecha.
	// - $row: Dato a obtener.
	// - $q: Recurso de la consulta.
	public static function get($row, $q = '')
	{
		// Si el recurso esta vacio y la última consulta también, cancelar.
		if(empty($q) AND !self::isQuery())
			return self::Error("04m", __FUNCTION__);
		
		// El recurso esta vacio, usar la última consulta.
		if(empty($q))
			$q = self::$lq;
			
		$r = self::fetch_assoc($q);
		return $r[$row];
	}
	
	// Función - Cambiar el motor de las tablas.
	// - $engine (MyISAM, INNODB): Motor a cambiar.
	// - $tables (Array): Tablas a cambiar.
	public static function to_engine($engine = 'MYISAM', $tables = '')
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
		$query = self::query("SHOW TABLES");
		
		while($row = mysql_fetch_row($query))
		{
			$fix = str_replace("_", " ", $row[0]);

			$r = query("SHOW COLUMNS FROM $row[0]");
			$rc = Array();
			
			if(mysql_num_rows($r) > 0)
			{
				while($roww = mysql_fetch_assoc($r))
					$rc[] = $roww['Field'];
			}

			$row[0] = str_replace(DA, "", $row[0]);

			$tables[] = Array(
				"name" => $row[0], 
				"name_fix" => $fix, 
				"translated" => Core::Translate($fix),
				"fields" => $rc
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
		
		// Obtener la última copia de la base de datos.		
		$ab = Core::theSession("backup_db");
		
		// La recuperación avanzada no esta activada o la copia esta vacia, lanzar error.
		if(!$config['server']['backup'] OR empty($ab))
			self::Error("06m", __FUNCTION__, "La recuperación avanzada esta desactivada o no fue posible encontrar una copia de la base de datos.");
		
		// Paso 1 - Creación de la base de datos.
		if($type == 1)
		{
			// Crear y seleccionar la base de datos.
			mysql_query("CREATE DATABASE IF NOT EXISTS $dbname");
			mysql_select_db($dbname) or self::Error("06m", __FUNCTION__, "No fue posible encontrar la base de datos, por favor asegurese de que esta exista y no se encuentre dañada.");
			
			// Ir al paso 2.
			BitRock::log("Se ha creado la base de datos correctamente, ahora se tratará de importar la información.");
			self::Recover($dbname, 2);
		}
		// Paso 2 - Importar la copia de seguridad.
		else
		{			
			$ab = explode(";", $ab);
			
			foreach($ab as $q)
			{				
				if(empty($q))
					continue;
					
				mysql_query(trim($q)) or self::Error("06m", __FUNCTION__, "No fue posible ejecutar '$q' durante la restauración de la base de datos.");
			}
			
			BitRock::log("Se ha importado la información a la base de datos correctamente.");
		}
	}
	
	// Función - Hacer un backup de la base de datos.
	// - $tables (Array): Tablas a recuperar.
	// - $out (Bool): Retornar la copia en texto plano, de otra manera retornar el nombre del archivo.
	public static function Backup($tables = '', $out = false)
	{
		if(empty($tables))
		{
			$query = self::query("SHOW TABLES");
			
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
			
		$return = str_replace("CREATE TABLE", "CREATE TABLE IF NOT EXISTS", $return);
		BitRock::log("Se ha procesado una recuperación de la base de datos correctamente.");
			
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