<?
/**
 * BeatRock
 *
 * Framework para el desarrollo de aplicaciones web.
 *
 * @author 		Iván Bravo <webmaster@infosmart.mx> @Kolesias123
 * @copyright 	InfoSmart 2013. Todos los derechos reservados.
 * @license 	http://creativecommons.org/licenses/by-sa/2.5/mx/  Creative Commons "Atribución-Licenciamiento Recíproco"
 * @link 		http://beatrock.infosmart.mx/
 * @version 	3.0
 *
 * @package 	MySQL
 * Driver necesario para interactuar con un servidor MySQL.
 *
*/

# Acción ilegal.
if( !defined('BEATROCK') )
	exit;

class SQL extends BaseSQL
{
	/**
	 * Conexión al servidor MySQL.
	 * @param string  $host     Host de conexión.
	 * @param string  $username Nombre de usuario.
	 * @param string  $password Contraseña.
	 * @param string  $dbname   Nombre de la base de datos.
	 * @param integer $port     Puerto del servidor. (Predeterminado: 3306)
	 * @return resource 		Conexión.
	 */
	static function Connect($host = '', $username = '', $password = '', $dbname = '', $port = 3306)
	{
		global $config;
		$sql = $config['sql'];

		Lang::SetSection('controller.mysql');

		# Desconectamos cualquier servidor activo.
		self::Disconnect();

		# $host O $username estan vacios, usar los del archivo de configuración.
		if( empty($host) OR empty($username) )
		{
			$host 		= $sql['host'];
			$username 	= $sql['user'];
			$password 	= $sql['pass'];
			$dbname 	= $sql['name'];
			$port		= $sql['port'];
		}

		# ¡Aún siguen vacios! Algo raro esta pasando aquí...
		if( empty($host) OR empty($dbname) )
			return false;

		# Realizamos conexión.
		$con = new MySQLi($host, $username, $password, '', $port);

		# ¡Un error!
		if( $con->connect_error )
			self::Error('sql.connect', $conn->connect_error);

		# Codificación UTF-8
		if( CHARSET == 'UTF-8' )
			$con->query("SET NAMES 'utf8'");

		# Establecemos las variables.
		self::$server 		= $con;
		self::$connected 	= true;

		# Seleccionamos la base de datos.
		self::select_db($dbname);

		# Reparación de inicio (Si esta ajustado)
		if( $sql['repair'] )
			self::Repair();

		# Hacemos prueba de acceso a la tabla site_config
		$test = $con->query("SELECT null FROM $sql[prefix]site_config");

		# ¡Prueba fallida! Al parecer no existe la tabla.
		# Intentamos recuperar la base de datos.
		if( !$test )
			self::Recover($dbname, 2);

		Reg('%connection.correct%', 'sql');
		return $con;
	}

	/**
	 * Selecciona la base de datos a interactuar.
	 * @param  string $dbname [description]
	 */
	static function select_db($dbname)
	{
		# Necesitamos habernos conectado.
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		# Intentamos seleccionar la base de datos.
		# Si no existe, la recuperamos.
		self::$server->select_db($dbname) or self::Recover($dbname);
	}

	/**
	 * Ejecutar una consulta.
	 * @param  string  $query Consulta.
	 * @param  boolean $cache ¿Guardar en caché?
	 * @param  boolean $free  ¿Liberar memoria al finalizar?
	 * @return resource       Recurso de la consulta.
	 */
	static function query($query, $cache = false, $free = false)
	{
		# Necesitamos habernos conectado.
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		# La consulta es un array, ejecutamos cada consulta y devolvemos
		# su resultado.
		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::query($q, $cache, $free);

			return $result;
		}

		# Traducimos los posibles "accesos directos"
		# includos {DA} y {DP}
		$query 	= Keys($query, array('DA' => DB_PREFIX, 'DP' => DB_PREFIX));

