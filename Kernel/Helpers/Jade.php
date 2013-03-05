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

// Acción ilegal.
if(!defined('BEATROCK'))
	exit;

class Jade
{
	// Lanzar error.
	// - $code: Código del error.
	// - $function: Función causante.
	// - $message: Mensaje del error.
	static function Error($code, $function, $message = '')
	{		
		Lang::SetSection('mod.jade');
		
		Bit::Status($message, __FILE__, array('function' => $function));
		Bit::LaunchError($code);
		
		return false;
	}

	static function Enabled()
	{
		global $site;
		return ($site['site_jade'] !== 'true' OR empty($site['site_jade_path'])) ? false : true;
	}

	static function Render($html, $data = array())
	{
		if(!self::Enabled())
			return $html;

		if(!function_exists('shell_exec'))
			self::Error('jade.need', __FUNCTION__);

		global $site;

		$source = Io::SaveTemporal($html, '.jade');
		$output = $source . '.html';

		if(!file_exists($source))
			self::Error('jade.notemporal', __FUNCTION__);

		$check 	= basename($site['site_jade_path']);

		if($check !== 'jade')
			self::Error('jade.invalid.compiler', __FUNCTION__);

		$compile 	= shell_exec("$site[site_jade_path] -P < \"$source\" > \"$output\" 2>&1");
		$result 	= file_get_contents($output);

		if(!$result OR Contains($result, 'Error') AND Contains($result, 'Object.Parser'))
			self::Error('jade.compile.error', __FUNCTION__, $result);

		return trim($result);
	}
}
?>