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

define('NO_FOOTER', true);
require('../Init.php');

$result = Array();
$error = Array();

if(empty($P['mysql_host']))
	$error[] = 'El Host para la conexión al servidor MySQL no es válido.';
		
if(empty($P['mysql_name']))
	$error[] = 'El nombre de usuario para la conexión al servidor MySQL no es válido.';
		
if(empty($P['mysql_pass']))
	$error[] = 'Por seguridad, es necesario que escribas una contraseña para la conexión al servidor MySQL.';
		
if(empty($P['mysql_name']))
	$error[] = 'Por favor escribe el nombre de la base de datos.';
	
if(empty($P['site_path']))
	$error[] = 'Por favor escribe la dirección de la aplicación.';
	
if(empty($P['site_resources']))
	$error[] = 'Por favor escribe la dirección de los recursos de tu aplicación.';
	
if(empty($P['site_resources_sys']))
	$error[] = 'Por favor escribe la dirección de los recursos globales para tus aplicaciones.';
	
if($P['security_level'] < 0 OR $P['security_level'] > 5 OR !is_numeric($P['security_level']))
	$error[] = 'Selecciona un sistema de codificación válido.';
	
if(empty($P['security_hash']) OR strlen($P['security_hash']) < 20)
	$error[] = 'Por favor escribe una clave de codificación con más de 20 caracteres.';
	
if(!empty($P['errors_email_to']) AND !isValid($P['errros_email_to']))
	$error[] = 'Por favor escribe un correo electrónico de reportes válido.';	
		
if(empty($error))
	$sql = mysql_connect($P['mysql_host'], $P['mysql_user'], $P['mysql_pass']) or $error[] = 'No hemos podido establecer una conexión con el Servidor MySQL. Asegurese de que los datos para la conexión sean correctos y que el servidor se encuentre disponible.';
		
if(empty($error))
	mysql_query("CREATE DATABASE IF NOT EXISTS $P[mysql_name] CHARACTER SET latin1;") or $error[] = 'No se ha podido crear la base de datos, es probable que el usuario que hayas escrito para la conexión no tenga los permisos necesarios.';
		
if(empty($error))
	mysql_select_db($P['mysql_name']) or $error[] = 'No se ha podido encontrar la base de datos para BeatRock. Este es un error desconocido, elimina los cambios que se hayan realizado e intentalo de nuevo.';
		
if(empty($error))
{
	if($P['nodb'] !== 'true')
		CreateDB($sql);
			
	file_put_contents('../../Kernel/Secret_Hash', $P['security_hash']);
	$config = file_get_contents('../templates/Configuration');

	$P['site_admin'] = $P['site_path'] . '/imgod';

	foreach($P as $param => $value)
		$config = str_ireplace("{" . $param . "}", $value, $config);

	$show_config = CleanText($config);
	$show_config = str_ireplace('<br />', '', $show_config);
		
	if($P['type'] == 'save')
	{			
		$write = file_put_contents('../../Kernel/Configuration.json', $config);
		file_put_contents('../../Kernel/Configuration.Backup.json', $config);
			
		if(!$write)
		{
			$result['status'] = 'OK';
			$result['show'] = $show_config;
		}
		else
			$result['status'] = 'OK';
	}
	else
	{
		$result['status'] = 'OK';
		$result['show'] = $show_config;
	}
}
else
{
	$result['status'] = 'ERROR';

	foreach($error as $e)
		$result['errors'][] = CleanText($e);
}

echo json_encode($result);
?>