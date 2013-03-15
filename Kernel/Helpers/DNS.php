<?
/**
 * BeatRock
 *
 * Framework para el desarrollo de aplicaciones web.
 *
 * @author 		Iván Bravo <webmaster@infosmart.mx> @Kolesias123
 * @copyright 	InfoSmart 2013. Todos los derechos reservados.
 * @license 	http://creativecommons.org/licenses/by-sa/2.5/mx/  Creative Commons "Atribución-Licenciamiento Recíproco"
 * @link 		http://beatrock.infosmart.mx/
 * @version 	3.0
 *
 * @package 	DNS
 * Permite la verificación de información por medio de DNS.
 *
*/

# Acción ilegal.
if ( !defined('BEATROCK') )
	exit;

class DNS
{
	/**
	 * Verifica si un dominio existe.
	 * @param string $domain Dominio
	 */
	static function CheckDomain($domain)
	{
		# No es un dominio válido.
		if ( !Core::Valid($domain, DOMAIN) )
			return false;

		# Usemos la función checkdnsrr
		if ( function_exists('checkdnsrr') )
		{
			if ( checkdnsrr($domain . '.', 'MX') )
				return true;

			if ( checkdnsrr($domain . '.', 'A') )
				return true;
		}
		# Usemos la consola.
		else if ( function_exists('exec') )
		{
			exec('nslookup -type=A ' . $domain, $result);

			foreach ( $result as $line )
			{
				if ( Contains($domain, $line) )
					return true;
			}
		}

		return false;
	}
}
?>