<?
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

class Steam
{
	static $api 	= null;

	// Obtener la información pública de un usuario.
	// - $id: Nombre de usuario o ID.
	static function GetInfo()
	{

	}

	// Preparar e implementar la API.
	static function Init()
	{
		if(self::$api !== null)
			return true;

		require MODS . 'External' . DS . 'steam' . DS . 'SteamLogin.class.php';
		require MODS . 'External' . DS . 'steam' . DS . 'SteamAPI.class.php';
	}
}
?>