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
 * @package 	Init
 * Preparación del Kernel, encargado de iniciar y administrar
 * los procesos, módulos y controladores del sistema.
 *
*/

#############################################################
## PREPARACIÓN DE CONSTANTES Y OPCIONES INTERNAS
#############################################################

# Reporte de errores recomendado para comenzar.
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

# Permitir acciones internas.
define('BEATROCK', 	true);
define('START', 	microtime(true));
define('DEBUG', 	true); // Descomente esta línea para imprimir mensajes de procesamiento.

# Información esencial del visitante.
define('IP', 	 	$_SERVER['REMOTE_ADDR']);
define('AGENT',  	$_SERVER['HTTP_USER_AGENT']);
define('FROM',   	$_SERVER['HTTP_REFERER']);
//define('LANG', 	 	substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
define('LANG', 'es');
define('CHARSET',	strtoupper(ini_get('default_charset')));

# Dirección actual y uso del protocolo seguro.
define('URL', 	$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
define('QUERY', $_SERVER['QUERY_STRING']);
define('SSL',	$_SERVER['HTTPS']);

# Ruta de la aplicación.
define('DS', 	DIRECTORY_SEPARATOR);
define('ROOT', 	dirname(__FILE__) . DS);

# Necesario para la función "htmlentities"
if ( !defined('ENT_SUBSTITUTE') )
	define('ENT_SUBSTITUTE', 8);

# Sin compresión ZLIB
ini_set('zlib.output_compression', 	'Off');

# Activando el colector de referencia circular.
gc_enable();

# Empezar sesión.
session_start();

#############################################################
## BUSCANDO Y ESTABLECIENDO RUTAS
#############################################################

# Rutas donde buscar el Kernel.
$search_kernel = array(
	ROOT,
	dirname(ROOT),
	dirname(dirname(ROOT)),
	dirname(dirname(dirname(ROOT)))
);

# Buscamos...
foreach( $search_kernel as $path )
{
	# Lo hemos encontrado.
	if( is_dir($path . DS . 'Kernel') )
	{
		define('KERNEL', $path . DS . 'Kernel' . DS);
		break;
	}
}

# Kernel: Ruta de las vistas.
define('KERNEL_VIEWS', 		KERNEL . 		'Views' . DS);
# Kernel: Ruta de las vistas relacionadas a BitRock.
define('KERNEL_VIEWS_BIT', 	KERNEL_VIEWS . 	'bitrock' . DS);

# App: Ruta de la carpeta interna.
define('APP', 					ROOT . 		'App' . DS);
# App: Ruta de las vistas.
define('APP_VIEWS', 			APP . 		'Views' . DS);
# App: Ruta de las cabeceras.
define('APP_VIEWS_HEADERS',		APP_VIEWS . 'headers' . DS);
# App: Ruta de las cabeceras.
define('BIT',				APP . 'Bitrock' . DS);
# App: Ruta de las traducciones.
define('LANGUAGES', 		APP . 'Languages' . DS);

#############################################################
## INICIANDO BitRock: Administrador de procesos iniciales.
#############################################################

# Información del Kernel.
include KERNEL . 'Info.php';

# Iniciando BitRock (Ayudante)...
require KERNEL . 'Helpers' . DS . 'Bit.php';
new Bit;

#############################################################
## INICIANDO INSTANCIAS DEL SISTEMA
#############################################################

# Preparamos los códigos de error.
new Codes;
# Preparamos el sistema de lenguajes.
new Lang;
# Preparamos y verificamos el archivo de configuración.
new Setup;

# Realizamos la conexión al servidor Memcache (Si la hay)
new Mem;
# Realizamos la conexión al servidor SQL (MySQL, SQLite 3 o PostgreSQL)
new BaseSQL;

# Establecemos la configuración del sitio en $site
new Site;
# Verificamos si el visitante es nuevo.
Site::Visit();

# Restauramos la información de un $_POST perdido (por un error)
Client::GetPost();
# Verificamos la carga del servidor.
Bit::CheckLoad();

#############################################################
## FUNCIONES DE ACCESO DIRECTO
#############################################################

/**
 * Reemplaza constantes en una cadena.
 * @param string $str   Cadena
 * @param string $other Otras constantes/valores a reemplazar.
 */
function Keys($str, $other = '')
{
	# Obtenemos las constantes.
	$params = get_defined_constants(true);
	$params = $params['user'];

	# Agregar más valores a la lista si los hay.
	if( is_array($other) )
	{
		foreach( $other as $param => $value )
			$params[$param] = $value;
	}

	# Remplazar cada una de los accesos directos encontrados.
	foreach( $params as $param => $value )
		$str = str_ireplace('{' . $param . '}', $value, $str);

	return $str;
}

/**
 * [Query description]
 * @param [type] $table [description]
 */
function Query($table)
{
	return new Query($table);
}

/**
 * Ejecutar una consulta.
 * @param  string  $query Consulta.
 * @param  boolean $cache ¿Guardar en caché?
 * @param  boolean $free  ¿Liberar memoria al finalizar?
 * @return resource       Recurso de la consulta.
 */
function q($query, $cache = false, $free = false)
{
	return SQL::query($query, $cache, $free);
}

/**
 * Insertar datos en una tabla.
 * @param string $table Tabla
 * @param array $data  	Datos
 * @return resource 	Recurso de la consulta.
 */
function Insert($table, $data)
{
	return SQL::Insert($table, $data);
}

/**
 * Actualizar los datos de una tabla.
 * @param string  $table   	Tabla
 * @param array  $updates 	Datos a actualizar.
 * @param array  $where   	Condiciones a cumplir.
 * @param integer $limit   	Limite de columnas a actualizar.
 * @return resource 		Recurso de la consulta.
 */
function Update($table, $updates, $where = '', $limit = 1)
{
	return SQL::Update($table, $updates, $where, $limit);
}

/**
 * Obtener el numero de filas de una consulta.
 * @param string $query Consulta O recurso de la consulta. Si
 * se deja vacio se usará el recurso de la última consulta hecha.
 */
function Rows($query = '')
{
	return SQL::Rows($query);
}

/**
 * Obtener los valores de una consulta.
 * @param string $query Consulta O recurso de la consulta. Si
 * se deja vacio se usará el recurso de la última consulta hecha.
 */
function Assoc($query = '')
{
	return SQL::Assoc($query);
}

/**
 * Obtener un valor especifico de una consulta.
 * @param string $query Consulta.
 * @return string Valor o array con los valores.
 */
function Get($query)
{
	return SQL::Get($query);
}

/**
 * Obtener los valores de una consulta.
 * @param string $query Consulta O recurso de la consulta. Si
 * se deja vacio se usará el recurso de la última consulta hecha.
 */
function Object($query = '')
{
	return SQL::Object($query);
}

/**
 * Obtener los valores de una consulta.
 * @param string $query Consulta O recurso de la consulta. Si
 * se deja vacio se usará el recurso de la última consulta hecha.
 */
function GetArray($query = '')
{
	return SQL::GetArray($query);
}

/**
 * Libera la memoria de la última consulta realizada.
 * @param resource $query Recurso de la última consulta.
 */
function Free($query = '')
{
	return SQL::Free($query);
}

/**
 * Obtener la última ID insertada en la base de datos.
 */
function LastID()
{
	return SQL::LastID();
}

###############################################################
## Aplicar filtro anti SQL Inyection a una cadena.
## - $str: 				Cadena.
## - $html (bool): 		¿Aplicar filtro anti XSS Inyection?
## - $from (charset): 	Codificación original de la cadena.
## - $to (charset): 	Codificación deseada para la cadena.
###############################################################
function Filter($str, $html = true, $from = '', $to = '')
{
	return Core::Filter($str, $html, $from, $to);
}
function _f($str, $html = true, $from = '', $to = '')
{
	return Core::Filter($str, $html, $from, $to);
}

###############################################################
## Aplicar filtro anti XSS Inyection a una cadena.
## - $str: 				Cadena a filtrar.
## - $from (charset): 	Codificación original de la cadena.
## - $to (charset): 	Codificación deseada para la cadena.
###############################################################
function Clean($str, $from = '', $to = '')
{
	return Core::Clean($str, $from, $to);
}
function _c($str, $from = '', $to = '')
{
	return Core::Clean($str, $from, $to);
}

###############################################################
## Búsqueda de un término en una cadena.
## - $str: 						Cadena donde buscar.
## - $words (cadena/array):		Término(s) a buscar.
## - $lower (bool): 			¿Convertir todo a minusculas?
###############################################################
function Contains($str, $words, $lower = false)
{
	return Core::Contains($str, $words, $lower);
}

###############################################################
## Obtener la fecha actual en cadena.
## - $hour (bool): 		¿Incluir hora?
## - $type (1, 2, 3): 	Formato.
###############################################################
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

###############################################################
## Calcular tiempo restante/faltante.
## - $date: Tiempo Unix o cadena de tiempo.
## - $num: 	Devolver solo el numero y tipo.
###############################################################
function CalcTime($date, $num = false)
{
	return Date::CalculateTime($date, $num);
}

###############################################################
## Imprimir de manera más visual una matriz/objeto (array/object)
## - $data (array/obect): Matriz/Objeto.
###############################################################
function _r($data)
{
	if( !is_array($data) AND !is_object($data) )
		return false;

	echo '<pre>';
	print_r($data);
	echo '</pre>';
}

###############################################################
## Traducir una cadena.
## - $data: 		Cadena.
## - $section: 		Nombre de la sección a utilizar.
## - $lang: 		Lenguaje a traducir.
## - $tpl (bool): 	¿Preparado para la traducción en tiempo real?
###############################################################
function _l($data, $lang = '', $section = '', $live = false)
{
	return Lang::Translate($data, $lang, $section, $live);
}

###############################################################
## Establecer variable de plantilla.
## - $param (string, array): 	Nombre de la variable.
## - $value: 					Valor.
###############################################################
function _t($param, $value = '')
{
	return Tpl::Set($param, $value);
}

function __($str)
{
	if ( is_array($str) )
	{
		foreach ( $str as $key => $value )
			$str[$key] = new Str($value);

		return $str;
	}

	if ( is_string($str) )
		return new Str($str);

	return $str;
}

function __f($str, $clean = false)
{
	if ( is_array($str) )
	{
		foreach ( $str as $key => $value )
		{
			$str[$key] = new Str($value);

			if ( !$clean )
				$str[$key]->filter();
			else
				$str[$key]->clean();
		}

		return $str;
	}

	if ( is_string($str) )
	{
		$str = new Str($str);

		if ( !$clean )
			$str->filter();
		else
			$str->clean();

		return $str;
	}

	return $str;
}

################################################################
## Guardar un log.
## - $message: 		Mensaje a guardar.
## - $type: 		Tipo del log.
##
## 		info: 		Informativo.
## 		warning: 	Alerta
## 		error: 		Error
##		sql: 		Informativo del servidor MySQL/SQLite
## 		memcache: 	Informativo del servidor Memcache
################################################################
function Reg($message, $type = LOG_INFO)
{
	Bit::Log($message, $type);
}

################################################################
## Borrar todas las cookies.
################################################################
function cookie_destroy()
{
	foreach( $_COOKIE as $param )
	{
		setcookie($param, '', -1000);
		unset($_COOKIE[$param]);
	}
}

################################################################
## Establecer una sesión.
## - $key: 		Nombre de la sesión.
## - $value: 	Valor.
################################################################
function _SESSION($key, $value = '')
{
	return Core::SESSION($key, $value);
}

################################################################
## Eliminar una sesión.
## - $key: 	Nombre de la sesión.
################################################################
function _DELSESSION($param)
{
	return Core::DELSESSION($param);
}

################################################################
## Establecer una cookie.
## - $key: 				Nombre de la cookie.
## - $value: 			Valor.
## - $duration: 		Duración en segundos.
## - $path: 			Ubicación donde podrá ser válida.
## - $domain: 			Dominio donde podrá ser válida.
## - $secure (bool): 	¿Solo válida para HTTPS?
## - $imgod (bool): 	Si se activa, el navegador web no podrá acceder a la cookie. (Como por ejemplo en JavaScript)
################################################################
function _COOKIE($key, $value = '', $duration = '', $path = '', $domain = '', $secure = false, $imgod = false)
{
	return Core::COOKIE($key, $value, $duration, $path, $domain, $secure, $imgod);
}

################################################################
## Eliminar una cookie.
## - $key: 	Nombre de la cookie.
## - $path: 	Ubicación donde es válida.
## - $domain: 	Dominio donde es válida.
################################################################
function _DELCOOKIE($key, $path = '', $domain = '')
{
	return Core::DELCOOKIE($key, $path, $domain);
}

################################################################
## Guardar un objeto en Memcache o en $_SESSION
## - $key: 		Nombre del objeto.
## - $value: 	Valor.
################################################################
function _CACHE($key, $value = '')
{
	return Core::CACHE($key, $value);
}

################################################################
## Eliminar un objeto de Memcache o $_SESSION
## - $key: Nombre del objeto.
################################################################
function _DELCACHE($param)
{
	return Core::DELCACHE($param);
}

#############################################################
## RECUPERACIÓN INTELIGENTE
#############################################################

Bit::SmartBackup();

#############################################################
## DEFINICIÓN DE GLOBALES
#############################################################

# Nombre de la aplicación.
define('SITE_NAME', $site['site_name']);

# Ruta local del Logo.
if( !empty($site['site_logo']) )
	define('LOGO', RESOURCES . '/images/'. $site['site_logo']);

# Motor del navegador web del visitante.
define('ENGINE', 	Client::Get('engine'));
# Navegador web del visitante.
define('BROWSER', 	Client::Get('browser'));
# Sistema operativo del visitante.
define('OS', 		Client::Get('os'));
# Host/DNS del visitante.
define('HOST', 		Client::Get('host'));
# País del visitante.
define('COUNTRY', 	Client::Get('country'));
# Zona horaria del visitante.
define('TIMEZONE', 	Client::Get('timezone'));
# Dominio actual.
define('DOMAIN', 	Core::GetHost(PATH));
# Ruta del RSS
define('RSS', 		$site['site_rss_path']);

# Configurar la zona horaria del visitante como zona horaria a utilizar en PHP.
if( $config['server']['timezone'] == true AND TIMEZONE !== '' )
	date_default_timezone_set(TIMEZONE);

$constants = get_defined_constants(true);
$constants = $constants['user'];

# Establecemos variables de plantilla para las constantes creadas. %PATH%, %RESOURCES%, etc..
Tpl::Set($constants);

# Establecemos variables de configuración de sitio.
Tpl::Set($site);

#############################################################
## SEGURIDAD
#############################################################

# Filtramos los datos de $_GET, $_POST y $_REQUEST (Anti SQL/XSS Inyection)
# y las ponemos en variables más cortas.

$G 	= __f($_GET);
$GC = __f($_GET, true);

$P 	= __f($_POST);
$PC = __f($_POST, true);

$R 	= __f($_REQUEST);
$RC = __f($_REQUEST, true);

$PA = $_POST;
$GA = $_GET;
$RA = $_REQUEST;

# Sospechas de inyección.
/*
foreach ( $_REQUEST as $key => $value )
{
	$value = strtoupper(urldecode($value));

	# Mmmm, al parecer alguien o algo esta intentando poner una consulta en las variables de entrada.
	preg_match("/SELECT ([^<]+) FROM/is", $value, $verify);
	preg_match("/DELETE ([^<]+) FROM/is", $value, $verify2);
	preg_match("/UPDATE FROM/is", $value, $verify3);

	# Si es así, enviarle un correo electrónico al webmaster.
	if( count($verify) !== 0 OR count($verify2) !== 0 OR count($verify3) !== 0 )
		Core::SendWait();
}
*/

# Si el modo seguro esta activado filtrar toda
# información proveniente del usuario y las sesiones.
# Además de eliminar información delicada.
if( $config['security']['enabled'] OR $Kernel['secure'] == true AND $Kernel['secure'] !== false )
{
	$_POST 		= $P;
	$_GET 		= $G;
	$_SESSION 	= _f($_SESSION);

	unset($config['sql']['user'], $config['sql']['password'], $config['security']['hash']);
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