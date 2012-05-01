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

class Io
{
	public static $temp = Array();
	
	// Función privada - Lanzar error.
	// - $msg: Mensaje del error.
	// - $function: Función causante.
	// - $file: Archivo causante.
	private static function Error($code, $msg, $function, $file = '')
	{
		BitRock::setStatus($msg, __FILE__, Array('function' => $function, 'out_file' => $file));
		BitRock::launchError($code);
	}
	
	// Función - Escribir archivo.
	// - $file: Ruta del archivo.
	// - $data: Datos/Bits a escribir.
	public static function Write($file, $data)
	{
		if(Core::isValid($data, "url"))
			$data = self::Read($data);
			
		// Escribiendo archivo...
		$r = @file_put_contents($file, $data);
		
		// Ha ocurrido un error :(
		if($r == false)
			self::Error('11i', 'No se ha podido escribir el contenido dentro del archivo de salida.', __FUNCTION__, $file);
			
		BitRock::log("Se ha escrito contenido dentro del archivo de salida '$file' correctamente.");
		return $r;
	}
	
	// Función - Leer archivo.
	// - $file: Ruta/Dirección web del archivo.
	// - $encode (base64, md5): Codificar los datos leidos.
	// - $params (Array): Datos para una conexión stream_context
	public static function Read($file, $encode = "", $params = Array())
	{
		// Leeyendo archivo...
		$r = file_get_contents($file, false, stream_context_create($params));
		
		// Ha ocurrido un error :(
		if($r == false)
			self::Error('11i', 'No se ha podido leer el contenido dentro del archivo de entrada.', __FUNCTION__, $file);
			
		if($encode == "base64")
			$r = base64_encode($r);
		if($encode == "md5")
			$r = md5($r);
			
		BitRock::log("Se ha leido el contenido dentro del archivo de entrada '$file' correctamente.");
		return $r;		
	}
	
	// Función - Eliminar archivo.
	// - $file: Ruta del archivo.
	public static function Delete($file)
	{
		// Eliminando archivo...
		$r = @unlink($file);
		
		// Ha ocurrido un error :(
		if($r == false)
			self::Error("11i", "No se ha podido eliminar el archivo '$file'. ", __FUNCTION__, $file);
		
		BitRock::log("Se ha eliminado el archivo '$file' correctamente.");
		return $r;
	}
	
	// Función - Copiar archivo.
	// - $file: Ruta/Dirección web del archivo.
	// - $to: Ruta del archivo de destino.
	// - $params (Array): Datos para una conexión stream_context
	public static function Copy($file, $to, $params = Array())
	{
		// Copiando archivo...
		$r = @copy($file, $to, stream_context_create($params));
		
		// Ha ocurrido un error :(
		if($r == false)
			self::Error("11i", "No se ha podido copiar el archivo '$file'. ", __FUNCTION__, $file);
		
		BitRock::log("Se ha copiado el archivo '$file' a '$to' correctamente.");
		return $r;
	}
	
	// Función - Mover/Renombrar archivo.
	// - $file: Ruta del archivo.
	// - $to: Ruta del archivo de destino.
	public static function Move($file, $to)
	{
		// Moviendo/Renombrando archivo.
		$r = @rename($file, $to);
		
		// Ha ocurrido un error :(
		if($r == false)
			self::Error("11i", "No se ha podido mover/renombrar el archivo '$file'. ", __FUNCTION__, $file);
		
		BitRock::log("Se ha movido/renombrado el archivo '$file' a '$to' correctamente.");
		return $r;
	}
	
