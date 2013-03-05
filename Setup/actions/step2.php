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

if(empty($_POST))
{
	$result['code'] 	= 'ERROR';
	$result['message']	= 'Ha ocurrido un error al intentar envíar la información, intentalo nuevamente.';
	goto Result;
}

$error = array();

if($_POST['sql_type'] !== 'mysql' AND $_POST['sql_type'] !== 'sqlite')
	$eror[] = 'Selecciona un tipo de servidor SQL válido.';

if($_POST['sql_type'] == 'mysql')
{
	if(empty($_POST['sql_host']))
		$error[] = 'El Host/Dirección IP para la conexión al servidor MySQL no debe estar en blanco.';

	if(empty($_POST['sql_user']))
		$error[] = 'El nombre de usuario para la conexión al servidor MySQL no debe estar en blanco.';

	if(empty($_POST['sql_pass']))
		$error[] = 'La contraseña para la conexión al servidor MySQL no debe estar en blanco.';

	if(empty($_POST['sql_name']))
		$error[] = 'El nombre de la base de datos no debe estar en blanco.';
}

if($_POST['sql_type'] == 'sqlite')
{
	if(empty($_POST['sql_lite_name']))
		$error[] = 'El nombre de la base de datos no debe estar en blanco.';
}

if(empty($_POST['site_path']))
	$error[] = 'La ubicación de la aplicación no debe estar en blanco.';

if(empty($_POST['site_resources']))
	$error[] = 'La ubicación de los recursos de la aplicación no debe estar en blanco.';

if(empty($_POST['site_resources_global']))
	$error[] = 'La ubicación de los recursos globales no debe estar en blanco.';

if($_POST['security_level'] < 0 OR $_POST['security_level'] > 5 OR !is_numeric($_POST['security_level']))
	$error[] = 'La clave de encriptación no debe estar en blanco.';

if(!empty($_POST['errors_email_reports']) AND !Valid($_POST['errors_email_reports']))
	$error[] = 'Escribe un correo electrónico de reportes válido.';

if($_POST['sql_type'] == 'mysql')
{
	if(empty($error))
		$mysql = new MySQLi($_POST['sql_host'], $_POST['sql_user'], $_POST['sql_pass'], '', $_POST['sql_port']);

	if($mysql->connect_error)
		$error[] = 'No se ha podido establecer una conexión con el servidor MySQL. Asegurese de que los datos para la conexión son correctos - Error: ' . $mysql->connect_error;

	if(empty($error))
		$mysql->query('CREATE DATABASE IF NOT EXISTS ' . $_POST['sql_name'] . ' CHARACTER SET '.str_replace('-', '', CHARSET).';') or $error[] = 'La conexión al servidor MySQL fue éxitosa, sin embargo no hemos podido crear la base de datos. Asegurese de que el usuario que ha proporcionado tiene los permisos necesarios. - Error:' . $mysql->error;

	if(empty($error))
		$mysql->select_db($_POST['sql_name']) or $error[] = 'Al parecer PHP no ha podido seleccionar la base de datos del servidor MySQL. Debido a que este es un error raro (Susupone que no era posible llegar aquí) le recomendamos verificar su instalación de PHP y solucionar cualquier problema con el servidor MySQL. - Error:' . $mysql->error;
}

if(empty($error))
{
	

	if($_POST['sql_type'] == 'mysql')
		$db 				= CreateDB($mysql);
	else
	{
		$dbname 			= ROOT . 'App' . DS . $_POST['sql_lite_name'] . '.sqlite';
		$_POST['sql_name'] 	= $dbname;

		$db 				= CreateDBLite($dbname);
		$_POST['sql_host'] 	= '';
	}

	if($db !== true AND $_POST['sql_type'] == 'mysql' OR $db == false AND $_POST['sql_type'] == 'sqlite')
	{
		$result['code'] 	= 'ERROR';
		$result['message'] 	= 'Ha ocurrido un problema al intentar importar la base de datos. Asegurese de haber descargado BeatRock completamente e intentelo nuevamente. - Error: ' . $db;

		goto Result;
	}

	file_put_contents('../../App/SECRET', $_POST['security_hash']);
	$config = file_get_contents('../templates/Configuration');

	foreach($_POST as $key => $value)
		$config = str_ireplace('{' . $key . '}', $value, $config);

	$write = file_put_contents('../../App/Configuration.php', $config);
	file_put_contents('../../App/Configuration.Backup.php', $config);

	if(!$write)
	{
		$result['code'] 	= 'ERROR';
		$result['message'] 	= 'Ha ocurrido un problema al intentar escribir el archivo de configuración. Asegurese de que tiene permisos de escritura en la carpeta /App/';
	}
	else
		$result['code'] 	= 'OK';
}
else
{
	$result['code'] 	= 'ERROR';
	$result['message'] 	= '';

	foreach($error as $e)
		$result['message'] .= '<li>' . _c($e) . '</li>';
}

Result:
echo json_encode($result);
?>