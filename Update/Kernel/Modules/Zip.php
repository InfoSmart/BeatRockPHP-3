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
	// Extraer un archivo ZIP.
	// - $file: Ruta del Archivo ZIP.
	// - $folder: Ruta del folder donde extraer los archivos.
	static function Extract($file, $folder)
	{
		Lang::SetSection('mod.zip');

		$a = new PclZip($file);
		$e = $a->extract(PCLZIP_OPT_PATH, $folder);
		
		if($e == 0)
		{
			Reg('%error.extract%' . $file, 'error');
			return false;
		}
			
		Reg('%extract.correct%' . $file);
		return true;
	}
	
	// Crear un archivo ZIP.
	// - $file: Ruta del nuevo archivo ZIP.
	// - $files (Array): Archivos a comprimir.
	static function Create($file, $files = Array())
	{
		Lang::SetSection('mod.zip');

		$a = new PclZip($file);
		$e = $a->create($files, PCLZIP_OPT_REMOVE_ALL_PATH);
		
		if($e == 0)
		{
			Reg('%error.create%' . $file, 'error');
			return false;
		}
		
		Reg('%create.correct%' . $file);
		return true;			
	}
	
	// Agregar un archivo a un archivo ZIP.
	// - $file: Ruta del archivo ZIP.
	// - $files (Array): Archivos a agregar.
	static function Add($file, $files)
	{
		Lang::SetSection('mod.zip');
					
		$a = new PclZip($file);
		$e = $a->add($files, PCLZIP_OPT_REMOVE_ALL_PATH);
		
		if($e == 0)
		{
			Reg('%error.add%' . $file, 'error');
			return false;
		}
		
		Reg('%add.correct%' . $file);
		return true;	
	}
}
?>