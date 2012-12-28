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

#############################################################
## PREPARACIÓN DE CONSTANTES Y OPCIONES INTERNAS	
#############################################################

// Permitir acciones internas.
define('BEATROCK', 	true);

// Información esencial del usuario.
define('IP', 	$_SERVER['REMOTE_ADDR']);
define('CHARSET',	(ini_get('default_charset') == '') ? 'UTF-8' : strtoupper(ini_get('default_charset')));
define('URL', 		$_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);

// Recursos de la instalación.
define('RESOURCES_INS', '//resources.infosmart.mx');

// Ajustando configuración de PHP recomendada.
ini_set('zlib.output_compression', 	'Off');

// Reporte de errores predeterminado.
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Empezar sesión.
session_start();

// Apagado
register_shutdown_function('Shutdown');

#############################################################
## FUNCIONES
#############################################################

function CheckRelease()
{
	global $Info;

	$check = file_get_contents('http://beatrock.infosmart.mx/system/check_release?ver=' . $Info['version']);
	$check = json_decode($check, true);

	return $check;
}

function CheckSystem()
{
	$result = [];

	$result['setup']		= is_writable('../Setup/');
	$result['kernel']		= is_writable('../Kernel/');
	$result['app']			= is_writable('../App/');

	$result['config'] 		= is_readable('./templates/Configuration');
	$result['db'] 			= is_readable('./templates/Database');
	$result['htaccess'] 	= is_readable('./templates/Htaccess');
	$result['webconfig'] 	= is_readable('./templates/Webconfig');

	$result['curl'] 		= function_exists('curl_init');
	$result['json'] 		= function_exists('json_encode');

	$result['shorttag'] 	= ini_get('short_open_tag');	
	$result['php'] 			= version_compare(PHP_VERSION, '5.4.0', '>=');

	$result['shell'] 		= function_exists('shell_exec');
	$result['cache']		= extension_loaded('mysqlnd_qc');
	$result['memcache']		= extension_loaded('memcache');
	$result['sqlite']		= extension_loaded('sqlite3');

	global $system;
	$system = $result;
}

function GetSystem()
{
	global $system;
	$result = $system;

	foreach($result as $key => $value)
	{
		if($value == true)
			$result[$key] = '<label class="icon" style="color: green">&#xe03b;</label>';
		else
		{
			$result[$key] = '<label class="icon" style="color: red">&#xe039;</label>';
		}
	}

	$system = $result;
}

function CreateDB($mysql)
{
	$database 	= file_get_contents('../templates/Database');
	$database 	= str_ireplace('{DB_PREFIX}', $_POST['sql_prefix'], $database);

	$db 		= explode(';', $database);

	if(CHARSET == 'UTF-8')
		$mysql->query("SET NAMES 'utf8'");

	foreach($db as $query)
	{
		$query = trim($query);

		if(empty($query))
			continue;

		$result = $mysql->query($query);

		if($result == false)
			return "'$query': " . $mysql->error;
	}

	return true;
}

function CreateDBLite($dbname)
{
	$database 	= file_get_contents('../templates/Database_SQLite');
	$result 	= file_put_contents($dbname, $database);

	return $result;
}

function Shutdown()
{
	if($_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest')
		require 'content/footer.php';
}

function _c($str, $from = '', $to = '')
{
	if(empty($to))
		$to = CHARSET;

	if(is_array($str))
	{
		if(count($str) > 50)
			return;

		$final = [];
			
		foreach($str as $param => $value)
			$final[$param] = _c($value, $from, $to);
				
		return $final;
	}
		
	if(!is_string($str))
		return $str;
			
	$str = trim($str);
	$str = htmlentities($str, ENT_COMPAT | ENT_SUBSTITUTE, $from, false);			
	$str = str_replace('&amp;', '&', $str);
		
	if(!empty($from) AND $from !== $to)
		$str = iconv($from, $to . '//TRANSLIT//IGNORE', $str);
		
	return nl2br($str);
}

function Random($length, $letters = true, $numbers = true, $other = false)
{
	if(!is_numeric($length))
		return;
			
	$result = '';
	$poss = '';
	$i = 0;
		
	if($letters)
		$poss .= 'abcdefghijklmnopqrstuvwxyz';
	
	if($numbers)
		$poss .= '0123456789';

	if($other)
		$poss .= 'ABCDEFHIJKL@%&^*/(){}-_';

	$poss = str_split($poss, 1);

	for($i = 1; $i < $length; ++$i)
	{
		mt_srand((double)microtime() * 1000000);

		$num 		= mt_rand(1, count($poss));
		$result 	.= $poss[$num - 1];
	}
		
	return $result;
}

function Valid($value, $type = 'email')
{
	if($type == 'email')
		$p = '^[^0-9][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,48}$/';
	if($type == 'username')
		$p = '^[a-z\d_]{5,32}$/i';
	if($type == 'ip')
		$p = '^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/';
	if($type == 'credit.card')
		$p = '^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/';
	if($type == 'url')
		$p = '^(http|https):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i';
	if($type == 'password')
		$p = '^[a-z+0-9]/i';
	if($type == 'subdomain')
		$p = '^[a-z]{3,10}$/i';
	if($type == 'domain')
		$p = '^([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i';
			
	if(empty($p) OR empty($value))
		return false;
			
	$valid = preg_match("/$p", $value);
	return (!$valid) ? false : true;
}

#############################################################
## PROCESOS
#############################################################

$ready 	= true;
$system = [];

CheckSystem();

foreach($system as $key => $value)
{
	if($key !== 'cache' AND $key !== 'memcache' AND $key !== 'shell')
	{
		if($value == false)
		{
			$ready = false;
			break;
		}
	}
}

#############################################################
## MODO SEGURO
#############################################################

// Almacenar información en variables cortas.
$P = _c($_POST);
$G = _c($_GET);

#############################################################
## HEMOS TERMINADO
#############################################################

// Información del Kernel.
include '../Kernel/Info.php';
?>