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
	
class Zip
{
	// Función - Extraer un archivo ZIP.
	// - $file: Ruta del Archivo ZIP.
	// - $folder: Ruta del folder donde extraer los archivos.
	public static function Extract($file, $folder)
	{
		$a = new PclZip($file);
		$e = $a->extract(PCLZIP_OPT_PATH, $folder);
		
		if($e == 0)
		{
			BitRock::log("No se ha podido extraer el archivo ZIP '$file'.", "error");
			return false;
		}
			
		BitRock::log("Se ha extraido el archivo ZIP '$file' en la carpeta '$folder'.");
		return true;
	}
	
	// Función - Crear un archivo ZIP.
	// - $file: Ruta del nuevo archivo ZIP.
	// - $files (Array): Archivos a comprimir.
	public static function Create($file, $files = Array())
	{			
		$a = new PclZip($file);
		$e = $a->create($files, PCLZIP_OPT_REMOVE_ALL_PATH);
		
		if($e == 0)
		{
			BitRock::log("No se ha podido crear el archivo ZIP '$file'.", "error");
			return false;
		}
		
		BitRock::log("Se ha creado el archivo ZIP '$file' correctamente.");
		return true;			
	}
	
	// FUnción - Agregar un archivo a un archivo ZIP.
	// - $file: Ruta del archivo ZIP.
	// - $files (Array): Archivos a agregar.
	public static function Add($file, $files)
	{			
		$a = new PclZip($file);
		$e = $a->add($files, PCLZIP_OPT_REMOVE_ALL_PATH);
		
		if($e == 0)
		{
			BitRock::log("No se ha podido agregar los archivos especificados en el archivo ZIP '$file'.", "error");
			return false;
		}
		
		BitRock::log("Se han agregado nuevos archivos en el archivo ZIP '$file' correctamente.");
		return true;	
	}
}
?>