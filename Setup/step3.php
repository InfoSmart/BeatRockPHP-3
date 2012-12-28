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

require 'Init.php';

$page = 'step3';

if($_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest')
	require 'content/header.php';

$step['error_404'] = '/error.php?code=404';
$step['error_403'] = '/error.php?code=403';
$step['error_401'] = '/error.php?code=401';
$step['error_500'] = '/error.php?code=500';
$step['error_503'] = '/error.php?code=503';

$step['max_execution'] 	= '50';
$step['memory_limit'] 	= '35';
$step['max_input'] 		= '50';
$step['max_filesize'] 	= '10';

$step['port'] 					= '80';
$step['worker_processes'] 		= '1';
$step['worker_rlimit_nofile'] 	= '8192';
$step['worker_connections'] 	= '1024';

if(!empty($_SESSION['setup_info']))
{
	foreach($_SESSION['setup_info'] as $key => $value)
		$step[$key] = $value;

	unset($_SESSION['setup_info']);
}

$errors = $_SESSION['setup_errors'];

if(!empty($errors))
	unset($_SESSION['setup_errors']);
?>
<div class="info">
	<div class="wrapper">
		<div class="left">
			<h2>Servidor</h2>

			<p>
				¡El archivo de configuración se ha creado con éxito! Ahora puede configurar que BeatRock pueda reemplazar algunos parámetros de su servidor web.
			</p>

			<p>
				Antes responda una sencilla pregunta ¿Qué servidor web usa?
			</p>

			<section>
				<p>
					<input type="radio" name="webserver" id="webserver" value="apache" checked form="form-step" /> Apache
				</p>

				<p>
					<input type="radio" name="webserver" id="webserver" value="iis" form="form-step" /> IIS
				</p>

				<p>
					<input type="radio" name="webserver" id="webserver" value="nginx" form="form-step" /> Nginx
				</p>
			</section>

			<p>
				<a data-to="step4" class="ibtn">Saltar paso</a>
			</p>
		</div>

		<figure>
			<img src="<?=RESOURCES_INS?>/systemv2/setup/images/step3.png" />
		</figure>
	</div>
</div>

<div class="wrapper">
<div class="content">
	<div class="box-error <? if(!empty($errors)) { ?>block<? } ?>" id="error">
		<?=$errors?>
	</div>

	<form action="./actions/step3.php" method="POST" id="form-step">		
		<section>
			<div class="col1">
				<h3>Páginas de error</h3>

				<p>
					<label>Error 404</label>
					<input type="text" name="error_404" value="<?=$step['error_404']?>" placeholder="Error 404 - No encontrado" />
					<span class="h">Dirección web, en el disco o mensaje a mostrar para el error 404.</span>
				</p>

				<p>
					<label>Error 401</label>
					<input type="text" name="error_401" value="<?=$step['error_401']?>" placeholder="Error 401 - Autenticación necesaria" />
					<span class="h">Dirección web, en el disco o mensaje a mostrar para el error 401.</span>
				</p>

				<p>
					<label>Error 403</label>
					<input type="text" name="error_403" value="<?=$step['error_403']?>" placeholder="Error 403 - Acceso no autorizado" />
					<span class="h">Dirección web, en el disco o mensaje a mostrar para el error 403.</span>
				</p>

				<p>
					<label>Error 500</label>
					<input type="text" name="error_500" value="<?=$step['error_500']?>" placeholder="Error 500 - Error interno del servidor" />
					<span class="h">Dirección web, en el disco o mensaje a mostrar para el error 500.</span>
				</p>

				<p>
					<label>Error 503</label>
					<input type="text" name="error_503" value="<?=$step['error_503']?>" placeholder="Error 503 - Servicio temporalmente no disponible" />
					<span class="h">Dirección web, en el disco o mensaje a mostrar para el error 503.</span>
				</p>
			</div>

			<div class="col2">
				<section>
					<p>
						Puede definir páginas de error personalizadas para mostrar en su aplicación.
					</p>
				</section>
			</div>
		</section>

		<section>
			<div class="col1">
				<h3>Extensiones</h3>

				<p data-apache="true" data-iis="false" data-nginx="true">
					<input type="checkbox" name="ext_cache" id="ext_cache" value="true" /> Utilizar caché
					<span class="h">Permitir usar una configuración recomendada de caché para ciertos archivos.</span>
				</p>

				<p data-apache="true" data-iis="true" data-nginx="true">
					<input type="checkbox" name="ext_gzip" id="ext_gzip" value="true" /> Utilizar compresión Gzip
					<span class="h">Permitir usar la compresión GZIP en ciertos archivos.</span>
				</p>
			</div>

			<div class="col2">
				<section>
					<p>
						"Utilizar caché" permite a su servidor establecer una configuración especial de guardado para los archivos más importantes/pesados de su aplicación entre los que se encuentran los archivos CSS y JavaScript.
					</p>

					<p>
						"Utilizar compresión Gzip" permite a su servidor comprimir los archivos más importantes/pesados de su aplicación entre los que se encuentran los archivos CSS y JavaScript.
					</p>

					<p>
						Tenga en cuenta que la compresión Gzip del paso anterior solo aplicaba para los archivos PHP y HTML de su aplicación.
					</p>
				</section>
			</div>
		</section>

		<section data-apache="true" data-iis="false" data-nginx="false" data-required>
			<div class="col1">
				<h3>Configuración de PHP</h3>

				<p>
					<label>Tiempo limite de carga</label>
					<input type="number" name="max_execution" value="<?=$step['max_execution']?>" placeholder="80" min="10" required />

					<span class="h">Seleccione el tiempo limite en segundos para cargar una página de su aplicación.</span>
				</p>

				<p>
					<label>Memoria limite de la aplicación</label>
					<input type="number" name="memory_limit" value="<?=$step['memory_limit']?>" placeholder="10" min="5" required />

					<span class="h">Seleccione cuanta memoria en MB puede usar su aplicación.</span>
				</p>

				<p>
					<label>Tiempo limite de subida</label>
					<input type="number" name="max_input" value="<?=$step['max_input']?>" placeholder="100" min="30" required />

					<span class="h">Seleccione el tiempo limite en segundos para subir un archivo a su servidor.</span>
				</p>

				<p>
					<label>Peso limite de subida</label>
					<input type="number" name="max_filesize" value="<?=$step['max_filesize']?>" placeholder="50" min="0" required />

					<span class="h">Seleccione el tamaño limite en MB de un archivo subiendo a su servidor.</span>
				</p>
			</div>

			<div class="col2">
				<section>
					<p>
						Si se supera el <b>tiempo limite de carga</b> PHP mostrará un error de tiempo limite excedido.
					</p>

					<p>
						Si su aplicación supera el uso de memoria limite BeatRock mostrará una página de <b>Sobrecarga</b>, si su servidor se ha quedado realmente sin recursos PHP mostrará un error de memoria.
					</p>

					<p>
						Para calcular el uso de memoria de la aplicación PHP necesita tener permisos establecidos en su archivo de configuración.
					</p>
				</section>
			</div>
		</section>

		<section hidden data-apache="false" data-iis="false" data-nginx="true" data-required>
			<input type="hidden" name="root" value="<?=ROOT?>" />
			<input type="hidden" name="server_name" value="localhost" />

			<div class="col1">
				<h3>Configuración de Nginx</h3>

				<p>
					<label>Puerto</label>
					<input type="number" name="port" value="<?=$step['port']?>" placeholder="80" min="10" max="65534" />
				</p>

				<p>
					<label>Conexiones máximas entrantes</label>
					<input type="number" name="worker_connections" value="<?=$step['worker_connections']?>" placeholder="8000" min="1000" max="65534" />
				</p>

				<p>
					<label>Directorio de los archivos de la aplicación</label>
					<select name="folder" class="btn">
						<option value="www">www</option>
						<option value="htdocs">htdocs</option>
						<option value="html">html</option>
						<option value="public_html">public_html</option>
					</select>
				</p>

				<p>
					<label>Worker Processes</label>
					<input type="number" name="worker_processes" value="<?=$step['worker_processes']?>" placeholder="1" min="1" />
				</p>

				<p>
					<label>Worker rLimit NoFile</label>
					<input type="number" name="worker_rlimit_nofile" value="<?=$step['worker_rlimit_nofile']?>" placeholder="8192" min="5000" />
				</p>
			</div>

			<div class="col2">
			</div>
		</section>

		<p>
			<button class="ibtn">Crear archivo de servidor</button>
		</p>
	</form>
</div>