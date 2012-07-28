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

class Io
{
	static $temp = array();
	
	// Lanzar error.
	// - $code: Código del error.
	// - $message: Mensaje del error.
	// - $function: Función causante.
	// - $file: Archivo causante.
	static function Error($code, $message, $function, $file = '')
	{
		Lang::SetSection('mod.io');

		BitRock::SetStatus($message, __FILE__, array('function' => $function, 'out_file' => $file));
		BitRock::LaunchError($code);
	}
	
	// Escribir datos en archivo.
	// - $file: Ruta del archivo.
	// - $data: Datos/Bits/Url a escribir.
	static function Write($file, $data)
	{
		if(Core::isValid($data, 'url'))
			$data = self::Read($data);
			
		$r = file_put_contents($file, $data) or self::Error('io.fail', '%error.write%', __FUNCTION__, $file);;			
			
		Reg('%write.correct%');
		return $r;
	}
	
	// Leer archivo.
	// - $file: Ruta/Dirección web del archivo.
	// - $encode (base64, md5): Codificar los datos leidos.
	// - $params (array): Datos para una conexión stream_context
	static function Read($file, $encode = '', $params = array())
	{
		$r = empty($params) ? file_get_contents($file) : file_get_contents($file, false, stream_context_create($params));
		
		if($r == false)
			self::Error('io.fail', '%error.read%', __FUNCTION__, $file);
			
		if($encode == 'base64')
			$r = base64_encode($r);
		if($encode == 'md5')
			$r = md5($r);
			
		Reg('%read.correct%');
		return $r;		
	}
	
	// Eliminar archivo.
	// - $file: Ruta del archivo.
	static function Delete($file)
	{
		$r = unlink($file) or self::Error('io.fail', '%error.delete%', __FUNCTION__, $file);

		Reg('%delete.correct%');
		return $r;
	}
	
	// Copiar archivo.
	// - $file: Ruta/Dirección web del archivo.
	// - $to: Ruta del archivo de destino.
	// - $params (Array): Datos para una conexión stream_context
	static function Copy($file, $to, $params = array())
	{
		$r = empty($params) ? copy($file, $to) : copy($file, $to, stream_context_create($params));
		
		if($r == false)
			self::Error('io.fail', '%error.copy%', __FUNCTION__, $file);
		
		Reg('%copy.correct%');
		return $r;
	}
	
	// Mover/Renombrar archivo.
	// - $file: Ruta del archivo.
	// - $to: Ruta del archivo de destino.
	static function Move($file, $to)
	{
		$r = rename($file, $to) or self::Error('io.fail', '%error.move%', __FUNCTION__, $file);
		
		Reg('%move.correct%');
		return $r;
	}
	
	// Vaciar directorio.
	// - $dir: Ruta del directorio.
	// - $del (Bool): ¿Eliminar directorio también?
	static function EmptyDir($dir, $del = false)
	{
		if(is_array($dir))
		{
			foreach($dir as $d)
				self::EmptyDir($d, $del);
				
			return true;
		}
		
		if(!is_dir($dir))
			return false;
		
		$files = glob($dir . '/*');

		foreach($files as $file)
		{
			if(is_dir($file))
			{
				self::EmptyDir($file);
				rmdir($file);
			}
			else if(is_file($file))
				self::Delete($file);
		}
		
		if($del)
			rmdir($dir);
		
		Reg('%empty.correct%');
		return true;
	}
	
	// Obtener los archivos/directorios de un directorio.
	// - $dir: Ruta del directorio.
	// - $subdir (Bool): ¿Combinar los archivos de los subdirectorios en el resultado?
	static function GetDir($dir, $subdir = false)
	{
		if(!is_dir($dir))
			return false;
			
		$result = array();
		$files 	= glob($dir . '/*');

		foreach($files as $file)
		{
			if(is_dir($file))
			{
				if($subdir)
					$result .= self::GetDir($dir);
				else
					$result[] = str_replace($dir, '', $file);
			}
			else if(is_file($file))
				$result[] = str_replace($dir . '/', '', $file);
		}
		
		return $result;
		Reg('%getfiles.correct%');
	}

	static function GetDirs($dir)
	{
		if(!is_dir($dir))
			return false;

		$result = array();
		$dirs 	= glob($dir . '/*');

		foreach($dirs as $d)
		{
			if(!is_dir($d))
				continue;

			$result[] = str_ireplace($dir . '/', '', $d);
		}

		return $result;
	}
	
	// Guardar un backup.
	// - $name: Nombre del archivo.
	// - $data: Datos/Bits del archivo.
	static function SaveBackup($name, $data)
	{
		self::Write(BIT . 'Backups' . DS . 'LAST', $name);
		return self::Write(BIT . 'Backups' . DS . $name, $data);
	}
	
	// Guardar un log.
	// - $name: Nombre del archivo.
	// - $data: Datos/Bits del archivo.
	static function SaveLog($name, $data)
	{		
		return self::Write(BIT . 'Logs' . DS . $name, $data);
	}
	
	// Guardar un archivo temporal.
	// - $data: Datos/Bits del archivo.
	static function SaveTemporal($data)
	{
		$name = BIT . 'Temp' . DS . Core::Random(20);
		self::Write($name, $data);
		
		self::$temp[] = $name;
		return $name;
	}
	
	// Obtener el tamaño del directorio en Bytes.
	// - $dir: Ruta del directorio.
	static function SizeDir($dir)
	{
		if(!is_dir($dir))
			return 0;
		
		$size = 0;
		$files = glob($dir . '/*');

		foreach($files as $file)
			$size = is_dir($file) ? $size + self::SizeDir($file) : $size + filesize($file);
		
		return $size;
	}
	
	// Transformar la cantidad especificada en un tamaño informatico.
	// - $b (Int): Cantiidad.
	// - $p (Int): Tamaño a transformar. (1 = Bytes, 2 = Kilobytes, 3 = MegaBytes, 4 = GigaBytes, 5 = TeraBytes, 6 = PetaBytes, 7 = ExaBytes, 8 = ZettaBytes, 9 = Yottabytes)
	// - $decimals (Int): Decimales máximo.
	static function Bytes($b, $p = 2, $decimals = 2)
	{
		$units 	= array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
		$c 		= 0;
		
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