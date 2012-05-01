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

class Captcha
{
	// Función - Mostrar un reCaptcha.
	// - $key: Clave pública.
	public static function Show($key = '')
	{
		if(empty($key))
			$key = "6Ldow8ISAAAAAAtIlIoKPe-qBlizmygWH2ASb4Pv";
			
		echo "<script src=\"//www.google.com/recaptcha/api/challenge?k=$key\"></script>";
		BitRock::log("Se ha mostrado un código de seguridad (Captcha).");
	}
	
	// Función - Verificar si es correcta la respuesta reCaptcha.
	// - $key: Clave privada.
	public static function Verify($key = '')
	{
		require_once(MODS . 'External' . DS . 'recaptchalib.php');
		
		if(empty($key))
			$key = "6Ldow8ISAAAAAGAev8nhH4hEgpxY2WDflwRiVpnw";
		
		$r = recaptcha_check_answer($key, IP, $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
		return $r->is_valid;
	}
}
?>