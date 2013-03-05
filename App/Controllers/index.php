<?
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart © 2013 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

# Acción ilegal
if( !defined('BEATROCK') )
	exit;

class Ctrl_index extends BaseController
{
	static function index()
	{
		global $G;

		$hello 			= 'Hola querido mundo';
		$hell['test']	= 'Esto es una prueba';

		self::$id				= 'index';
		self::$name 			= 'Inicio';
		self::$lang 			= ( !empty($G['lang']) ) ? $G['lang'] : LANG;
		self::$lang_sections 	= array('page.welcome');
	}

	static function lol($lol)
	{
		self::$id = 'index';
		var_dump( $lol );
	}
}
?>