		# Para guardar en caché es necesario la extensión mysqlnd_qc
		if( $cache == true AND extension_loaded('mysqlnd_qc') )
			$query = '/*' . MYSQLND_QC_ENABLE_SWITCH  . '*/' . $query;

		# Ajustamos la última consulta realizada.
		# !!FIX
		# Es necesario ajustarla antes de realizar la consulta para que en caso
		# de un error se tome esta variable como referencia.
		self::$lastQuery 	= $query;
		# Realizamos la consulta.
		$result 			= self::$server->query($query) or self::Error('sql.query');

		# +1 consulta.
		++self::$querys;

		# Liberamos memoria.
		if( $free OR self::$freeResult )
		{
			self::Free($result);
			self::$lastResource 	= null;
		}
		else
			self::$lastResource 	= $result;

		gc_collect_cycles();

		Reg('%query.correct%', 'sql');
		return $result;
	}

	/**
	 * Obtener el numero de filas de una consulta.
	 * @param string $query Consulta O recurso de la consulta. Si
	 * se deja vacio se usará el recurso de la última consulta hecha.
	 */
	static function Rows($query = '')
	{
		# Necesitamos habernos conectado.
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		# La consulta es un array, ejecutamos cada consulta y devolvemos
		# su resultado.
		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::Rows($q);

			return $result;
		}

		# $query esta vacia y no se ha hecho ninguna consulta antes.
		if( empty($query) AND !self::FirstQuery() )
			return self::Error('sql.query.need');

		# Estamos realizando la consulta.
		if( is_string($query) AND !empty($query) )
		{
			# ¡No contiene SELECT!
			if( !Contains($query, 'SELECT', true) )
				return self::Error('sql.query.novalid');

			# Ejecutamos la consulta y obtenemos su recurso.
			$sql = self::query($query);
		}

		else if ( is_object($query) )
			$sql = $query;

		# Establecemos el recurso de la última consulta hecha.
		else
			$sql = self::$lastResource;

		# Obtenemos las filas.
		$result = $sql->num_rows;

		# Liberamos memoria.
		if( self::$freeResult )
			self::Free();

		return $result;
	}

	/**
	 * Obtener los valores de una consulta.
	 * @param string $query Consulta O recurso de la consulta. Si
	 * se deja vacio se usará el recurso de la última consulta hecha.
	 */
	static function Assoc($query = '')
	{
		# Necesitamos habernos conectado.
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		# La consulta es un array, ejecutamos cada consulta y devolvemos
		# su resultado.
		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::Assoc($q);

			return $result;
		}

		# $query esta vacia y no se ha hecho ninguna consulta antes.
		if( empty($query) AND !self::FirstQuery() )
			return self::Error('sql.query.need');

		# Estamos realizando la consulta.
		if( is_string($query) AND !empty($query) )
		{
			# ¡No contiene SELECT!
			if( !Contains($query, 'SELECT', true) )
				return self::Error('sql.query.novalid');

			# Ejecutamos la consulta y obtenemos su recurso.
			$sql 	= self::query($q);
			$result = ( self::Rows($sql) > 0 ) ? $sql->fetch_assoc() : false;
		}
		else if ( is_resource($query) )
			$result = $query->fetch_assoc();
		else
		{
			# Establecemos el recurso de la última consulta hecha.
			$sql 	= self::$lastResource;
			$result = $sql->fetch_assoc();
		}

		# Liberamos memoria.
		if( self::$freeResult )
			self::Free();

		return $result;
	}

	/**
	 * Obtener los valores de una consulta.
	 * @param string $query Consulta O recurso de la consulta. Si
	 * se deja vacio se usará el recurso de la última consulta hecha.
	 */
	static function Object($query = '')
	{
		# Necesitamos habernos conectado.
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		# La consulta es un array, ejecutamos cada consulta y devolvemos
		# su resultado.
		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::Object($q);

			return $result;
		}

		# $query esta vacia y no se ha hecho ninguna consulta antes.
		if( empty($query) AND !self::FirstQuery() )
			return self::Error('sql.query.need');

		# Estamos realizando la consulta.
		if( is_string($query) AND !empty($query) )
		{
			# ¡No contiene SELECT!
			if( !Contains($query, 'SELECT', true) )
				return self::Error('sql.query.novalid');

			# Ejecutamos la consulta y obtenemos su recurso.
			$sql 	= self::query($q);
			$result = ( self::Rows($sql) > 0 ) ? $sql->fetch_object() : false;
		}
		else
		{
			# Establecemos el recurso de la última consulta hecha.
			$sql 	= self::$lastResource;
			$result = $sql->fetch_object();
		}

		# Liberamos memoria.
		if( self::$freeResult )
			self::Free();

		return $result;
	}

	/**
	 * Obtener los valores de una consulta.
	 * @param string $query Consulta O recurso de la consulta. Si
	 * se deja vacio se usará el recurso de la última consulta hecha.
	 */
	static function GetArray($query = '')
	{
		# Necesitamos habernos conectado.
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		# La consulta es un array, ejecutamos cada consulta y devolvemos
		# su resultado.
		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::GetArray($q);

			return $result;
		}

		# $query esta vacia y no se ha hecho ninguna consulta antes.
		if( empty($query) AND !self::FirstQuery() )
			return self::Error('sql.query.need');

		# Estamos realizando la consulta.
		if( is_string($query) AND !empty($query) )
		{
			# ¡No contiene SELECT!
			if( !Contains($query, 'SELECT', true) )
				return self::Error('sql.query.novalid');

			# Ejecutamos la consulta y obtenemos su recurso.
			$sql 	= self::query($q);
			$result = ( self::Rows($sql) > 0 ) ? $sql->fetch_array() : false;
		}
		else
		{
			# Establecemos el recurso de la última consulta hecha.
			$sql 	= self::$lastResource;
			$result = $sql->fetch_array();
		}

		# Liberamos memoria.
		if( self::$freeResult )
			self::Free();

		return $result;
	}

	/**
	 * Obtener los valores de una consulta.
	 * @param string $query Consulta O recurso de la consulta. Si
	 * se deja vacio se usará el recurso de la última consulta hecha.
	 */
	static function Row($query = '')
	{
		# Necesitamos habernos conectado.
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		# La consulta es un array, ejecutamos cada consulta y devolvemos
		# su resultado.
		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::Row($q);

			return $result;
		}

		# $query esta vacia y no se ha hecho ninguna consulta antes.
		if( empty($query) AND !self::FirstQuery() )
			return self::Error('sql.query.need');

		# Estamos realizando la consulta.
		if( is_string($query) AND !empty($query) )
		{
			# ¡No contiene SELECT!
			if( !Contains($query, 'SELECT', true) )
				return self::Error('sql.query.novalid');

			# Ejecutamos la consulta y obtenemos su recurso.
			$sql 	= self::query($q);
			$result = ( self::Rows($sql) > 0 ) ? $sql->fetch_row() : false;
		}
		else
		{
			# Establecemos el recurso de la última consulta hecha.
			$sql 	= self::$lastResource;
			$result = $sql->fetch_row();
		}

		# Liberamos memoria.
		if( self::$freeResult )
			self::Free();

		return $result;
	}

	/**
	 * Obtener un valor especifico de una consulta.
	 * @param string $query Consulta.
	 * @return string Valor o array con los valores.
	 */
	static function Get($query)
	{
		# Necesitamos habernos conectado.
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		# La consulta es un array, ejecutamos cada consulta y devolvemos
		# su resultado.
		if( is_array($query) )
		{
			foreach( $query as $q )
				$result[] = self::Get($q);

			return $result;
		}

		# Obtenemos el valor (o valores) a obtener.
		preg_match("/SELECT ([^<]+) FROM/is", $query, $params);

		# La consulta no contiene SELECT
		# no se encontro ningún valor
		# el valor es * o null
		if( !Contains($query, 'SELECT', true) OR empty($params[1]) OR $params[1] == '*' OR strtolower($params[1]) == 'null' )
			return self::Error('sql.query.novalid');

		# En caso de haber varios valores estos se separan por comas.
		$pp 	= explode(',', $params[1]);
		# Ejecutamos la consulta.
		$row 	= self::Assoc($query);
		# Resultado predeterminado.
		$result = false;

		# La consulta fallo.
		if( !$row )
			return false;

		# Hay más de un valor, devolvemos el resultado en un array.
		if( count($pp) > 1 )
		{
			foreach( $pp as $param )
				$result[$param] = $row[$param];
		}
		else
			$result = $row[$params[1]];

		# Liberamos memoria.
		if( self::$freeResult )
			self::Free();

		return $result;
	}

	/**
	 * Insertar datos en una tabla.
	 * @param string $table Tabla
	 * @param array $data  	Datos
	 * @return resource 	Recurso de la consulta.
	 */
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

	/**
	 * Actualizar los datos de una tabla.
	 * @param string  $table   	Tabla
	 * @param array  $updates 	Datos a actualizar.
	 * @param array  $where   	Condiciones a cumplir.
	 * @param integer $limit   	Limite de columnas a actualizar.
	 * @return resource 		Recurso de la consulta.
	 */
	static function Update($table, $updates, $where = '', $limit = 1)
	{
		# Necesitamos habernos conectado.
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		# Los datos a actualizar no son un array.
		if( !is_array($updates) )
			return false;

		# Construimos la consulta.

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

	/**
	 * Obtener toda la información de una consulta.
	 * @param string $query Consulta.
	 */
	static function Data($query)
	{
		# Necesitamos habernos conectado.
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		# Ejecutamos la consulta.
		$sql = self::query($query);

		# Fallamos.
		if( !$sql )
			return false;

		# Devolvemos un array con la información posible.
		$result = array(
			'resource' 	=> $sql,
			'assoc' 	=> $sql->fetch_assoc(),
			'rows' 		=> $sql->num_rows
		);

		# Liberamos memoria.
		if( self::$freeResult )
			self::Free();

		return $result;
	}

	/**
	 * Obtener las filas que han sido afectadas en la última consulta.
	 */
	static function Affected()
	{
		# Necesitamos habernos conectado.
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		return self::$server->affected_rows;
	}

	/**
	 * Obtener la última ID insertada en la base de datos.
	 */
	static function LastID()
	{
		# Necesitamos habernos conectado.
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		return self::$server->insert_id;
	}

	/**
	 * Aplicar filtro anti SQL Inyection a una cadena.
	 * @param string $str Cadena
	 */
	static function Escape($str)
	{
		# Necesitamos habernos conectado.
		if( !self::Connected() )
			return self::Error('sql.need.connection');

		return self::$server->escape_string($str);
	}

	/**
	 * Libera la memoria de la última consulta realizada.
	 * @param resource $query Recurso de la última consulta.
	 */
	static function Free($query = '')
	{
		# $query esta vacia y no se ha hecho ninguna consulta antes.
		if( empty($query) AND !self::FirstQuery() )
			return self::Error('sql.query.need');

		# Establecemos el recurso de la última consulta hecha.
		if( empty($query) )
			$query = self::$lastResource;

		# Devolvemos la variable $freeResult a false
		if( self::$freeResult )
			self::$freeResult = false;

		return $query->free();
	}

	/**
	 * Obtener un valor especifico de un recurso o la última consulta hecha.
	 * @param string $row   Valor
	 * @param string $query Consulta O recurso de la consulta. Si
	 * se deja vacio se usará el recurso de la última consulta hecha.
	 * @return string Valor
	 */
	static function GetData($row, $query = '')
	{
		# $query esta vacia y no se ha hecho ninguna consulta antes.
		if( empty($query) AND !self::FirstQuery() )
			return self::Error('sql.query.need');

		# Establecemos el recurso de la última consulta hecha.
		if( empty($query) )
			$query = self::$lastResource;

		$result = self::Assoc($query);
		return $result[$row];
	}

	/**
	 * Cambia el motor de las tablas especificadas.
	 * @param string $engine Nuevo motor (MYISAM o INNODB)
	 * @param string $tables Tablas afectadas.
	 */
	static function Engine($engine = 'MYISAM', $tables = '')
	{
		# Por ahora solo MYISAM y INNODB son aceptadas.
		if( $engine !== 'MYISAM' AND $engine !== 'INNODB' )
			return self::Error('sql.engine');

		# Afectar a todas las tablas.
		if( empty($tables) )
		{
			$query = self::query('SHOW TABLES');

			while( $tmp = self::GetArray($query) )
				self::query("ALTER TABLE $tmp[0] ENGINE = $engine");
		}

		# Aplicamos los cambios a las tablas especificadas.
		else if( is_array($tables) )
		{
			foreach( $tables as $t )
				self::query("ALTER TABLE $t ENGINE = $engine");
		}

		Reg("%engine_correct% $engine");
	}

	/**
	 * Optimiza las tablas especificadas.
	 * @param array $tables Tablas afectadas.
	 */
	static function Optimize($tables = '')
	{
		# Afectar a todas las tablas.
		if( empty($tables) )
		{
			$query = self::query('SHOW TABLES');

			while( $tmp = self::GetArray($query) )
				self::query("OPTIMIZE TABLE $tmp[0]");
		}

		# Aplicamos los cambios a las tablas especificadas.
		else if( is_array($tables) )
		{
			foreach( $tables as $t )
				self::query("OPTIMIZE TABLE $t");
		}

		Reg('%optimize_correct%');
	}

	/**
	 *  Reparar las tablas especificadas.
	 * @param array $tables Tablas afectadas.
	 */
	static function Repair($tables = '')
	{
		# Afectar a todas las tablas.
		if( empty($tables) )
		{
			$query = self::query('SHOW TABLES');

			while( $tmp = self::GetArray($query) )
				self::query("REPAIR TABLE $tmp[0]");
		}

		# Aplicamos los cambios a las tablas especificadas.
		else if( is_array($tables) )
		{
			foreach( $tables as $t )
				self::query("REPAIR TABLE $t");
		}

		Reg('%repair.correct%');
	}

	/**
	 * Examina las tablas y ordena la información.
	 * Usado para la administración inteligente.
	 * @return Información.
	 */
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

		# La recuperación inteligente esta desactivada o
		# no hay copia de seguridad existente.
		if( !$config['server']['backup'] OR empty($backup) )
			self::Error('sql.recovery', '%backup.disable%');

		# Paso 1
		if( $type == 1 )
		{
			# Creamos la base de datos.
			self::$server->query("CREATE DATABASE IF NOT EXISTS $dbname");
			# Selccionamos la base de datos creada.
			self::$server->select_db($dbname) or self::Error('sql.recovery', '%error.db%');

			Reg('%backup.createdb%');
			# Ahora el segundo paso.
			self::Recover($dbname, 2);
		}

		# Paso 2
		else
		{
			# Separamos cada línea del backup como una consulta.
			$backup = explode(";\n", $backup);

			foreach( $backup as $query )
			{
				# Eliminamos espacios en blanco.
				$query 	= trim($query);

				# ¿Esta vacia?
				if( empty($query) )
					continue;

				# Ejecutamos la consulta.
				$result = self::$server->query($query) or self::Error('sql.recovery', '%error.backup.query% ' . $query);
			}

			Reg('%backup.correct%');
		}

		# Enviamos correo electrónico de que pudimos recuperar la DB.
		Email::SendWarn('recover');
	}

	/**
	 * Crear un backup de la base de datos.
	 * @param array  $tables 	Tablas a recuperar
	 * @param boolean $out    	Retornar la copia en texto plano, de otra manera retornar el nombre del archivo.2
	 * @return string El nombre del backup o el texto del SQL.
	 */
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