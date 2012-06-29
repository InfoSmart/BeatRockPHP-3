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

if($P['server'] == 'apache')
{
	if($P['max_execution'] < 10 OR !is_numeric($P['max_execution']))
		$error[] = 'Por favor establece un tiempo limite de ejecución válida.';
		
	if($P['memory_limit'] < 5 OR !is_numeric($P['memory_limit']))
		$error[] = 'Por favor establece un limite de memoria válido.';
		
	if($P['max_input'] < 30 OR !is_numeric($P['max_input']))
		$error[] = 'Por favor establece un tiempo limite de subida válido.';
		
	if($P['max_filesize'] < 0 OR !is_numeric($P['max_filesize']))
		$error[] = 'Por favor establece un peso limite de subida válido.';
}

if($P['server'] == 'nginx')
{
	if($P['worker_processes'] < 1 OR !is_numeric($P['worker_processes']))
		$error[] = 'Por favor establece un proceso de trabajo válido.';

	if($P['worker_rlimit_nofile'] < 5000 OR !is_numeric($P['worker_rlimit_nofile']))
		$error[] = 'Por favor establece los un "Descriptores máximo de archivo" válido.';

	if($P['worker_connections'] < 1000 OR !is_numeric($P['worker_connections']))
		$error[] = 'Por favor establece un numero de conexiones entrantes maximas válido.';

	if($P['folder'] !== 'www' AND $P['folder'] !== 'htdocs' AND $P['folder'] !== 'html')
		$error[] = 'Por favor selecciona un directorio donde tienes los archivos de tu web válido.';
}

foreach($P as $param => $value)
{
	$pp = explode('_', $param);

	if($pp[0] == 'error')
		$errors[$pp[1]] = $value;
	if($pp[0] == 'modules')
		$modules[$pp[1]] = $value;
}
		
if(empty($error))
{
	$errorspage = '';
	$cache = '';
	$gzip = '';
	foreach($errors as $param => $value)
	{
		$errs['apache'] .= "ErrorDocument $param \"$value\"\r\n";
		$errs['iis'] .= "		<error statusCode=\"$param\" redirect=\"$value\" />\r\n";
		$errs['nginx'] .= "		error_page $param \"$value\"\r\n";
	}

	if($P['server'] == 'apache')
		$data = file_get_contents('../templates/Htaccess');
	if($P['server'] == 'iis')
		$data = file_get_contents('../templates/Webconfig');
	if($P['server'] == 'nginx')
		$data = file_get_contents('../templates/Nginxconf');

	if($P['server'] == 'apache' OR $P['server'] == 'nginx')
	{
		if($P['modules_cache'] == 'true')
			$cache = file_get_contents('../templates/' . $P['server'] . '/Cache');		
	}

	if($P['modules_gzip'] == 'true')
		$gzip = file_get_contents('../templates/' . $P['server'] . '/Gzip');

	if(!empty($errs))
	{
		$errorspage = file_get_contents('../templates/' . $P['server'] . '/Errors');
		$errorspage .= $errs[$P['server']];

		if($P['server'] == 'iis')
			$errorspage = '<customErrors mode="RemoteOnly" defaultRedirect="/error.php?code=404">\r\n' . $errorspage . '</customErrors>';
	}

	$data = str_ireplace('{config_cache}', $cache, $data);
	$data = str_ireplace('{config_gzip}', $gzip, $data);
	$data = str_ireplace('{config_errors}', $errorspage, $data);

	$P['root'] = str_ireplace('\\', '/', $_POST['root']);
	$P['root'] = substr($P['root'], 0, strlen($P['root']) - 1);

	foreach($P as $param => $value)
		$data = str_ireplace("{" . $param . "}", $value, $data);
	
	if($P['server'] == 'apache')
		$write = file_put_contents("../../.htaccess", $data);
	if($P['server'] == 'iis')
		$write = file_put_contents("../../web.config", $data);
	if($P['server'] == 'nginx')
	{
		$write = file_put_contents("../../nginx.conf", $data);
		copy('../templates/nginx-mime.types', '../../nginx-mime.types');
	}
	
	if(!$write)
		$error[] = "No se ha podido escribir el archivo de configuración para el servidor correctamente, verifica los permisos de la instalación.";
	else
		$result['status'] = 'OK';
}

if(!empty($error))
{
	$result['status'] = 'ERROR';

	foreach($error as $e)
		$result['errors'][] = CleanText($e);
}

echo json_encode($result);
?>