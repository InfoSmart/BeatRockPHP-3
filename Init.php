<?
##############################################################
## 						  BeatRock
##############################################################
## Framework avanzado de procesamiento para PHP.
##############################################################
## InfoSmart © 2012 Todos los derechos reservados.
## Iván Bravo Bravo - @Kolesias123 - webmaster@infosmart.mx
## http://www.infosmart.mx/
##############################################################
## BeatRock se encuentra bajo la licencia de
## Creative Commons "Atribución-Licenciamiento Recíproco"
## http://creativecommons.org/licenses/by-sa/2.5/mx/
##############################################################
## http://beatrock.infosmart.mx/
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

// Reporte de errores recomendado para empezar.
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Permitir acciones internas.
define('BEATROCK', 	true);
define('START', 	microtime(true));
//define('DEBUG', 	true);

// Información esencial del usuario.
define('IP', 	 	$_SERVER['REMOTE_ADDR']);
define('AGENT',  	@$_SERVER['HTTP_USER_AGENT']);
define('FROM',   	@$_SERVER['HTTP_REFERER']);
define('LANG', 	 	substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
define('CHARSET',	strtoupper(ini_get('default_charset')));

// Dirección actual y uso del protocolo seguro.
define('URL', $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
define('SSL', @$_SERVER['HTTPS']);

// Parametros de directorio.
define('DS', 	DIRECTORY_SEPARATOR);
define('ROOT', 	dirname(__FILE__) . DS);

// Parametros de ubicación.
define('KERNEL', 			ROOT . 'Kernel' . DS);
define('KERNEL_TPL', 		KERNEL . 'Views' . DS);
define('KERNEL_TPL_BIT', 	KERNEL_TPL . 'bitrock' . DS);
define('KERNEL_CTRLS', 		KERNEL . 'Controllers' . DS);

define('HEADERS', 			KERNEL . 'Headers' . DS);
define('BIT', 				KERNEL . 'BitRock' . DS);

define('APP', 				ROOT . 'App' . DS);
define('APP_TPL', 			APP . 'Views' . DS);
define('APP_TPL_HEADERS',	APP_TPL . 'headers' . DS);
define('APP_CTRLS', 		APP . 'Controllers' . DS);

define('LANGUAGES', 		APP . 'Languages' . DS);

// Ajustando configuración de PHP recomendada.
ini_set('zlib.output_compression', 	'Off');

// Activando el colector de referencia circular.
gc_enable();

// Empezar sesión.
session_start();

#############################################################
## INICIANDO BitRock: Administrador de procesos iniciales.
#############################################################

// Información del Kernel.
include KERNEL . 'Info.php';

// Iniciando BitRock (Ayudante)...
require KERNEL_CTRLS . 'Bit.php';
new Bit;

#############################################################
## INICIANDO INSTANCIAS DEL SISTEMA
#############################################################

// Preparando y obteniendo el sistema de lenguaje.
new Lang;
// Preparando y obteniendo variables del archivo de Configuración.
new Setup;

// Realizando conexión al servidor MemCache.
new Mem;
// Realizando conexión al servidor SQL.
SQL::Init();
	
// Obteniendo la configuración del sitio web.
new Site;
// Agregando una nueva visita a la base de datos.
Site::Visit();
// Ejecutar tareas de protección al sitio.
Site::Protect();

// Obteniendo datos "POST" perdidos en una sesión anterior.
Client::GetPost();
// Verificación de carga.
Bit::CheckLoad();

#############################################################
## FUNCIONES DE ACCESO DIRECTO
#############################################################

// Remplazar "accesos directos" en una cadena.
// - $str: 	Cadena de texto a ajustar.
// - $other (array): Otros parametros a reemplazar.
function Short($str, $other = '')
{
	$params = get_defined_constants(true);
	$params = $params['user'];

	if(is_array($other))
	{
		foreach($other as $param => $value)
			$params[$param] = $value;
	}

	foreach($params as $param => $value)
		$str = str_ireplace('{' . $param . '}', $value, $str);

	return $str;
}

function Query($table)
{
	return new Query($table);
}

// Ejecutar consulta en el servidor SQL.
// - $q: Consulta a ejecutar.
function q($q, $cache = false, $free = false)
{
	return SQL::query($q, $cache, $free);
}

// Insertar datos en la base de datos.
// - $table: Tabla a insertar los datos.
// - $data (Array): Datos a insertar.
function query_insert($table, $data)
{
	return SQL::query_insert($table, $data);
}
function Insert($table, $data)
{
	return SQL::query_insert($table, $data);
}

// Actualizar datos en la base de datos.
// - $table: Tabla a insertar los datos.
// - $updates (Array): Datos a actualizar.
// - $where (Array): Condiciones a cumplir.
// - $limt (Int): Limite de columnas a actualizar.
function query_update($table, $updates, $where = '', $limit = 1)
{
	return SQL::query_update($table, $updates, $where, $limit);
}
function Update($table, $updates, $where = '', $limit = 1)
{
	return SQL::query_update($table, $updates, $where, $limit);
}

// Obtener numero de valores de una consulta SQL.
// - $q: Consulta a ejecutar.
function query_rows($q)
{
	return SQL::query_rows($q);
}
function Rows($q)
{
	return SQL::query_rows($q);
}

// Obtener los valores de una consulta SQL.
// - $q: Consulta a ejecutar.
function query_assoc($q)
{
	return SQL::query_assoc($q);
}
function Assoc($q)
{
	return SQL::query_assoc($q);
}

// Obtener un dato especifico de una consulta SQL.
// - $q: Consulta a ejecutar.
function query_get($q, $row)
{
	return SQL::query_get($q);
}
function Get($q)
{
	return SQL::query_get($q);
}

// Obtener numero de valores de un recurso MySQL o la última consulta hecha.
// - $q: Recurso de una consulta.
function num_rows($q = '')
{
	return SQL::num_rows($q);
}

// Obtener los valores de un recurso MySQL o la última consulta hecha.
// - $q: Recurso de la consulta.
function fetch_assoc($q = '')
{
	return SQL::fetch_assoc($q);
}

// Obtener los valores de un recurso MySQL o la última consulta hecha.
// - $q: Recurso de la consulta.
function fetch_object($q = '')
{
	return SQL::fetch_object($q);
}

// Obtener los valores de un recurso MySQL o la última consulta hecha.
// - $q: Recurso de la consulta.
function fetch_array($q = '')
{
	return SQL::fetch_array($q);
}

// Liberar la memoria de la última consulta realizada.
// - $q: Recurso de la consulta.
function free_result($q = '')
{
	return SQL::free_result($q);
}

// Obtener la última ID insertada en la base de datos.
function last_id()
{
	return SQL::last_id();
}

// Filtrar una cadena para evitar Inyección SQL.
// - $str: Cadena a filtrar.
// - $html (Bool): ¿Filtrar HTML con HTML ENTITIES? (Evitar Inyección XSS)
// - $e (Charset): Codificación de letras de la cadena a filtrar.
function Filter($str, $html = true, $from = '', $to = '')
{
	return Core::Filter($str, $html, $from, $to);
}
function _f($str, $html = true, $from = '', $to = '')
{
	return Core::Filter($str, $html, $from, $to);
}

// Filtrar una cadena para evitar Inyección XSS.
// - $str: Cadena a filtrar.
// - $e (Charset): Codificación de letras de la cadena a filtrar.
function Clean($str, $from = '', $to = '')
{
	return Core::Clean($str, $e);
}
function _c($str, $from = '', $to = '')
{
	return Core::Clean($str, $from, $to);
}

// Búsqueda de un término en una cadena.
// - $str: Cadena donde buscar.
// - $words (Cadena/Array): Término(s) a buscar.
// - $lower (Bool): ¿Convertir todo a minusculas?
function Contains($str, $words, $lower = false)
{
	return Core::Contains($str, $words, $lower);
}

// Obtener la fecha actual en caracteres.
// - $hour (Bool): ¿Incluir hora?
// - $type (1, 2, 3): Modo de respuesta.
function NormalDate($hour = true, $type = 1)
{
	if(!is_numeric($type) OR $type < 1 OR $type > 3)
		$type = 1;
		
	if($type == 1)
		$date = date('d') . '-' . Date::GetMonth(date('m')) . '-' . date('Y');
	if($type == 2)
		$date = date('d') . '/' . Date::GetMonth(date('m')) . '/' . date('Y');
	if($type == 3)
		$date = date('d') . ' de ' . Date::GetMonth(date('m')) . ' de ' . date('Y');
	
	if($hour)
		$date .= ' ' . date('H:i:s');
	
	return $date;
}

// Calcular tiempo restante/faltante.
// - $date: Tiempo Unix o cadena de tiempo.
// - $num: Devolver solo el numero y tipo.
function CalcTime($date, $num = false)
{
	return Date::CalculateTime($date, $num);
}

// Imprimir de manera visual una matriz (array);
// - $a (Array): Array a imprimir.
function _r($a)
{
	if(!is_array($a) AND !is_object($a))
		return;

	echo '<pre>';
	print_r($a);
	echo '</pre>';
}

// Traducir una cadena.
// - $data: Cadena.
// - $section: Nombre de la sección a utilizar.
// - $lang: Lenguaje a traducir.
// - $tpl: ¿Preparado para la traducción en tiempo real?
function _l($data, $section = '', $lang = '', $tpl = false)
{
	return Lang::SetParams($data, $lang, $section, $tpl);
}

// Establecer variable de plantilla.
// - $param (String, Array): Variable.
// - $vlaue: Valor.
function _t($param, $value = '')
{
	return Tpl::Set($param, $value);
}

// Procesar una plantilla (TPL) y obtener su contenido en HTML.
// - $tpl: Ubicación de la plantilla.
// - $extra (Bool): ¿Aplicar las variables y comprimir HTML?
// - $ext: Extensión de la plantilla.
function _tp($tpl, $extra = false, $ext = '')
{
	return Tpl::Process($tpl, $extra, $ext);
}

// Guardar log.
// - $message: Mensaje a guardar.
// - $type (info, warning, error, mysql): Tipo del log.
function Reg($message, $type = 'info')
{
	Bit::Log($message, $type);
}

// Borrar todas las cookies.
function cookie_destroy()
{
	foreach($_COOKIE as $param)
	{
		setcookie($param, '', -1000);
		unset($_COOKIE[$param]);
	}
}

// Definir una sesión.
// - $param: Parametro/Nombre.
// - $value: Valor, si se deja vacio se retornara el valor actual.
function _SESSION($param, $value = '')
{
	return Core::SESSION($param, $value);
}

// Eliminar una sesión.
// - $param: Parametro/Nombre.
function _DELSESSION($param)
{
	return Core::DELSESSION($param);
}

// Definir una cookie.
// - $param: Parametro/Nombre.
// - $value: Valor, si se deja vacio se retornara el valor actual.
// - $duration: Duración en segundos.
// - $path: Ubicación donde podrá ser válida.
// - $domain: Dominio donde podrá ser válida.
// - $secure (Bool): ¿Solo válida para HTTPS?
// - $imgod (Bool): Si se activa, el navegador web no podrá acceder a la cookie. (Como por ejemplo en JavaScript)
function _COOKIE($param, $value = '', $duration = '', $path = '', $domain = '', $secure = false, $imgod = false)
{
	return Core::COOKIE($param, $value, $duration, $path, $domain, $secure, $imgod);
}

// Eliminar una cookie.
// - $param: Parametro/Nombre.
// - $path: Ubicación donde es válida.
// - $domain: Dominio donde es válida.
function _DELCOOKIE($param, $path = '', $domain = '')
{
	return Core::DELCOOKIE($param, $path, $domain);
}

// Definir una sesión. (Según se adecua la situación)
// - $param: Parametro/Nombre.
// - $value: Valor, si se deja vacio se retornara el valor actual.
function _CACHE($param, $value = '')
{
	return Core::CACHE($param, $value);
}

// Eliminar una sesión. (Según se adecua la situación)
// - $param: Parametro/Nombre.
function _DELCACHE($param)
{
	return Core::DELCACHE($param);
}

#############################################################
## RECUPERACIÓN AVANZADA
#############################################################

Bit::AdvBackup();

#############################################################
## DEFINICIÓN DE VARIABLES GLOBALES
#############################################################

// Definición - Nombre de la aplicación.
define('SITE_NAME', $site['site_name']);

// Definición - Dirección local del Logo.
if(!empty($site['site_logo']))
	define('LOGO', RESOURCES . '/images/'.$site['site_logo']);

// Definición - Motor del navegador web del usuario.
define('ENGINE', Client::Get('engine'));
// Definición - Navegador web del usuario.
define('BROWSER', Client::Get('browser'));
// Definición - Sistema operativo del usuario.
define('OS', Client::Get('os'));
// Definición - Host/DNS del usuario.
define('HOST', Client::Get('host'));
// Definición - País del usuario.
define('COUNTRY', Client::Get('country'));
// Definición - Timezone del usuario.
define('TIMEZONE', Client::Get('timezone'));
// Definición - Dominio actual.
define('DOMAIN', Core::GetHost(PATH));
// Definición - Dirección del RSS
define('RSS', $site['site_rss_path']);

if($config['server']['timezone'] == true AND TIMEZONE !== '')
	date_default_timezone_set(TIMEZONE);

$constants = get_defined_constants(true);
$constants = $constants['user'];

// Definir variables de plantilla para las constantes propias. %PATH%, %RESOURCES%, etc..
Tpl::Set($constants);

// Definir variables de configuración de sitio.
Tpl::Set($site);

#############################################################
## SEGURIDAD
#############################################################

// Almacenar información filtrada en variables cortas.
$G 	= _f($_GET);
$GC = _c($_GET);

$P 	= _f($_POST);
$PC = _c($_POST);

$R 	= _f($_REQUEST);
$RC = _c($_REQUEST);

$PA = $_POST;
$GA = $_GET;
$RA = $_REQUEST;

// Sospechas de inyección.
foreach($_REQUEST as $key => $value)
{
	$value = strtoupper(urldecode($value));

	preg_match("/SELECT ([^<]+) FROM/is", $value, $verify);
	preg_match("/DELETE ([^<]+) FROM/is", $value, $verify2);
	preg_match("/UPDATE FROM/is", $value, $verify3);

	if(count($verify) !== 0 OR count($verify2) !== 0 OR count($verify3) !== 0)
		Core::SendWait();
}

// Si el modo seguro esta activado filtrar toda
// información proviniente del usuario y sesiones.
// Además de eliminar información delicada.
if($config['security']['enabled'] OR $Kernel['secure'] == true AND $Kernel['secure'] !== false)
{
	$_POST 		= $P;
	$_GET 		= $G;
	$_SESSION 	= _f($_SESSION);
		
	unset($config['mysql'], $config['security']['hash']);
}

#############################################################
## VERIFICACIÓN DE CONEXIÓN ACTIVA DEL USUARIO
#############################################################

$my = null;
$ms = null;

Users::Session();
Users::Cookie();

#############################################################
## FUNCIONES PERSONALIZADAS
#############################################################

require APP . 'Functions.php';

#############################################################
## HEMOS TERMINADO
#############################################################

include APP . 'Setup.php';
Reg('BeatRock se ha cargado correctamente.');
?>