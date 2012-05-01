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
## Creative Commons "Atribución-Licenciamiento Recíproco"	##
## http://creativecommons.org/licenses/by-sa/2.5/mx/		##
##############################################################
## http://beatrock.infosmart.mx/				  			##
##############################################################

## -----------------------------------------------------------
##          Inicialzación (Initialization - Init)
## -----------------------------------------------------------
## Archivo de preparación del Kernel, encargado de iniciar
## y administrar los procesos y módulos del sistema.
## -----------------------------------------------------------

#############################################################
## PREPARACIÓN DE CONSTANTES Y OPCIONES INTERNAS	
#############################################################

// Permitir acciones internas.
define('BEATROCK', true);
define('START', microtime(true));
//define('DEBUG', true);

// Información esencial del cliente.
define('IP', $_SERVER['REMOTE_ADDR']);
define('AGENT', @$_SERVER['HTTP_USER_AGENT']);
define('FROM', @$_SERVER['HTTP_REFERER']);
define('LANG', @substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));

// Dirección actual y uso del protocolo seguro.
define('URL', $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
define('SSL', @$_SERVER['HTTPS']);

// Parametros de ubicación interna.
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__) . DS);

// Subparametros de ubicación interna.
define('KERNEL', ROOT . 'Kernel' . DS);
define('TEMPLATES', KERNEL . 'Templates' . DS);
define('HEADERS', KERNEL . 'Headers' . DS);
define('BIT', KERNEL . 'BitRock' . DS);
define('BIT_TEMP', KERNEL . 'BitRock' . DS . 'Templates' . DS);
define('MODS', KERNEL . 'Modules' . DS);

// Ajustando configuración de PHP recomendada.
ini_set('safe_mode', 'Off');
ini_set('register_globals', 'Off');
ini_set('zlib.output_compression', 'Off');
ini_set('short_open_tag', 'On');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Empezar sesión.
session_start();

#############################################################
## INICIANDO BitRock: Administrador de procesos iniciales.
#############################################################

// Información del Kernel.
require(KERNEL . 'Info.php');

// Enviando cabeceras predeterminadas.
header("Server: X", true);
header("X-Powered-By: BeatRock v$Info[version]: http://beatrock.infosmart.mx/");

// BitRock.
require_once(MODS . 'BitRock.php');
new BitRock();

#############################################################
## INICIANDO INSTANCIAS DEL SISTEMA
#############################################################

// Preparando y obteniendo variables del archivo de Configuración.
$config = Setup::Init();
// Realizando conexión al servidor MySQL.
MySQL::Connect();	
	
// Agregando una nueva visita a la base de datos.
Site::addVisit();
// Obteniendo la configuración del sitio web.
$site = Site::getConfiguration();

// Obteniendo datos "POST" perdidos en una sesión anterior.
Client::GetPost();
// Obteniendo traducción actual.
$lang = Site::getTranslation();

// Verificación de carga media.
BitRock::CheckLoad();

#############################################################
## RECUPERACIÓN AVANZADA
#############################################################

// Definiendo la recuperación avanzada.
$back = Core::theSession("backup_time");

// Si la configuración avanzada esta activada, guardar en variables de sesión
// la información perteneciente al archivo de configuración y una copia
// de seguridad de la base de datos.
if($config['server']['backup'] AND (empty($back) OR time() > $back))
{
	Core::theSession("backup_config", Io::Read(KERNEL . 'Configuration.php'));
	Core::theSession("backup_db", MySQL::Backup('', true));
	Core::theSession("backup_time", Core::Time(30, 2));
}
// De otra forma, borrar todo.
else if(!$config['server']['backup'])
{
	Core::delSession("backup_config");
	Core::delSession("backup_db");
	Core::delSession("backup_time");
}
	
#############################################################
## FUNCIONES DE ACCESO DIRECTO
#############################################################

