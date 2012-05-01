<?php
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart © 2011 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

$page['gzip'] = false;
require('../Init.php');

function CheckReady()
{
	if(file_exists('../Kernel/Configuration.php') OR file_exists('./SECURE'))
	{
		if($_SESSION['install']['secure'] !== true)
		{
			header("Location: ./error_ready");
			exit;
		}
	}
}

CheckReady();

$step['error_404'] = "/Kernel/FakeProxy.php?toUrl=aHR0cDovL3Jlc291cmNlcy5pbmZvc21hcnQubXgvZXJyb3JzL25vdF9mb3VuZC5odG1s";
$step['error_403'] = "/Kernel/FakeProxy.php?toUrl=aHR0cDovL3Jlc291cmNlcy5pbmZvc21hcnQubXgvZXJyb3JzL3VuYXV0aG9yaXplZC5odG1s";
$step['error_500'] = "/Kernel/FakeProxy.php?toUrl=aHR0cDovL3Jlc291cmNlcy5pbmZvc21hcnQubXgvZXJyb3JzL2ludGVybmFsX3NlcnZlcl9lcnJvci5odG1ss";
$step['error_503'] = "/Kernel/FakeProxy.php?toUrl=aHR0cDovL3Jlc291cmNlcy5pbmZvc21hcnQubXgvZXJyb3JzL21haW50ZW5hbmNlLmh0bWw=";

$step['max_execution'] = "50";
$step['memory_limit'] = "10";
$step['max_input'] = "50";
$step['max_filesize'] = "10";

if($G['do'] == "save")
{
	foreach($P as $param => $value)
		$step[$param] = $value;
		
	if(empty($step['error_404']) OR empty($step['error_403']) OR empty($step['error_500']))
		$error[] = "Por favor escribe una dirección o mensaje en todos los campos de Documentos de error.";
		
	if($P['max_execution'] < 10 OR !is_numeric($P['max_execution']))
		$error[] = "Por favor establece un tiempo limite de ejecución válida.";
		
	if($P['memory_limit'] < 5 OR !is_numeric($P['memory_limit']))
		$error[] = "Por favor establece un limite de memoria válido.";
		
	if($P['max_input'] < 30 OR !is_numeric($P['max_input']))
		$error[] = "Por favor establece un tiempo limite de subida válido.";
		
	if($P['max_filesize'] < 0 OR !is_numeric($P['max_filesize']))
		$error[] = "Por favor establece un peso limite de subida válido.";
		
	if(empty($error))
	{
		$htaccess = file_get_contents("Htaccess");
		
		foreach($step as $param => $value)
			$htaccess = str_replace("{" . $param . "}", $value, $htaccess);
		
		$write = file_put_contents("../.htaccess", $htaccess);
		
		if(!$write)
			$error[] = "No se ha podido escribir el archivo Htaccess correctamente, verifica los permisos de la instalación.";
		else
			Core::Redirect("/Setup/step4");
	}
}

$page['name'] = "Configuración interna";
require('./Header.php');
?>
<div class="content">
	<p>
		¡Perfecto! Todo ha ido genial, ahora tomese unos minutos para configurar algunas opciones internas para su aplicación. Cabe mencionar que debe tener activado el uso de <b>.htaccess</b> para esto.
	</p>
	
	<p>
		Si lo desea puede omitir este paso y <a href="./step4">continuar</a>.
	</p>
	
	<form action="<?=PATH?>/Setup/step3?do=save" method="POST">
		<section>
			<h2>Documentos de error</h2>
			
			<p>
				<label for="error_404">Error 404:</label>
				<input type="text" name="error_404" id="error_404" value="<?=$step['error_404']?>" placeholder="Error 404 - No encontrado" required autofocus autocomplete="off" />
				
				<span>Escriba la dirección fisica, dirección web o mensaje del error 404. Cuando se intenta acceder a una página no existente.</span>
			</p>
			
			<p>
				<label for="error_403">Error 403:</label>
				<input type="text" name="error_403" id="error_403" value="<?=$step['error_403']?>" placeholder="Error 403 - Acceso no autorizado" required autocomplete="off" />
				
				<span>Escriba la dirección fisica, dirección web o mensaje del error 403. Cuando se intenta acceder a una página sin los permisos necesarios.</span>
			</p>
			
			<p>
				<label for="error_500">Error 500:</label>
				<input type="text" name="error_500" id="error_500" value="<?=$step['error_500']?>" placeholder="Error 500 - Error interno del servidor" required autocomplete="off" />
				
				<span>Escriba la dirección fisica, dirección web o mensaje del error 500. Cuando se produce un error interno del servidor.</span>
			</p>
		</section>
		
		<section>
			<h2>Ejecución</h2>
			
			<p>
				<label for="max_execution">Tiempo limite de ejecución:</label>
				<input type="number" name="max_execution" id="max_execution" value="<?=$step['max_execution']?>" placeholder="80" min="10" required />
				
				<span>Seleccione el limite de tiempo en segundos para la ejecución de la aplicación, en caso de que supere la cantidad seleccionada se mostrará una advertencia de "Tiempo limite excedido" de PHP.</span>
			</p>
			
			<p>
				<label for="memory_limit">Memoria limite de la aplicación:</label>
				<input type="number" name="memory_limit" id="memory_limit" value="<?=$step['memory_limit']?>" placeholder="10" min="5" required />
				
				<span>Seleccione la memoria limite (MB) para la aplicación, en caso de que supere la cantidad seleccionada se mostrará una página de "Sobrecarga"</span>
			</p>
			
			<p>
				<label for="max_input">Tiempo limite de subida:</label>
				<input type="number" name="max_input" id="max_input" value="<?=$step['max_input']?>" placeholder="100" min="30" required />
				
				<span>Seleccione el limite de tiempo en segundos para la subida de archivos.</span>
			</p>
			
			<p>
				<label for="max_filesize">Peso limite de subida:</label>
				<input type="number" name="max_filesize" id="max_filesize" value="<?=$step['max_filesize']?>" placeholder="50" min="0" required />
				
				<span>Seleccione el peso limite (MB) para la subida de archivos.</span>
			</p>
		</section>
		
		<p>
			<input type="submit" value="Guardar y continuar" class="ibtn" />
		</p>
	</form>
</div>
<?php require('Footer.php')?>