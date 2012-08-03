<?php
##############################################################
## 						  BeatRock
##############################################################
## Framework avanzado de procesamiento para PHP.
##############################################################
## InfoSmart � 2012 Todos los derechos reservados.
## Iv�n Bravo Bravo - @Kolesias123 - webmaster@infosmart.mx
## http://www.infosmart.mx/
##############################################################
## BeatRock se encuentra bajo la licencia de
## Creative Commons "Atribuci�n-Licenciamiento Rec�proco"
## http://creativecommons.org/licenses/by-sa/2.5/mx/
##############################################################
## http://beatrock.infosmart.mx/
##############################################################

## -----------------------------------------------------------
##          Inicialzaci�n (Initialization - Init)
## -----------------------------------------------------------
## Archivo de preparaci�n del Kernel, encargado de iniciar
## y administrar los procesos y m�dulos del sistema.
## -----------------------------------------------------------

#############################################################
## PREPARACI�N DE CONSTANTES Y OPCIONES INTERNAS	
#############################################################

// Reporte de errores recomendado para empezar.
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Permitir acciones internas.
define('BEATROCK', 	true);
define('START', 	microtime(true));
//define('DEBUG', 	true);

// Informaci�n esencial del usuario.
define('IP', 	$_SERVER['REMOTE_ADDR']);
define('AGENT', $_SERVER['HTTP_USER_AGENT']);
define('FROM',  $_SERVER['HTTP_REFERER']);
define('LANG', 	substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));

