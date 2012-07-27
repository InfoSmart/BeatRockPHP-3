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
	static $lang 		= '';
	static $params 		= array();

	static $langs 		= array();
	static $all_params 	= array();

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
	static function Init()
	{
		self::$langs = Io::GetDirs(LANGUAGES);

		foreach(self::$langs as $folder)
		{
			$files 					= Io::GetDir(LANGUAGES . $folder);
			self::$params[$folder] 	= array();

			foreach($files as $file)
			{
				if($file == 'Codes.json' OR !Contains($file, 'json'))
					continue;

				$data 					= Core::LoadJSON(LANGUAGES . $folder . DS . $file);
				self::$params[$folder] 	= array_merge($data, self::$params[$folder]);
			}
		}
	}

	static function PrepareLive($section = '')
	{
		global $page;

		if(empty($section))
			$section = $page['lang.sections'];

		if(is_array($section) AND count($section) == 1)
			$section = $section[0];

		$result = array();
		$params = self::$params;

		foreach($params as $lang => $value)
		{
			if(is_array($section))
			{
				foreach($section as $sec)
				{
					if(empty($params[$lang][$sec]))
						continue;

					$result[$lang][$sec] = ShortCuts($params[$lang][$sec]);
				}
			}
			else
			{
				if(empty($params[$lang][$section]))
					return;

				$result[$lang] = ShortCuts($params[$lang][$section]);
			}
		}

		return $result;
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
	static function SetParams($data, $lang = '', $section = '', $tpl = false)
	{
		if(empty($lang))
			$lang = LANG;

		if(empty($section))
			$section = self::$section;

		if(is_array($section) AND count($section) == 1)
			$section = $section[0];

		$params = self::$params[$lang];

		if(is_array($section))
		{
			foreach($section as $sec)
			{
				if(empty($params[$sec]))
					continue;

				foreach($params[$sec] as $param => $value)
				{
					$value 	= utf8_decode($value);
					$value 	= ShortCuts($value);

					if($tpl)
						$data 	= str_ireplace('%' . $param . '%', '<span data-lang-param="' . $param . '" data-lang-section="' . $sec . '">' . $value . '</span>', $data);
					else
						$data 	= str_ireplace('%' . $param . '%', $value, $data);
				}
			}
		}
		else
		{
			if(empty($params[$section]))
				return $data;

			foreach($params[$section] as $param => $value)
			{
				$value = utf8_decode($value);
				$value = ShortCuts($value);

				if($tpl)
					$data = str_ireplace('%' . $param . '%', '<span data-lang-param="' . $param . '">' . $value . '</span>', $data);
				else
					$data = str_ireplace('%' . $param . '%', $value, $data);
			}
		}

		return $data;
	}
}
?>