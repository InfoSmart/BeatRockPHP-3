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

require('../../Init.php');
require('../Info.php');

$result = array();

/* BASE DE DATOS */

// Obteniendo archivo de cambios en la DB.
$database = @file_get_contents('http://beatrock.infosmart.mx/releases/PHP/Updates_2.4.1-' . $Info['version']);
$database = str_ireplace("{DB_ALIAS}", DB_ALIAS, $database);

// Error al obtener el archivo.
if($database == false OR empty($database))
{
	$result['status'] = 'ERROR';
	goto Result;
}

// Backup: DB
$fileName = MySQL::Backup();
//copy(IT . 'Backups' . DS . $fileName, ROOT . $fileName);

$querys = explode(';', trim($database));

foreach($querys as $query)
{
	$query = trim($query);

	if(empty($query))
		continue;

	query($query);
}

/* BASE DE DATOS - CAMBIOS 2.4.2 */

Insert('site_config', Array(
	'var' => 'site_compress',
	'result' => $config['server']['compression']
));

Insert('site_config', Array(
	'var' => 'site_recovery',
	'result' => $config['server']['backup']
));

Insert('site_config', Array(
	'var' => 'cpu_limit',
	'result' => $config['server']['limit_load']
));

Insert('site_config', Array(
	'var' => 'apache_limit',
	'result' => $config['server']['limit']
));

Insert('site_config', Array(
	'var' => 'site_status',
	'result' => $site['site_state']
));

Insert('site_config', Array(
	'var' => 'site_header_javascript',
	'result' => $site['site_analytics']
));

Insert('site_config', Array(
	'var' => 'site_optimized_javascript',
	'result' => $site['site_bottom_javascript']
));

query("DELETE FROM {DA}site_config WHERE var = 'site_state' LIMIT 1");
query("DELETE FROM {DA}site_config WHERE var = 'site_analytics' LIMIT 1");
query("DELETE FROM {DA}site_config WHERE var = 'site_bottom_javascript' LIMIT 1");
query("DELETE FROM {DA}site_config WHERE var = 'site_author' LIMIT 1");

MySQL::Engine('MYISAM', array(
	'{DA}site_cache',
	'{DA}site_countrys',
	'{DA}site_maps',
	'{DA}site_news',
	'{DA}site_timers',
	'{DA}site_translate',
	'{DA}wordsfilter'
));

MySQL::Engine('INNODB', array(
	'{DA}site_errors',
	'{DA}site_logs',
	'{DA}site_visits'
));

/* ARCHIVO DE CONFIGURACIÓN */

// Obteniendo la plantilal del archivo de configuración.
$config_data = file_get_contents('../templates/Configuration');
$config_result = array();

// Backup: Configuration
copy(KERNEL . 'Configuration.php', KERNEL . 'Configuration.Update.Backup.php');

foreach($config as $param => $values)
{
	foreach($values as $par => $val)
	{
		if($config[$param][$par] == true AND is_bool($config[$param][$par]))
			$val = 'true';

		if($config[$param][$par] == null AND is_bool($config[$param][$par]))
			$val = 'null';

		if($config[$param][$par] == false AND is_bool($config[$param][$par]))
			$val = 'false';

		if($par == 'ssl' AND empty($val))
			$val = 'null';

		$val = str_ireplace('.', '_', $val);
		$config_result[$param . '_' . $par] = $val;
	}
}

$config_result['errors_email_to'] = $config['errors']['email.to'];
$config_result['site_resources_sys'] = $config['site']['resources.sys'];
$config_result['mysql_repair_error'] = $config['mysql']['repair.error'];

foreach($config_result as $param => $value)
{
	$config_data = str_ireplace('{' . $param . '}', $value, $config_data);
}

unlink(KERNEL . 'Configuration.php');
file_put_contents(KERNEL . 'Configuration.php', $config_data);

$result['status'] = 'OK';

Result:
{
	echo json_encode($result);
}
?>