// Direcci�n actual y uso del protocolo seguro.
define('URL', $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
define('SSL', @$_SERVER['HTTPS']);

// Parametros de ubicaci�n interna.
define('DS', 	DIRECTORY_SEPARATOR);
define('ROOT', 	dirname(__FILE__) . DS);

// Subparametros de ubicaci�n interna.
define('KERNEL', 		ROOT . 'Kernel' . DS);
define('TEMPLATES', 	KERNEL . 'Templates' . DS);
define('TEMPLATES_BIT', TEMPLATES . 'bitrock' . DS);
define('HEADERS', 		KERNEL . 'Headers' . DS);
define('BIT', 			KERNEL . 'BitRock' . DS);
define('MODS', 			KERNEL . 'Modules' . DS);
define('SITE_MODS', 	MODS . 'Site' . DS);
define('LANGUAGES', 	KERNEL . 'Languages' . DS);

// Ajustando configuraci�n de PHP recomendada.
ini_set('zlib.output_compression', 	'Off');
ini_set('short_open_tag', 			'On');
ini_set('expose_php', 				'Off');

// Activando el colector de referencia circular.
gc_enable();

// Empezar sesi�n.
session_start();

#############################################################
## INICIANDO BitRock: Administrador de procesos iniciales.
#############################################################

// Informaci�n del Kernel.
require KERNEL . 'Info.php';

// Iniciando BitRock...
require MODS . 'BitRock.php';
new BitRock();

#############################################################
## INICIANDO INSTANCIAS DEL SISTEMA
#############################################################

// Preparando y obteniendo el sistema de lenguaje.
$lang 	= Lang::Init();
// Preparando y obteniendo variables del archivo de Configuraci�n.
$config = Setup::Init();

// Realizando conexi�n al servidor MemCache.
Mem::Init();
// Realizando conexi�n al servidor MySQL.
MySQL::Connect();
	
// Obteniendo la configuraci�n del sitio web.
$site = Site::GetConfig();
// Agregando una nueva visita a la base de datos.
Site::AddVisit();

// Obteniendo datos "POST" perdidos en una sesi�n anterior.
Client::GetPost();

// Verificaci�n de carga media.
BitRock::CheckLoad();

#############################################################
## RECUPERACI�N AVANZADA
#############################################################

// Definiendo la recuperaci�n avanzada.
$back = Core::TheCache('backup_time');

// Si la configuraci�n avanzada esta activada, guardar en variables de sesi�n
// la informaci�n perteneciente al archivo de configuraci�n y una copia
// de seguridad de la base de datos.
if($config['server']['backup'] AND (empty($back) OR time() > $back))
{
	Core::TheCache('backup_config', Io::Read(KERNEL . 'Configuration.php'));
	Core::TheCache('backup_db', MySQL::Backup('', true));
	Core::TheCache('backup_time', Core::Time(30, 2));
}
// De otra forma, borrar todo.
else if(!$config['server']['backup'])
{
	Core::DelCache('backup_config');
	Core::DelCache('backup_db');
	Core::DelCache('backup_time');
}
	
#############################################################
## FUNCIONES DE ACCESO DIRECTO
#############################################################

// Remplazar "accesos directos" en una cadena.
// - $str: 	Cadena de texto a ajustar.
// - $other (array): Otros parametros a reemplazar.
function ShortCuts($str, $other = '')
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

// Ejecutar consulta en el servidor MySQL.
// - $q: Consulta a ejecutar.
function query($q)
{
	return MySQL::query($q);
}

// Insertar datos en la base de datos.
// - $table: Tabla a insertar los datos.
// - $data (Array): Datos a insertar.
function query_insert($table, $data)
{
	return MySQL::query_insert($table, $data);
}
function Insert($table, $data)
{
	return MySQL::query_insert($table, $data);
}

// Actualizar datos en la base de datos.
// - $table: Tabla a insertar los datos.
// - $updates (Array): Datos a actualizar.
// - $where (Array): Condiciones a cumplir.
// - $limt (Int): Limite de columnas a actualizar.
function query_update($table, $updates, $where = '', $limit = 1)
{
	return MySQL::query_update($table, $updates, $where, $limit);
}
function Update($table, $updates, $where = '', $limit = 1)
{
	return MySQL::query_update($table, $updates, $where, $limit);
}

// Obtener numero de valores de una consulta MySQL.
// - $q: Consulta a ejecutar.
function query_rows($q)
{
	return MySQL::query_rows($q);
}
function Rows($q)
{
	return MySQL::query_rows($q);
}

// Obtener los valores de una consulta MySQL.
// - $q: Consulta a ejecutar.
function query_assoc($q)
{
	return MySQL::query_assoc($q);
}
function Assoc($q)
{
	return MySQL::query_assoc($q);
}

// Obtener un dato especifico de una consulta MySQL.
// - $q: Consulta a ejecutar.
function query_get($q, $row)
{
	return MySQL::query_get($q);
}
function Get($q)
{
	return MySQL::query_get($q);
}

// Obtener numero de valores de un recurso MySQL o la �ltima consulta hecha.
// - $q: Recurso de una consulta.
function num_rows($q = '')
{
	return MySQL::num_rows($q);
}

// Obtener los valores de un recurso MySQL o la �ltima consulta hecha.
// - $q: Recurso de la consulta.
function fetch_assoc($q = '')
{
	return MySQL::fetch_assoc($q);
}

// Obtener los valores de un recurso MySQL o la �ltima consulta hecha.
// - $q: Recurso de la consulta.
function fetch_object($q = '')
{
	return MySQL::fetch_object($q);
}

// Obtener los valores de un recurso MySQL o la �ltima consulta hecha.
// - $q: Recurso de la consulta.
function fetch_array($q = '')
{
	return MySQL::fetch_array($q);
}

// Liberar la memoria de la �ltima consulta realizada.
// - $q: Recurso de la consulta.
function free_result($q = '')
{
	return MySQL::free_result($q);
}

// Obtener la �ltima ID insertada en la base de datos.
function last_id()
{
	return MySQL::last_id();
}

// Filtrar una cadena para evitar Inyecci�n SQL.
// - $str: Cadena a filtrar.
// - $html (Bool): �Filtrar HTML con HTML ENTITIES? (Evitar Inyecci�n XSS)
// - $e (Charset): Codificaci�n de letras de la cadena a filtrar.
function FilterText($str, $html = true, $e = 'ISO-8859-15')
{
	return Core::FilterText($str, $html, $e);
}
function _f($str, $html = true, $e = 'ISO-8859-15')
{
	return Core::FilterText($str, $html, $e);
}

// Filtrar una cadena para evitar Inyecci�n XSS.
// - $str: Cadena a filtrar.
// - $e (Charset): Codificaci�n de letras de la cadena a filtrar.
function CleanText($str, $e = 'ISO-8859-15')
{
	return Core::CleanText($str, $e);
}
function _c($str, $e = 'ISO-8859-15')
{
	return Core::CleanText($str, $e);
}

// B�squeda de un t�rmino en una cadena.
// - $str: Cadena donde buscar.
// - $words (Cadena/Array): T�rmino(s) a buscar.
// - $lower (Bool): �Convertir todo a minusculas?
function Contains($str, $words, $lower = false)
{
	return Core::Contains($str, $words, $lower);
}

// Obtener la fecha actual en caracteres.
// - $hour (Bool): �Incluir hora?
// - $type (1, 2, 3): Modo de respuesta.
function NormalDate($hour = true, $type = 1)
{
	if(!is_numeric($type) OR $type < 1 OR $type > 3)
		$type = 1;
		
	if($type == 1)
		$date = date('d') . '-' . GetMonth(date('m')) . '-' . date('Y');
	if($type == 2)
		$date = date('d') . '/' . GetMonth(date('m')) . '/' . date('Y');
	if($type == 3)
		$date = date('d') . ' de ' . GetMonth(date('m')) . ' de ' . date('Y');
	
	if($hour)
		$date .= ' ' . date('H:i:s');
	
	return $date;
}

// Obtener el mes en caracteres de un mes numerico.
// - $num (Int): Mes numerico.
// - $c (Bool): �Obtener solo las tres primeras letras?
function GetMonth($num, $c = false)
{
	return Core::GetMonth($num, $c);
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
// - $section: Nombre de la secci�n a utilizar.
// - $lang: Lenguaje a traducir.
// - $tpl: �Preparado para la traducci�n en tiempo real?
function _l($data, $section = '', $lang = '', $tpl = false)
{
	return Lang::SetParams($data, $lang, $section, $tpl);
}

// Calcular tiempo restante/faltante.
// - $date: Tiempo Unix o cadena de tiempo.
// - $num: Devolver solo el numero y tipo.
function CalcTime($date, $num = false)
{
	return Core::CalculateTime($date, $num);
}

// Guardar log.
// - $message: Mensaje a guardar.
// - $type (info, warning, error, mysql): Tipo del log.
function Reg($message, $type = 'info')
{
	BitRock::Log($message, $type);
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

// Definir una sesi�n.
// - $param: Parametro/Nombre.
// - $value: Valor, si se deja vacio se retornara el valor actual.
function _SESSION($param, $value = '')
{
	return Core::theSession($param, $value);
}

// Eliminar una sesi�n.
// - $param: Parametro/Nombre.
function _DELSESSION($param)
{
	return Core::delSession($param);
}

// Definir una cookie.
// - $param: Parametro/Nombre.
// - $value: Valor, si se deja vacio se retornara el valor actual.
// - $duration: Duraci�n en segundos.
// - $path: Ubicaci�n donde podr� ser v�lida.
// - $domain: Dominio donde podr� ser v�lida.
// - $secure (Bool): �Solo v�lida para HTTPS?
// - $imgod (Bool): Si se activa, el navegador web no podr� acceder a la cookie. (Como por ejemplo en JavaScript)
function _COOKIE($param, $value = '', $duration = '', $path = '', $domain = '', $secure = false, $imgod = false)
{
	return Core::theCookie($param, $value, $duration, $path, $domain, $secure, $imgod);
}

// Eliminar una cookie.
// - $param: Parametro/Nombre.
// - $path: Ubicaci�n donde es v�lida.
// - $domain: Dominio donde es v�lida.
function _DELCOOKIE($param, $path = '', $domain = '')
{
	return Core::delCookie($param, $path, $domain);
}

#############################################################
## DEFINICI�N DE VARIABLES GLOBALES
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

// Definici�n - Nombre de la aplicaci�n.
define('SITE_NAME', $site['site_name']);

// Definici�n - Direcci�n local del Logo.
if(!empty($site['site_logo']))
	define('LOGO', RESOURCES . '/images/'.$site['site_logo']);

// Definici�n - Motor del navegador web del usuario.
define('ENGINE', Client::Get('engine'));
// Definici�n - Navegador web del usuario.
define('BROWSER', Client::Get('browser'));
// Definici�n - Sistema operativo del usuario.
define('OS', Client::Get('os'));
// Definici�n - Host/DNS del usuario.
define('HOST', Client::Get('host'));
// Definici�n - Dominio actual.
define('DOMAIN', Core::GetHost(PATH));

// Constantes definidas.
$constants = get_defined_constants(true);
$constants = $constants['user'];

// Evitar problemas
$site['site_slogan'] 		= html_entity_decode($site['site_slogan']);
$site['site_description'] 	= html_entity_decode($site['site_description']);
$site['site_keywords'] 		= html_entity_decode($site['site_keywords']);

// Definir variables de plantilla para todas las constantes.
Tpl::Set($constants);
// Definir variables de configuraci�n de sitio.
Tpl::Set($site);

#############################################################
## MODO SEGURO
#############################################################

// Almacenar informaci�n filtrada en variables cortas.
$G 	= _f($_GET);
$GC = _c($_GET);

$P 	= _f($_POST);
$PC = _c($_POST);

$R 	= _f($_REQUEST);
$RC = _c($_REQUEST);

$PA = $_POST;
$GA = $_GET;
$RA = $_REQUEST;

// Si el modo seguro esta activado filtrar toda
// informaci�n proviniente del usuario y sesiones.
// Adem�s de eliminar informaci�n delicada.
if($config['security']['enabled'] OR $Kernel['secure'] == true AND $Kernel['secure'] !== false)
{
	$_POST 		= $P;
	$_GET 		= $G;
	$_SESSION 	= _f($_SESSION);
		
	unset($config['mysql'], $config['security']['hash']);
}

#############################################################
## VERIFICACI�N DE CONEXI�N ACTIVA
#############################################################

$my = null;
$ms = null;

Users::CheckSession();
Users::CheckCookie();

#############################################################
## HEMOS TERMINADO
#############################################################

require KERNEL . 'Functions.php';
Reg('BeatRock se ha cargado correctamente.');
?>