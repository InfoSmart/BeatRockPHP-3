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
$database = @file_get_contents('http://beatrock.infosmart.mx/releases/PHP/Updates_2.4.2-' . $Info['version']);
$database = str_ireplace("{DB_PREFIX}", DB_ALIAS, $database);

// Error al obtener el archivo.
if($database == false OR empty($database))
{
	$result['status'] = 'ERROR';
	goto Result;
}

// Backup: DB
$fileName = MySQL::Backup();
copy(BIT . 'Backups' . DS . $fileName, ROOT . $fileName);

$querys = explode(';', trim($database));

foreach($querys as $query)
{
	$query = trim($query);

	if(empty($query))
		continue;

	query($query);
}

/* ARCHIVO DE CONFIGURACIÓN */

// Obteniendo la plantilal del archivo de configuración.
$config_data 	= file_get_contents('../templates/Configuration');
$config_result 	= array();

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

// Fixes
$config_result['errors_email_to'] 		= $config['errors']['email.to'];
$config_result['site_resources_sys'] 	= $config['site']['resources.sys'];
$config_result['mysql_repair_error'] 	= $config['mysql']['repair.error'];

foreach($config_result as $param => $value)
{
	$config_data = str_ireplace('{' . $param . '}', $value, $config_data);
}

//unlink(KERNEL . 'Configuration.php');
file_put_contents(KERNEL . 'Configuration.json', $config_data);

$result['status'] = 'OK';

Result:
{
	Core::theSession('update_status', $result['status']);
	Core::Redirect('/Update/');
}
?>