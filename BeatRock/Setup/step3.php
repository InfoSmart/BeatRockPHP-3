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

$page['gzip'] = false;
require('../Init.php');

if(file_exists('../Kernel/Configuration.php') OR file_exists('./SECURE'))
{
	if($_SESSION['install']['secure'] !== true)
	{
		header('Location: ./error_ready.php');
		exit;
	}
}

$step['error_404'] = '/error.php?code=404';
$step['error_403'] = '/error.php?code=403';
$step['error_401'] = '/error.php?code=401';
$step['error_500'] = '/error.php?code=500';
$step['error_503'] = '/error.php?code=503';

$step['max_execution'] = '50';
$step['memory_limit'] = '35';
$step['max_input'] = '50';
$step['max_filesize'] = '10';

$step['port'] = '80';
$step['worker_processes'] = '1';
$step['worker_rlimit_nofile'] = '8192';
$step['worker_connections'] = '1024';

$page['name'] = 'Configuración interna';
require('./Header.php');
?>
<div class="pre">
	<section class="left">
		<h2>Invitemos a su servidor</h2>
		<cite>Puede configurar algunos aspectos de su servidor en esta aplicación.</cite>

		<p>
			¿Nuevos documentos de errores? ¿Más memoria o más tiempo de subida de archivos? 
		</p>

		<p>
			Configure ciertos aspectos de su servidor web en esta página, desde la memoria de ejecución hasta el uso de la compresión GZIP en todos los archivos.
		</p>

		<p>
			Antes que nada ¿Qué tipo de servidor web esta utilizando?
		</p>

		<p>
			<input type="radio" name="server" value="apache" checked form="process-form" /> Apache (.htaccess)
		</p>

		<p>
			<input type="radio" name="server" value="iis" form="process-form" /> IIS (web.config)
		</p>

		<p>
			<input type="radio" name="server" value="nginx" form="process-form" /> Nginx (nginx.conf)
		</p>
	</section>

	<figure class="right">
		<img src="<?=RESOURCES_INS?>/system/setup/images/step3.png" />
	</figure>
</div>

<div class="box-error">
	Por favor resuelve los siguientes problemas:
	<div id="errors"></div>
</div>

<div class="content">	
	<form id="process-form">
		<details>
			<summary>Documentos de error</summary>

			<div class="c1">			
				<p>
					<label for="error_404">Error 404:</label>
					<input type="text" name="error_404" id="error_404" value="<?=$step['error_404']?>" placeholder="Error 404 - No encontrado" autofocus autocomplete="off" />
					
					<span>Escriba la dirección física, dirección web o mensaje del error 404. Cuando se intenta acceder a una página no existente.</span>
				</p>
				
				<p>
					<label for="error_401">Error 401:</label>
					<input type="text" name="error_401" id="error_401" value="<?=$step['error_401']?>" placeholder="Error 401 - Autenticación necesaria" autocomplete="off" />
					
					<span>Escriba la dirección física, dirección web o mensaje del error 401. Cuando es necesaria una autenticación en la página.</span>
				</p>

				<p>
					<label for="error_503">Error 503:</label>
					<input type="text" name="error_503" id="error_503" value="<?=$step['error_503']?>" placeholder="Error 503 - Servicio temporalmente no disponible" autocomplete="off" />
					
					<span>Escriba la dirección física, dirección web o mensaje del error 503. Cuando la aplicación no se encuentra disponible o se encuentra en mantenimiento.</span>
				</p>
			</div>

			<div class="c2">
				<p>
					<label for="error_403">Error 403:</label>
					<input type="text" name="error_403" id="error_403" value="<?=$step['error_403']?>" placeholder="Error 403 - Acceso no autorizado" autocomplete="off" />
					
					<span>Escriba la dirección física, dirección web o mensaje del error 403. Cuando se intenta acceder a una página sin los permisos necesarios.</span>
				</p>

				<p>
					<label for="error_500">Error 500:</label>
					<input type="text" name="error_500" id="error_500" value="<?=$step['error_500']?>" placeholder="Error 500 - Error interno del servidor" autocomplete="off" />
					
					<span>Escriba la dirección física, dirección web o mensaje del error 500. Cuando se produce un error interno del servidor.</span>
				</p>
			</div>
		</details>

		<details>
			<summary>Módulos</summary>

			<div class="c1">
				<p data-apache="true" data-nginx>
					<input type="checkbox" name="modules_cache" value="true" /> Utilizar "caché optimizada".
					<span>Permite la configuración recomendada de Caché en los archivos de la aplicación.</span>
				</p>
			</div>

			<div class="c2">
				<p data-apache="true" data-iis data-nginx>
					<input type="checkbox" name="modules_gzip" value="true" checked /> Utilizar GZIP en archivos web.
					<span>Permite usar la compresión GZIP fuera de los archivos PHP de BeatRock.</span>
				</p>
			</div>
		</details>
		
		<details data-apache data-required>
			<summary>Configuración PHP</summary>

			<div class="c1">			
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
			</div>

			<div class="c2">			
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
			</div>
		</details>

		<details hidden data-nginx data-required>
			<summary>Configuración Nginx</summary>

			<input type="hidden" name="root" id="root" value="<?=ROOT?>" />
			<input type="hidden" name="server_name" id="server_name" value="localhost" />

			<div class="c1">
				<p>
					<label for="port">Puerto:</label>
					<input type="number" name="port" id="port" value="<?=$step['port']?>" placeholder="80" min="10" max="65534" />
				</p>

				<p>
					<label for="worker_processes">Procesos de trabajo:</label>
					<input type="number" name="worker_processes" id="worker_processes" value="<?=$step['worker_processes']?>" placeholder="1" min="1" />
				</p>

				<p>
					<label for="worker_rlimit_nofile">Descriptores máximo de archivo:</label>
					<input type="number" name="worker_rlimit_nofile" id="worker_rlimit_nofile" value="<?=$step['worker_rlimit_nofile']?>" placeholder="8192" min="5000" />
				</p>
			</div>

			<div class="c2">
				<p>
					<label for="worker_connections">Conexiones entrantes maximas:</label>
					<input type="number" name="worker_connections" id="worker_connections" value="<?=$step['worker_connections']?>" placeholder="8000" min="1000" />
				</p>

				<p>
					<label for="folder">Directorio de archivos web:</label>
					<select name="folder" id="folder" class="btn">
						<option value="www">www</option>
						<option value="htdocs">htdocs</option>
						<option value="html">html</option>
					</select>
				</p>
			</div>
		</details>
		
		<p>
			<input type="submit" value="Guardar y continuar" class="ibtn" id="save_step3" />
		</p>
	</form>

	<section id="complete" hidden>
		<h1>¡De maravillas!</h1>

		<p>
			El archivo de configuración de su servidor web ha sido creado con los ajustes que ha especificado, por favor revisa:
		</p>

		<ul>
			<li>En caso de usar Apache, que el archivo <b>"<?=ROOT?>.htaccess"</b> exista.</li>
			<li>En caso de usar IIS, que el archivo <b>"<?=ROOT?>web.config"</b> exista.</li>
			<li>En caso de usar Nginx, que los archivos <b>"<?=ROOT?>nginx.conf"</b> y <b>"<?=ROOT?>nginx-mime.types"</b> existan.</li>
		</ul>

		<p>
			Si lo deseas <b>(y usas Nginx)</b> puedes usar el archivo creado como nuevo archivo de configuración para el servidor web.
		</p>

		<p>
			<a href="./step4.php" class="ibtn iblue">Continuar</a>
		</p>
	</section>
</div>
<?php require('Footer.php')?>