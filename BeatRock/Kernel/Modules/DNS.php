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

class DNS
{
	// Verificar si un dominio existe.
	// - $domain: Dominio web.
	static function CheckDomain($domain)
	{
		if(!Core::isValid($domain, 'domain'))
			return false;

		if(function_exists('checkdnsrr'))
		{
			if(checkdnsrr($domain . '.', 'MX'))
				return true;

			if(checkdnsrr($domain . '.', 'A'))
				return true;
		}
		else
		{
			exec('nslookup -type=A ' . $domain, $result);

			foreach($result as $line)
			{
				if(eregi("^$domain", $line))
					return true;
			}
		}

		return false;
	}

	// Verificar si un correo electrónico existe.
	// - $email: Correo electrónico.
	static function CheckEmail($email)
	{
		if(!Core::isValid($email))
			return false;

		$sender = 'beatrock_send@infosmart.mx';
		$SMTP 	= new SMTP_validateEmail();

		$result = $SMTP->validate($email, $sender);
		echo $result;
	}
}
?>