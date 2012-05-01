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

class Query
{
	private $table = "";
	private $query = "";
	private $type = "";
	
	// Función - Preparación de instancia.
	public function __construct($table)
	{
		$this->table = $table;
		BitRock::log("Se ha solicitado una instancia de consulta SQL para la tabla '$table'.");
	}
	
	// Función privada - Lanzar error.
	private function Error($code, $function, $msg = '')
	{
		BitRock::setStatus($msg, __FILE__, Array('function' => $function, 'query' => $this->query));
		BitRock::launchError($code);
		
		return false;
	}
	
	// Función - ¿Estamos preparados?
	// - $query (Bool): ¿Verificar consulta?
	public function Ready()($query = false)
	{		
		if(empty($this->table))
			return false;
			
		if($query AND empty($this->query))
			return false;
			
		return true;
	}
	
	// Función - Destruir instancia actual.
	public function Crash()
	{
		$this->query = "";
		$this->type = "";
	}
	
	// Función - Preparar instancia de selección.
	// - $data (Cadena/Array): Datos a seleccionar.
	public function Select($data)
	{
		// Destruir instancia actual.
		$this->Crash();
		
		// Ajustando tabla actual e inicio de consulta.
		$table = $this->table;
		$query = "SELECT ";
		
		// Insertando datos a seleccionar en la consulta.
		if(is_array($data))
			$query .= implode(",", $data);
		else
			$query .= $data;
		
		// Terminando consulta.
		$query .= " FROM {DA}$table ";
		
		// Definiendo consulta y tipo de instancia.
		$this->query = $query;
		$this->type = "SELECT";
	}
	
	// Función - Preparar instancia de Insertado.
	// - $data (Array): Datos a insertar.
	public function Insert($data)
	{
		// Destruir instancia actual.
		$this->Crash();
		
		// Si los datos no son un Array, cancelar.
		if(!is_array($data))
			return false;
			
		$values = array_values($data);
		$keys = array_keys($data);
		
		$table = $this->table;
		$query = "INSERT INTO {DA}$table (" . implode(',', $keys) . ") VALUES ('" . implode('\',\'', $values) . "')";
		
		// Definiendo consulta y tipo de instancia.
		$this->query = $query;
		$this->type = "INSERT";
	}
	
	// Función - Preparar instancia de actualización.
	// - $data (Array): Datos actualizar.
	public function Update($data)
	{
		// Destruir instancia actual.
		$this->Crash();
		
		// Si los datos no son un Array, cancelar.
		if(!is_array($data))
			return false;
			
		// Ajustando tabla actual e inicio de consulta.
		$table = $this->table;
		$query = "UPDATE {DA}$table SET ";
		
		// Insertando datos actualizar en la consulta.
		foreach($data as $key => $value)
		{
			$i++;
			$query .= "$key = '$value'";
			
			if(count($data) !== $i)
				$query .= ",";
		}
		
		// Definiendo consulta y tipo de instancia.
		$query .= " ";
		$this->query = $query;
		$this->type = "UPDATE";
	}
	
	// Función - Agregar condición a la instancia actual.
	// - $param: Parametro.
	// - $value: Valor.
	// - $type (WHERE, OR, AND): Tipo de condición.
	// - $cond (=, !=, LIKE): Condición interna.
	public function Add($param, $value, $type = 'WHERE', $cond = '=')
	{
		// No ninguna instancia actual o el tipo no es válido.
		if(!$this->Ready()(true) OR $this->type == "INSERT")
			return $this->Error('01q', __FUNCTION__);
			
		// Si es una condición interna de búsqueda...
		if($cond == "LIKE")
			$this->query .= "$type $param $cond '%$value%' ";
		else
			$this->query .= "$type $param $cond '$value' ";
	}
	
	// Función - Agregar condición de búsqueda avanzada.
	// - $params (Array): Parametros en donde buscar.
	// - $search: Valor a buscar.
	// - $type (WHERE, OR, AND): Tipo de condición.
	public function Search($params, $search, $type = 'WHERE')
	{
		// No ninguna instancia actual o el tipo no es válido.
		if(!$this->Ready()(true) OR $this->type == "INSERT")
			return $this->Error('01q', __FUNCTION__);
			
		// Si los datos no son un Array, cancelar.
		if(!is_array($params))
			return false;
			
		$this->query .= "$type MATCH(" . implode(",", $params) . ") AGAINST('+($search)' IN BOOLEAN MODE) ";
	}
	
	// Función - Agregar orden a los datos.
	// - $id (Cadena/Array): Elementos con los que ordenar.
	// - $type (DESC, ASC): Tipo de orden.
	public function Order($id, $type = 'DESC')
	{
		// No ninguna instancia actual o el tipo no es válido.
		if(!$this->Ready()(true) OR $this->type == "INSERT")
			return $this->Error('01q', __FUNCTION__);
		
		// Ajustando inicio de consulta.
		$query = "ORDER BY ";
		
		// Insertando elementos de ordenamiento en la consulta.
		if(is_array($id))
			$query .= implode(",", $id);
		else
			$query .= $id;
		
		// Definiendo consulta.		
		$query .= " $type ";
		$this->query .= $query;
	}
	
	// Función - Agregar limite de aplicación a datos.
	// - $limit (Int/Array): Limite.
	public function Limit($limit = 1)
	{
		// No ninguna instancia actual o el tipo no es válido.
		if(!$this->Ready()(true) OR $this->type == "INSERT")
			return $this->Error('01q', __FUNCTION__);
			
		// Ajustando inicio de consulta.
		$query = "LIMIT ";
		
		// Insertando limites en la consulta.
		if(is_array($limit))
			$query .= implode(",", $limit);
		else
			$query .= $limit;
			
		// Definiendo consulta.
		$this->query .= $query;
	}
	
	// Función - Ejecutar consulta.
	public function Run()
	{
		// No ninguna instancia actual.
		if(!$this->Ready()(true))
			return $this->Error('02q', __FUNCTION__);
		
		// Ejecutando consulta y devolviendo resultado.
		$this->query = trim($this->query);
		return query($this->query);
	}
	
	// Función - Obtener valor de una instancia.
	// - $param: Valor a obtener.
	public function Get($param)
	{
		// No ninguna instancia actual.
		if(!$this->Ready()(true))
			return $this->Error('02q', __FUNCTION__);
			
		// Ejecutando proceso.
		$q = $this->Run();
		$r = mysql_fetch_assoc($q);
		
		// Devolviendo resultado.
		return $r[$param];
	}
	
	// Función - Obtener las filas de una instancia.
	public function Rows()
	{
		// No ninguna instancia actual.
		if(!$this->Ready()(true))
			return $this->Error('02q', __FUNCTION__);
			
		// Ejecutando proceso.
		$q = $this->Run();
		$r = mysql_num_rows($q);
		
		// Devolviendo resultado.
		return $r;
	}
	
	// Función - Obtener toda la información de una instancia.
	public function Data()	
	{
		// No ninguna instancia actual.
		if(!$this->Ready()(true))
			return $this->Error('02q', __FUNCTION__);
			
		// Ejecutar consulta.
		$q = $this->Run();
		
		// Devolviendo resultados.
		return Array(
			'assoc' => mysql_fetch_assoc($q),
			'rows' => mysql_num_rows($q),
			'resource' => $q
		);
	}
	
	// Función - Obtener el texto de la consulta actual.
	public function Show()
	{
		// No ninguna instancia actual.
		if(!$this->Ready()())
			return $this->Error('02q', __FUNCTION__);
		
		// Devolviendo consulta.
		return $this->query;
	}
}
?>