// Función - Ajustar accesos directos.
// - $str: 	Cadena de texto a ajustar.
function ShortCuts($str)
{	
	$str = str_ireplace('{DA}', DB_ALIAS, $str);
	$str = str_ireplace('{SITE_NAME}', SITE_NAME, $str);
	$str = str_ireplace('{PATH}', PATH, $str);
	$str = str_ireplace('{PATH_SSL}', PATH_SSL, $str);
	$str = str_ireplace('{PATH_NS}', PATH_NS, $str);
	$str = str_ireplace('{ADMIN}', ADMIN, $str);
	$str = str_ireplace('{PROTOCOL}', PROTOCOL, $str);
	$str = str_ireplace('{RESOURCES}', RESOURCES, $str);
	$str = str_ireplace('{RESOURCES_SYS}', RESOURCES_SYS, $str);
	$str = str_ireplace('{IP}', IP, $str);
	$str = str_ireplace('{AGENT}', AGENT, $str);
	$str = str_ireplace('{BROWSER}', BROWSER, $str);
	$str = str_ireplace('{OS}', OS, $str);
	$str = str_ireplace('{HOST}', HOST, $str);
	$str = str_ireplace('{DOMAIN}', DOMAIN, $str);

	return $str;
}

// Función - Ejecutar consulta en el servidor MySQL.
// - $q: Consulta a ejecutar.
// - $cache: ¿Guardar resultados en caché?
function query($q, $cache = false)
{
	return MySQL::query($q, $cache);
}

// Función - Insertar datos en la base de datos.
// - $table: Tabla a insertar los datos.
// - $data (Array): Datos a insertar.
function query_insert($table, $data)
{
	return MySQL::query_insert($table, $data);
}

// Función - Actualizar datos en la base de datos.
// - $table: Tabla a insertar los datos.
// - $updates (Array): Datos a actualizar.
// - $where (Array): Condiciones a cumplir.
// - $limt (Int): Limite de columnas a actualizar.
function query_update($table, $updates, $where = '', $limit = 1)
{
	return MySQL::query_update($table, $updates, $where, $limit);
}

// Función - Obtener numero de valores de una consulta MySQL.
// - $q: Consulta a ejecutar.
function query_rows($q)
{
	return MySQL::query_rows($q);
}

// Función - Obtener un dato especifico de una consulta MySQL.
// - $q: Consulta a ejecutar.
// - $row: Dato a obtener.
function query_get($q, $row)
{
	return MySQL::query_get($q, $row);
}

// Función - Filtrar una cadena para evitar Inyección SQL.
// - $str: Cadena a filtrar.
// - $html (Bool): ¿Filtrar HTML con HTML ENTITIES? (Evitar Inyección XSS)
// - $e (Charset): Codificación de letras de la cadena a filtrar.
function FilterText($str, $html = true, $e = "ISO-8859-15")
{
	return Core::FilterText($str, $html, $e);
}
function _f($str, $html = true, $e = "ISO-8859-15")
{
	return Core::FilterText($str, $html, $e);
}

// Función - Filtrar una cadena para evitar Inyección XSS.
// - $str: Cadena a filtrar.
// - $e (Charset): Codificación de letras de la cadena a filtrar.
function CleanText($str, $e = "ISO-8859-15")
{
	return Core::CleanText($str, $e);
}
function _c($str, $e = "ISO-8859-15")
{
	return Core::CleanText($str, $e);
}

// Función - Búsqueda de un término en una cadena.
// - $str: Cadena donde buscar.
// - $words (Cadena/Array): Término(s) a buscar.
// - $lower (Bool): ¿Convertir todo a minusculas?
function Contains($str, $words, $lower = false)
{
	return Core::Contains($str, $words, $lower);
}

// Función - Obtener la fecha actual en caracteres.
// - $hour (Bool): ¿Incluir hora?
// - $type (1, 2, 3): Modo de respuesta.
function NormalDate($hour = true, $type = 1)
{
	if(!is_numeric($type) OR $type < 1 OR $type > 3)
		$type = 1;
		
	if($type == 1)
		$date = date('d') . "-" . GetMonth(date('m')) . "-" . date('Y');
	if($type == 2)
		$date = date('d') . "/" . GetMonth(date('m')) . "/" . date('Y');
	if($type == 3)
		$date = date('d') . " de " . GetMonth(date('m')) . " de " . date('Y');
	
	if($hour)
		$date .= " " . date('H:i:s');
	
	return $date;
}

