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

class Error extends Exception
{
	public function __construct($data)
	{
		$backtrace = $this->backtrace();
		_r($data);

		//Bit::Status($data['error']['message']);
	}
}
?>