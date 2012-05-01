<?php
##############################################################
## 						  BeatRock				  	   		##
##############################################################
## Framework avanzado de procesamiento para PHP.   			##
##############################################################
## InfoSmart © 2012 Todos los derechos reservados. 			##
## Iván Bravo Bravo - Kolesias123			  	   			##
## http://www.infosmart.mx/									##
##############################################################
## BeatRock se encuentra bajo la licencia de	   			##
## Creative Commons "Attribution-ShareAlike 3.0 Unported"	##
## http://creativecommons.org/licenses/by-sa/3.0/			##
##############################################################
## http://beatrock.infosmart.mx/				  			##
##############################################################

#############################################################
## PREPARACIÓN DE CONSTANTES Y OPCIONES INTERNAS	
#############################################################

// Permitir acciones internas.
define('BEATROCK', true);

// Información esencial del cliente.
define('IP', $_SERVER['REMOTE_ADDR']);

// Dirección actual y uso del protocolo seguro.
define('URL', $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
define('SSL', @$_SERVER['HTTPS']);

// Ajustando configuración de PHP recomendada.
ini_set('safe_mode', 'Off');
ini_set('register_globals', 'Off');
ini_set('zlib.output_compression', 'Off');
ini_set('short_open_tag', 'On');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Enviando cabeceras predeterminadas.
header('Server: X');
header('X-Powered-By: BeatRock: http://beatrock.infosmart.mx/');

// Empezar sesión.
session_start();

// Ejecutar la función de apagado cuando BeatRock termine.
register_shutdown_function('ShutDown');

#############################################################
## DEFINICIÓN DE VARIABLES GLOBALES
#############################################################

// Variables de fecha y tiempo.
$date['mc'] = microtime();
$date['f'] = (time() - (8 * 60));

#############################################################
## FUNCIONES DE PROCESAMIENTO INTERNO
#############################################################

function Random($length, $letters = true, $numbers = true, $other = false)
{
	if(!is_numeric($length))
		return;
			
	$result = "";
	$poss = "";
	$i = 0;
		
	if($letters)
		$poss .= "abcdefhijklwxyz";
			
	if($numbers)
		$poss .= "0123456789";
			
	if($other)
		$poss .= "ABCDEFHIJKL@%&^*/(){}-_";
			
	while($i < $length)
	{
		$result .= substr($poss, mt_rand(0, strlen($poss) - 1), 1);
		$i++;
	}
	
	return $result;
}

function CleanText($str)
{
	if(is_array($str))
	{
		$final = Array();
		
		foreach($str as $param => $value)
			$final[$param] = CleanText($value);
			
		return $final;
	}

	if(!is_string($str))
		return $str;
	
	$str = stripslashes(trim($str));
	$str = htmlentities($str, ENT_COMPAT | ENT_SUBSTITUTE, "ISO-8859-15", false);
			
	$str = str_replace('&amp;', '&', $str);
	$str = iconv("ISO-8859-15", "ISO-8859-15//TRANSLIT//IGNORE", $str);
	
	return nl2br($str);
}

function Encrypte($str, $level = 4, $hash)
{
	if(!is_string($str))
		return $str;
		
	if($level == 1)
		$str = md5($str . $hash);
	if($level == 2)
		$str = sha1($str . $hash);
	if($level == 3)
	{
		$s = hash_init('sha256', HASH_HMAC, $hash);
		hash_update($s, sha1($str));
		hash_update($s, $hash);
		$str = hash_final($s);
	}
	if($level == 4)
	{
		$s = hash_init('sha256', HASH_HMAC, $hash);
		hash_update($s, sha1($str));
		hash_update($s, $hash);
		$str = hash_final($s);
		$str = md5($hash . $str);
	}
	if($level == 5)
	{
		$result = "";
		
		for($i = 0; $i < strlen($str); $i++)
		{
			$char = substr($str, $i, 1);
			$keychar = substr($hash, ($i % strlen($hash)) -1, 1);
			$char = chr(ord($char) + ord($keychar));
			$result .= $char;
		}
		
		$str = base64_encode($result);
	}
		
	return $str;			
}

function isValid($str, $type = 'email')
{
	if($type == "email")
		$p = "^[^0-9][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,48}$/";
	if($type == "username")
		$p = "^[a-z\d_]{5,32}$/i";
	if($type == "ip")
		$p = "^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/";
	if($type == "credit.card")
		$p = "^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/";
	if($type == "url")
		$p = "^(http|https):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i";
	if($type == "password")
		$p = "^[a-z+0-9]/i";
			
	if(empty($p) OR empty($str))
		return "NO";
			
	$valid = preg_match("/$p", $str);
	return !$valid ? "NO" : "SI";
}

function CreateDB($sql)
{
	global $step;
	
	$database = file_get_contents("Database");
	$database = str_replace("{DB_ALIAS}", $step['mysql_alias'], $database);
			
	$db = explode(";", $database);
			
	foreach($db as $query)
	{
		$query = trim($query);
			
		if(empty($query))
			continue;
					
		mysql_query($query, $sql);
	}
}

function ShutDown()
{
	// Cerrar sesión actual.
	session_write_close();
	
	if(!defined("NO_FOOTER"))
		require('Footer.php');
}

function CheckInit()
{
	$result['setup'] = is_writable('../Setup/');
	$result['kernel'] = is_writable('../Kernel/');
	
	$result['config'] = is_readable('./Configuration');
	$result['db'] = is_readable('./Database');
	$result['htaccess'] = is_readable('./Htaccess');
	
	$result['curl'] = function_exists('curl_init');
	$result['json'] = function_exists('json_encode');
	
	$result['php'] = true;
	
	if(PHP_MAJOR_VERSION >= 5)
	{
		if(PHP_MINOR_VERSION < 3)
			$result['php'] = false;
	}
	
	return $result;
}

function CheckReady()
{
	if(file_exists('../Kernel/Configuration.php') OR file_exists('./SECURE'))
	{
		if($_SESSION['install']['secure'] !== true)
		{
			header("Location: ./error_ready");
			exit;
		}
	}
}

function CheckRelease()
{
	global $Info;
	$check = file_get_contents("http://beatrock.infosmart.mx/system/check_release?ver=" . $Info['version']);
	$check = json_decode(trim($check), true);
	
	if(!is_array($check))
		$status = "&times; Error de verificación de versión.";
	else if($check['code'] == "ERROR")
		$status = 'Se encuentra una actualización disponible (' . $check['ver'] . ').<br /><a href="' . $check['download'] . '">Descargar</a> - <a href="' . $check['url'] . '" target="_blank">Más información</a>.';
	else
		$status = "&radic; BeatRock está actualizado.";
		
	return $status;
}

#############################################################
## MODO SEGURO
#############################################################

// Almacenar información en variables cortas.
$P = CleanText($_POST);
$G = CleanText($_GET);
	
#############################################################
## VERIFICACIÓN DE PREPARACIÓN
#############################################################

$status = CheckInit();

foreach($status as $param => $value)
{
	if($value == false)
	{
		if($page['id'] !== "status")
		{
			header("Location: ./status");
			exit;
		}
		else
			$status[$param] = '<b class="red">Error - Sin cumplir</b>';
	}
	else
		$status[$param] = '<b class="green">¡Perfecto!</b>';
}

if($page['id'] !== "ready")
	CheckReady();
	
#############################################################
## HEMOS TERMINADO
#############################################################

require('../Kernel/Info.php');
?>