// Función - Obtener el mes en caracteres de un mes numerico.
// - $num (Int): Mes numerico.
// - $c (Bool): ¿Obtener solo las tres primeras letras?
function GetMonth($num, $c = false)
{
	return Core::getMonth($num, $c);
}

// Función - Imprimir correctamente un Array;
// - $a (Array): Array a imprimir.
function _r($a)
{
	if(!is_array($a))
		return;

	echo "<pre>";
	print_r($a);
	echo "</pre>";
}

#############################################################
## DEFINICIÓN DE VARIABLES GLOBALES
#############################################################

// Variables de fecha y tiempo.
$date['f'] = (time() - (8 * 60));
$date['d'] = date('d');
$date['G'] = date('G');
$date['i'] = date('i');
$date['s'] = date('s');
$date['N'] = date('N');
$date['n'] = date('n');
$date['j'] = date('j');
$date['Y'] = date('Y');

// Definición - Nombre de la aplicación.
define("SITE_NAME", $site['site_name']);

// Definición - Dirección local del Logo.
if(!empty($site['site_logo']))
	define("LOGO", RESOURCES . "/images/$site[site_logo]");
else
	define("LOGO", "");

// Definición - Navegador web del usuario.
define("BROWSER", Client::Get("browser"));
// Definición - Sistema operativo del usuario.
define("OS", Client::Get("os"));
// Definición - Host/DNS del usuario.
define("HOST", Client::Get("host"));
// Definición - Dominio actual.
define("DOMAIN", Core::GetHost(PATH));

// Definir variables predeterminadas para la plantilla.
Tpl::Set(Array(
	"DB_ALIAS" => DB_ALIAS,
	"SITE_NAME" => SITE_NAME,
	"LOGO" => LOGO,
	"PATH" => PATH,
	"PATH_SSL" => PATH_SSL,
	"PATH_NS" => PATH_NS,
	"PATH_NOW" => PATH_NOW,
	"ADMIN" => ADMIN,
	"PROTOCOL" => PROTOCOL,
	"RESOURCES" => RESOURCES,
	"RESOURCES_SYS" => RESOURCES_SYS,
	"IP" => IP,
	"AGENT" => AGENT,
	"BROWSER" => BROWSER,
	"OS" => OS,
	"HOST" => HOST,
	"DOMAIN" => DOMAIN
));

// Definir variables de configuración de sitio.
Tpl::Set($site);

#############################################################
## ¡EN MANTENIMIENTO!
#############################################################

if($site['site_state'] !== "open")
{
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Cache-Control: no-cache');

	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');
	
	echo Tpl::Process(BIT_TEMP . "Maintenance");
	exit;
}

#############################################################
## MODO SEGURO
#############################################################

// Almacenar información filtrada en variables cortas.
$G = _f($_GET);
$GC = _c($_GET);

$P = _f($_POST);
$PC = _c($_POST);

$R = _f($_REQUEST);
$RC = _c($_REQUEST);

$PA = $_POST;
$GA = $_GET;
$RA = $_REQUEST;

// Si el modo seguro esta activado filtrar toda
// información proviniente del usuario y sesiones.
// Además de eliminar información delicada.
if($config['security']['enabled'] OR $Kernel['secure'] == true AND $Kernel['secure'] !== false)
{
	$_POST = $P;
	$_GET = $G;
	$_SESSION = _f($_SESSION);
		
	unset($config['mysql'], $config['security']['hash']);
	BitRock::log("Se ha pasado por los filtros de seguridad correctamente.");
}

#############################################################
## VERIFICACIÓN DE CONEXIÓN ACTIVA
#############################################################

$my = null;
$ms = null;

Users::CheckSession();
Users::CheckCookie();

#############################################################
## HEMOS TERMINADO
#############################################################

require_once(KERNEL . 'Functions.php');
BitRock::log("BeatRock se ha cargado correctamente.");
?>