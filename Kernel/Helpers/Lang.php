<?
#####################################################
## 					 BeatRock
#####################################################
## Framework avanzado de procesamiento para PHP.
#####################################################
## InfoSmart © 2013 Todos los derechos reservados.
## http://www.infosmart.mx/
#####################################################
## http://beatrock.infosmart.mx/
#####################################################

# Acción ilegal.
if( !defined('BEATROCK') )
	exit;

###############################################################
## Ayudante Lang
###############################################################
## Administra y procesa las traducciones disponibles, puede
## traducir cadenas o vistas HTML.
###############################################################

class Lang
{
	static $lang 		= '';		# Lenguaje que usaremos.
	static $vars 		= array();	# Variables de lenguaje.
	static $langs 		= array();	# Lenguajes disponibles.

	static $section;				# Sección de traducción.

	###############################################################
	## Destruye todas las variables actuales.
	###############################################################
	static function Destroy()
	{
		self::$vars = array();
	}

	###############################################################
	## Constructor
	## Carga todas las variables de traducción.
	###############################################################
	function __construct()
	{
		# Obtenemos todas las carpetas dentro de /Languages/
		self::$langs = Io::GetDirFiles(LANGUAGES);

		foreach( self::$langs as $lang )
		{
			# !!!FIX
			$lang 					= str_replace('/', '', $lang);
			# Obtenemos todos los archivos de traducción de ese lenguaje.
			$files 					= Io::GetDirFiles(LANGUAGES . $lang);
			# Iniciamos la variable que contendrá las variables.
			self::$vars[$lang] 		= array();

			foreach( $files as $file )
			{
				# Codes.json es para los códigos de errores, no guardarlo.
				# El archivo debe tener la extensión json para poder usarlo.
				if( $file == 'Codes.json' OR !Contains($file, 'json') )
					continue;

				# Obtenemos sus variables.
				$data 				= Core::LoadJSON(LANGUAGES . $lang . DS . $file);
				# Guardamos las variables.
				self::$vars[$lang] 	= array_merge($data, self::$vars[$lang]);
			}
		}
	}

	###############################################################
	## Obtener el código JavaScript con las variables
	## de traducción, nos servirá para el sistema de "traducción"
	## en tiempo real.
	###############################################################
	static function GetLiveJS($section = '')
	{
		global $page;

		# No hay sección definida, usar la establecida para la página.
		if( empty($section) )
			$section = $page['lang.sections'];

		# La sección es una matriz de una sola llave.
		if( is_array($section) AND count($section) == 1 )
			$section = $section[0];

		$result = array();
		$vars 	= self::$vars;

		foreach( $vars as $lang => $var )
		{
			# Usaremos más de una sección.
			if( is_array($section) )
			{
				foreach( $section as $sec )
				{
					$value = $vars[$lang][$var];

					# ¿Sin valor?
					if( empty($value) )
						continue;

					# No estamos usando UTF-8, descodificar.
					if( CHARSET !== 'UTF-8' )
						$value 				= Core::UTF8Decode($value);

					$value 					= Keys($value);
					$result[$lang][$sec] 	= $value;
				}
			}
			else
			{
				$value = $vars[$lang][$section];

				# ¿Sin valor?
				if( empty($value) )
					return;

				# No estamos usando UTF-8, descodificar.
				if( CHARSET !== 'UTF-8' )
					$value 		= Core::UTF8Decode($lvalue);

				$value 			= Keys($value);
				$result[$lang] 	= $value;
			}
		}

		return $result;
	}

	###############################################################
	## Establecer la sección actual de traducción.
	## - $section: Nombre/Matriz de la sección.
	###############################################################
	static function SetSection($section)
	{
		self::$section = $section;
	}

	###############################################################
	## Obtener la traducción de una variable.
	## - $param: 	Nombre de la variable.
	## - $section: 	Nombre de la sección.
	###############################################################
	static function GetVar($param, $section = '')
	{
		if( empty($section) )
			$section = self::$section;

		return self::$vars[$section][$param];
	}

	###############################################################
	## Traducir una cadena.
	## - $data: 		Cadena.
	## - $lang: 		Lenguaje a traducir.
	## - $section: 		Nombre de la sección a utilizar.
	## - $live (bool): 	¿Preparado para la traducción en tiempo real?
	###############################################################
	static function Translate($data, $lang = LANG, $section = '', $live = false)
	{
		# No se establecio el lenguaje, usar el del visitante.
		if( empty($lang) )
			$lang = LANG;

		# Al parecer este lenguaje no existe en nuestra base de datos, usar español.
		if( empty(self::$vars[$lang]) )
			$lang = 'es';

		# No hay sección definida, usar la establecida globalmente.
		if( empty($section) )
			$section = self::$section;

		# La sección es una matriz de una sola llave.
		if( is_array($section) AND count($section) == 1 )
			$section = $section[0];

		$vars = self::$vars[$lang];

		# Usaremos más de una sección.
		if( is_array($section) )
		{
			foreach( $section as $sec )
			{
				# Esta sección esta vacia.
				if( empty($vars[$sec]) )
					continue;

				foreach( $vars[$sec] as $param => $value )
				{
					# No estamos usando UTF-8, descodificar.
					if( CHARSET !== 'UTF-8' )
						$value 	= utf8_decode($value);

					# Accesos directos
					$value 	= Keys($value);

					# Traducimos
					if( $live )
						$data 	= str_ireplace('%' . $param . '%', '<span data-lang-param="' . $param . '" data-lang-section="' . $sec . '">' . $value . '</span>', $data);
					else
						$data 	= str_ireplace('%' . $param . '%', $value, $data);
				}
			}
		}
		else
		{
			# Esta sección esta vacia, no hay nada que traducir.
			if( empty($vars[$section]) )
				return $data;

			foreach( $vars[$section] as $param => $value )
			{
				# No estamos usando UTF-8, descodificar.
				if( CHARSET !== 'UTF-8' )
					$value 	= utf8_decode($value);

				$value 	= Keys($value);

				# Traducimos
				if( $live )
					$data = str_ireplace('%' . $param . '%', '<span data-lang-param="' . $param . '">' . $value . '</span>', $data);
				else
					$data = str_ireplace('%' . $param . '%', $value, $data);
			}
		}

		return $data;
	}
}
?>