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

require '../Init.php';
require '../../root.php';

$error 		= array();
$errors 	= array();
$extensions = array();

if($P['webserver'] == 'apache')
{
	if($P['max_execution'] < 10 OR !is_numeric($P['max_execution']))
		$error[] = 'Por favor establece un tiempo limite de carga válida.';
		
	if($P['memory_limit'] < 5 OR !is_numeric($P['memory_limit']))
		$error[] = 'Por favor establece un limite de memoria válido.';
		
	if($P['max_input'] < 30 OR !is_numeric($P['max_input']))
		$error[] = 'Por favor establece un tiempo limite de subida válido.';
		
	if($P['max_filesize'] < 0 OR !is_numeric($P['max_filesize']))
		$error[] = 'Por favor establece un peso limite de subida válido.';
}

if($P['webserver'] == 'nginx')
{
	if($P['worker_processes'] < 1 OR !is_numeric($P['worker_processes']))
		$error[] = 'Por favor establece un proceso de trabajo válido.';

	if($P['worker_rlimit_nofile'] < 5000 OR !is_numeric($P['worker_rlimit_nofile']))
		$error[] = 'Por favor establece los un "Descriptores máximo de archivo" válido.';

	if($P['worker_connections'] < 1000 OR !is_numeric($P['worker_connections']))
		$error[] = 'Por favor establece un numero de conexiones entrantes maximas válido.';

	if($P['folder'] !== 'www' AND $P['folder'] !== 'htdocs' AND $P['folder'] !== 'html' AND $P['folder'] !== 'public_html')
		$error[] = 'Por favor selecciona el directorio donde tienes los archivos de tu web.';
}

foreach($P as $key => $value)
{
	$pp = explode('_', $key);

	if($pp[0] == 'error')
		$errors[$pp[1]] 	= $value;

	if($pp[0] == 'ext')
		$extensions[$pp[1]] = $value;
}

if(empty($error))
{
	$errors_page 	= '';
	$cache_page 	= '';
	$gzip_page		= '';

	$errs 			= array();

	foreach($errors as $key => $value)
	{
		$errs['apache'] .= "ErrorDocument $key \"$value\"\r\n";
		$errs['iis'] 	.= "	<error statusCode=\"$key\" redirect=\"$value\" />\r\n";
		$errs['nginx']	.= "	error_page $key \"$value\"\r\n";
	}

	if($P['webserver'] == 'apache')
		$data = file_get_contents('../templates/Htaccess');
	if($P['webserver'] == 'iis')
		$data = file_get_contents('../templates/Webconfig');
	if($P['webserver'] == 'nginx')
		$data = file_get_contents('../templates/Nginxconf');

	if($P['webserver'] == 'apache' OR $P['webserver'] == 'nginx')
	{
		if($P['ext_cache'] == 'true')
			$cache_page = file_get_contents('../templates/' . $P['webserver'] . '/Cache');
	}

	if($P['ext_gzip'] == 'true')
		$gzip_page = file_get_contents('../templates/' . $P['webserver'] . '/Gzip');

	if(!empty($errrs))
	{
		$errors_page 	= file_get_contents('../templates/' . $P['webserver'] . '/Errors');
		$errors_page 	.= $errs[$P['webserver']];

		if($P['webserver'] == 'iis')
			$errors_page = '<customErrors mode="RemoteOnly" defaultRedirect="/error.php?code=404">' . $errors_page . '</customErrors>';
	}

	$data = str_ireplace('{config_cache}', $cache_page, $data);
	$data = str_ireplace('{config_gzip}', $gzip_page, $data);
	$data = str_ireplace('{config_errors}', $errors_page, $data);

	$P['charset'] = CHARSET;

	foreach($P as $key => $value)
		$data = str_ireplace('{' . $key . '}', $value, $data);

	if($P['webserver'] == 'apache')
		$write = file_put_contents('../../.htaccess', $data);
	if($P['webserver'] == 'iis')
		$write = file_put_contents('../../web.config', $data);

	if($P['webserver'] == 'nginx')
	{
		$write = file_put_contents('../../nginx.conf', $data);
		copy('../templates/nginx-mime.types', '../../nginx-mime.types');
	}

	if(!$write)
		$error[] = 'No hemos podido escribir el archivo de configuración para el servidor. Asegurese de que la instalación cuenta con los permisos necesarios.';
	else
		header('Location: ../step4.php');
}

if(!empty($error))
{
	$message = '';

	foreach($error as $e)
		$message = '<li>' . $e . '</li>';

	$_SESSION['setup_errors'] 	= $message;
	$_SESSION['setup_info']		= $P;

	header('Location: ../step3.php');
}
?>