	// Función - Vaciar directorio.
	// - $dir: Ruta del directorio.
	// - $del (Bool): ¿Eliminar directorio también?
	public function EmptyDir($dir, $del = false)
	{
		// Al parecer eliminaremos varios directorios.
		if(is_array($dir))
		{
			foreach($dir as $d)
				self::EmptyDir($d);
				
			return true;
		}
		
		// Esto no es un directorio. -.-"
		if(!is_dir($dir))
			return false;
		
		// Viendo los archivos/directorios dentro y eliminandolos...
		foreach(glob($dir . "/*") as $file)
		{
			if(is_dir($file))
			{
				self::EmptyDir($file);
				@rmdir($file);
			}
			else if(is_file($file))
				self::Delete($file);
		}
		
		// Queremos eliminar este directorio también.
		if($del)
			@rmdir($dir);
		
		BitRock::log("Se ha vaciado el directorio '$dir' correctamente.");
		return true;
	}
	
	// Función - Obtener los archivos/directorios de un directorio.
	// - $dir: Ruta del directorio.
	// - $subdir (Bool): ¿Combinar los archivos de los subdirectorios en el resultado?
	public static function GetDir($dir, $subdir = false)
	{
		// Esto no es un directorio. -.-"
		if(!is_dir($dir))
			return false;
			
		$result = Array();
			
		// Viendo los archivos/directorios dentro...
		foreach(glob($dir . "/*") as $file)
		{
			if(is_dir($file))
			{
				if($subdir)
					$result .= self::GetDir($dir);
				else
					$result[] = str_replace($dir, '', $file);
			}
			else if(is_file($file))
				$result[] = str_replace($dir . "/", '', $file);
		}
		
		return $result;
		BitRock::log("Se han obtenido los archivos del directorio '$dir' correctamente.");
	}
	
	// Función - Guardar un backup.
	// - $name: Nombre del archivo.
	// - $data: Datos/Bits del archivo.
	public static function SaveBackup($name, $data)
	{
		self::Write(BIT . 'Backups' . DS . 'LAST', $name);
		return self::Write(BIT . 'Backups' . DS . $name, $data);
	}
	
	// Función - Guardar un log.
	// - $name: Nombre del archivo.
	// - $data: Datos/Bits del archivo.
	public static function SaveLog($name, $data)
	{		
		return self::Write(BIT . 'Logs' . DS . $name, $data);
	}
	
	// Función - Guardar un archivo temporal.
	// - $data: Datos/Bits del archivo.
	public static function SaveTemporal($data)
	{
		$name = BIT . 'Temp' . DS . Core::Random(20);
		self::Write($name, $data);
		
		self::$temp[] = $name;
		return $name;
	}
	
	// Función - Obtener el tamaño del directorio en Bytes.
	// - $dir: Ruta del directorio.
	public static function SizeDir($dir)
	{
		// Esto no es un directorio. -.-"
		if(!is_dir($dir))
			return 0;
		
		// Tamaño inicial.
		$size = 0;
			
		// Viendo todos los archivos/directorios dentro y agregando su peso al tamaño inicial.
		foreach(glob($dir . "/*") as $file)
		{
			if(is_dir($file))
				$size = $size + self::SizeDir($file);
			else if(is_file($file))
				$size = $size + filesize($file);
		}
		
		return $size;
	}
	
	// Función - Transformar la cantidad especificada en un tamaño informatico.
	// - $b (Int): Cantiidad.
	// - $p (Int): Tamaño a transformar. (1 = Bytes, 2 = Kilobytes, 3 = MegaBytes, 4 = GigaBytes, 5 = TeraBytes, 6 = PetaBytes, 7 = ExaBytes, 8 = ZettaBytes, 9 = Yottabytes)
	// - $decimals (Int): Decimales máximo.
	public static function Bytes($b, $p = 2, $decimals = 2)
	{
		$units = array("B","kB","MB","GB","TB","PB","EB","ZB","YB");
		$c = 0;
		
		if(!$p && $p !== 0) 
		{
			foreach($units as $k => $u) 
			{
				if(($b / pow(1024,$k)) >= 1) 
				{
					$r["bytes"] = $b / pow(1024,$k);
					$r["units"] = $u;
					$c++;
				}
			}
			
			return round($r["bytes"], $decimals);
		}
		else
			return round($b / pow(1024,$p), $decimals);
	}
}
?>