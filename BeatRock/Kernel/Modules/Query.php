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
	$table 	= '';
	$query 	= '';
	$type 	= '';
	
	// Preparación de instancia.
	function __construct($table)
	{
		Lang::SetSection('mod.query');

		$this->table = $table;
		Reg('%init%' . $table);
	}
	
	// Lanzar error.
	function Error($code, $function, $msg = '')
	{
		Lang::SetSection('mod.query');

		BitRock::SetStatus($msg, __FILE__, array('function' => $function, 'query' => $this->query));
		BitRock::LaunchError($code);
		
		return false;
	}
	
	// ¿Estamos preparados?
	// - $query (Bool): ¿Verificar consulta?
	function Ready($query = false)
	{		
		if(empty($this->table))
			return false;
			
		if($query AND empty($this->query))
			return false;
			
		return true;
	}
	
	// Destruir instancia actual.
	function Crash()
	{
		$this->query 	= '';
		$this->type 	= '';
	}
	
	// Preparar instancia de selección.
	// - $data (Cadena/Array): Datos a seleccionar.
	function Select($data)
	{
		$this->Crash();
		
		$table = $this->table;
		$query = "SELECT ";
		
		if(is_array($data))
			$query .= implode(",", $data);
		else
			$query .= $data;
		
		$query .= " FROM {DA}$table ";
		
		$this->query 	= $query;
		$this->type 	= 'SELECT';
	}
	
	// Preparar instancia de Insertado.
	// - $data (Array): Datos a insertar.
	function Insert($data)
	{
		$this->Crash();

		if(!is_array($data))
			return false;
			
		$values = array_values($data);
		$keys 	= array_keys($data);
		
		$table = $this->table;
		$query = "INSERT INTO {DA}$table (" . implode(',', $keys) . ") VALUES ('" . implode('\',\'', $values) . "')";
		
		$this->query = $query;
		$this->type = "INSERT";
	}
	
	// Preparar instancia de actualización.
	// - $data (Array): Datos actualizar.
	function Update($data)
	{
		$this->Crash();
		
		if(!is_array($data))
			return false;
			
		$table = $this->table;
		$query = "UPDATE {DA}$table SET ";
		
		foreach($data as $key => $value)
		{
			$i++;
			$query .= "$key = '$value'";
			
			if(count($data) !== $i)
				$query .= ",";
		}
		
		$query 			.= " ";
		$this->query 	= $query;
		$this->type 	= 'UPDATE';
	}
	
	// Agregar condición a la instancia actual.
	// - $param: Parametro.
	// - $value: Valor.
	// - $type (WHERE, OR, AND): Tipo de condición.
	// - $cond (=, !=, LIKE): Condición interna.
	function Add($param, $value, $type = 'WHERE', $cond = '=')
	{
		if(!$this->Ready(true) OR $this->type == 'INSERT')
			return $this->Error('query.prepare', __FUNCTION__);
			
		if($cond == 'LIKE')
			$this->query .= "$type $param $cond '%$value%' ";
		else
			$this->query .= "$type $param $cond '$value' ";
	}
	
	// Agregar condición de búsqueda avanzada.
	// - $params (Array): Parametros en donde buscar.
	// - $search: Valor a buscar.
	// - $type (WHERE, OR, AND): Tipo de condición.
	function Search($params, $search, $type = 'WHERE')
	{
		if(!$this->Ready(true) OR $this->type == 'INSERT')
			return $this->Error('query.prepare', __FUNCTION__);
			
		if(!is_array($params))
			return false;
			
		$this->query .= "$type MATCH(" . implode(",", $params) . ") AGAINST('+($search)' IN BOOLEAN MODE) ";
	}
	
	// Agregar orden a los datos.
	// - $id (Cadena/Array): Elementos con los que ordenar.
	// - $type (DESC, ASC): Tipo de orden.
	function Order($id, $type = 'DESC')
	{
		if(!$this->Ready(true) OR $this->type == 'INSERT')
			return $this->Error('query.prepare', __FUNCTION__);
		
		$query = 'ORDER BY ';
		
		if(is_array($id))
			$query .= implode(',', $id);
		else
			$query .= $id;
		
		$query 			.= " $type ";
		$this->query 	.= $query;
	}
	
	// Agregar limite de aplicación a datos.
	// - $limit (Int/Array): Limite.
	function Limit($limit = 1)
	{
		if(!$this->Ready(true) OR $this->type == 'INSERT')
			return $this->Error('query.prepare', __FUNCTION__);
			
		$query = 'LIMIT ';
		
		if(is_array($limit))
			$query .= implode(",", $limit);
		else
			$query .= $limit;
			
		$this->query .= $query;
	}
	
	// Ejecutar consulta.
	function Run()
	{
		if(!$this->Ready(true))
			return $this->Error('query.exec', __FUNCTION__);
		
		$this->query = trim($this->query);
		return query($this->query);
	}
	
	// Obtener valor de una instancia.
	// - $param: Valor a obtener.
	function Get($param)
	{
		if(!$this->Ready(true))
			return $this->Error('query.exec', __FUNCTION__);
			
		$q = $this->Run();
		$r = fetch_assoc($q);
		
		return $r[$param];
	}
	
	// Obtener las filas de una instancia.
	function Rows()
	{
		if(!$this->Ready(true))
			return $this->Error('query.exec', __FUNCTION__);
			
		$q = $this->Run();
		$r = num_rows($q);
		
		return $r;
	}
	
	// Obtener toda la información de una instancia.
	function Data()	
	{
		if(!$this->Ready(true))
			return $this->Error('query.exec', __FUNCTION__);
			
		$q = $this->Run();
		
		return array(
			'assoc' 	=> fetch_assoc($q),
			'rows' 		=> num_rows($q),
			'resource' 	=> $q
		);
	}
	
	// Obtener el texto de la consulta actual.
	function Show()
	{
		if(!$this->Ready())
			return $this->Error('query.exec', __FUNCTION__);
		
		return $this->query;
	}
}
?>