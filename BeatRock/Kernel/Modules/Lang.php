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

class Lang
{
	static $lang 	= '';
	static $params 	= array();
	static $section;

	// Lanzar error.
	// - $code: Código del error.
	// - $function: Función causante.
	// - $message: Mensaje del error.
	static function Error($code, $function, $message = '')
	{
		BitRock::SetStatus($message, __FILE__, array('function' => $function));
		BitRock::LaunchError($code);

		return false;
	}

	// Destruir parametros actuales.
	static function Crash()
	{
		self::$params = array();
	}

	// Cargar un lenguaje.
	// - $lang: Lenguaje.
	static function Init($lang = '')
	{
		if(empty($lang))
			$lang = LANG;

		if(self::$lang == $lang)
			return;

		if(!empty(self::$lang))
			self::Crash();

		$folder = LANGUAGES . $lang;

		if(!is_dir($folder))
			$folder = LANGUAGES . 'es';

		$files = Io::GetDir($folder);

		foreach($files as $file)
		{
			if($file == 'Codes.json' OR !Contains($file, 'json'))
				continue;

			$data 			= Core::LoadJSON($folder . DS . $file);
			self::$params 	= array_merge($data, self::$params);
		}

		self::$lang = $lang;
	}

	// Establecer una sección de lenguaje.
	// - $section: Nombre/Array de la sección.
	static function SetSection($section)
	{
		self::$section = $section;
	}

	// Obtener la traducción de un parametro.
	// - $section: Nombre de la sección.
	static function GetParam($param, $section = '')
	{
		if(empty($section))
			$section = self::$section;

		return self::$params[$section][$param];
	}

	// Traducir una cadena.
	// - $data: Cadena.
	// - $section: Nombre de la sección a utilizar.
	static function SetParams($data, $section = '')
	{
		if(empty($section))
			$section = self::$section;

		if(is_array($section))
		{
			foreach($section as $sec)
			{
				if(empty(self::$params[$sec]))
					continue;

				foreach(self::$params[$sec] as $param => $value)
				{
					$value 	= utf8_decode($value);
					$value 	= ShortCuts($value);

					$data 	= str_ireplace('%' . $param . '%', $value, $data);
				}
			}
		}
		else
		{
			if(empty(self::$params[$section]))
				return $data;

			foreach(self::$params[$section] as $param => $value)
				$data = str_ireplace('%' . $param . '%', utf8_decode($value), $data);
		}

		return $data;
	}
}
?>