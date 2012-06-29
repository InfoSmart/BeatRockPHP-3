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

class Captcha
{
	// Mostrar un reCaptcha.
	// - $key: Clave pública.
	static function Show($key = '6Ldow8ISAAAAAAtIlIoKPe-qBlizmygWH2ASb4Pv')
	{			
		echo "<script src=\"//www.google.com/recaptcha/api/challenge?k=$key\"></script>";
	}
	
	// Verificar si es correcta la respuesta reCaptcha.
	// - $key: Clave privada.
	static function Verify($key = '6Ldow8ISAAAAAGAev8nhH4hEgpxY2WDflwRiVpnw')
	{
		require_once MODS . 'External' . DS . 'recaptchalib.php';
		
		$r = recaptcha_check_answer($key, IP, $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
		return $r->is_valid;
	}
}
?>