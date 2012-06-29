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

require('../Init.php');
require('./Info.php');

function CheckInit()
{
	global $Original;
	
	$result['config'] 	= is_readable('./templates/Configuration');
	$result['kernel'] 	= is_writable('../Kernel/');

	$result['curl'] 	= function_exists('curl_init');
	$result['json'] 	= function_exists('json_encode');

	$result['shorttag'] = ini_get('short_open_tag');	
	$result['php'] 		= version_compare(PHP_VERSION, '5.3.0', '>=');

	$result['beatrock'] = ($Original['version'] == '2.4.2') ? true : false;
	
	return $result;
}

$status 	= CheckInit();
$continue 	= true;

foreach($status as $param => $value)
{
	if($value == false)
	{
		$status[$param] = '?';
		$continue 		= false;
	}
	else
		$status[$param] = '>';
}

$logLink 	= 'https://docs.google.com/document/d/1HeSIBElNLH-NEdlSGqGck4I8jSBg4uevudf4ZP3DzKQ/edit';
$result 	= Core::theSession('update_status');

$page['name'] = '2.4.2 a 2.4.3';
require('Header.php');
?>
<div class="wrapper">
<div class="pre">
	<section class="left">
		<h2>Actualicemos su aplicación...</h2>
		<cite>Una versión aún más poderosa le espera...</cite>

		<p>
			Gracias por su interés en BeatRock, este proceso de actualización hara los cambios necesarios en su base de datos para que no haya problemas con su aplicación.
		</p>

		<p>
			Antes de proseguir por favor tomese unos minutos para leer los cambios en esta versión de BeatRock, de esta forma podrá tener en consideración los cambios que deberá efectuar en su código:
		</p>

		<p style="margin: 25px 0;">
			<a href="<?=$logLink?>" target="_blank" class="ibtn">Log de cambios</a>
		</p>

		<p>
			<b>Recuerde:</b> Esta actualización solo podrá hacer cambios en su base de datos y su archivo de configuración, tome en cuenta que también se crearán un "Backup" de los mismos por si algo sucede mal.
		</p>

		<p>
			<b style="color: red">¡Atención!</b> Si tiene el "modo seguro" activado por favor desactivelo para evitar problemas en la actualización.
		</p>
	</section>

	<figure class="right">
		<img src="<?=RESOURCES_INS?>/system/setup/images/update.png" />
	</figure>
</div>
</div>

<div class="content index">

	<div class="wrapper">
	<div id="process-form">
	<section class="version">
		<h2>¿A donde nos vamos actualizar?</h2>

		<div class="left">		
			<li><b>Nombre código:</b> <?=$Info['code']?></li>
			<li><b>Versión:</b> <?=$Info['version.revision']?></li>
			<li><b>Fase:</b> <?=$Info['version.fase']?></li>
			<li><b>Fecha de creación:</b> <?=$Info['date']?></li>
			<li><b>Hora de creación:</b> <?=$Info['date_hour']?></li>
			<li><b>Nombre:</b> <?=$Info['version.code']?></li>
		</div>

		<div class="right">
			<span class="version">v<?=$Info['version.revision']?></span>
		</div>
	</section>

	<section>
		<h2>¿Esta preparado?</h2>

		<table cellspacing="0" cellpadding="0" class="intable">
			<thead>
				<tr>
					<th>Función / Característica</th>
					<th>Estado</th>
					<th>Más información</th>
				</tr>
			</thead>

			<tbody>
				<tr>
					<th>Carpeta "/Kernel/" escribible</th>
					<th class="icon"><?=$status['kernel']?></th>
					<th>
						Permite la creación del archivo de configuración.
					</th>
				</tr>

				<tr>
					<th>Archivo "/Setup/Configuration" leible</th>
					<th class="icon"><?=$status['config']?></th>
					<th>
						Permite la lectura de la plantilla del archivo de configuración.
					</th>
				</tr>

				<tr>
					<th>Librería cURL</th>
					<th class="icon"><?=$status['curl']?></th>
					<th>
						<a href="http://www.codigogratis.com.ar/como-habilitar-curl-en-xampp-enabling-curl-on-xampp/" target="_blank">Instalar en Xampp</a> - <a href="http://www.pressthered.com/how_to_install_php_curl_on_iis/" target="_blank">Instalar en IIS (En Ingles)</a>
					</th>
				</tr>

				<tr>
					<th>Librería JSON</th>
					<th class="icon"><?=$status['json']?></th>
					<th></th>
				</tr>

				<tr>
					<th>Etiqueta corta de PHP</th>
					<th class="icon"><?=$status['shorttag']?></th>
					<th>
						Hace que la instalación y algunas partes de BeatRock funcionen correctamente. - <a href="http://www.cristalab.com/tutoriales/usar-etiqueta-corta-en-php-5-c27491l/" target="_blank">Activar</a>
					</th>
				</tr>

				<tr>
					<th>Versión de PHP - <?=phpversion()?></th>
					<th class="icon"><?=$status['php']?></th>
					<th>
						BeatRock solamente es compatible con las versiones 5.3 o superiores de PHP.
					</th>
				</tr>

				<tr>
					<th>Versión de BeatRock - <?=$Original['version']?></th>
					<th class="icon"><?=$status['beatrock']?></th>
					<th>
						Debe tener la versión <b>2.4.2</b> de BeatRock para actualizarse a esta versión.
					</th>
				</tr>
			</tbody>
		</table>
	</section>

	<p>
		<strong style="color: red">¡Atención!</strong> Dependiendo de la situación es probable que el archivo de configuración no se actualize correctamente, al terminar la actualización verifica si el archivo <b>"Configuration.json"</b> se encuentra correctamente escrito.
	</p>

	<p>
		<strong style="color: red">Nota:</strong> Si al terminar la actualización te da un error relacionado con la tabla "site_translate" no te preocupes, todo se hizo correcto y podrás continuar actualizando los archivos.
	</p>
	
	<p class="center">
	<? if($continue) { ?>	
		<a href="./actions/update.php" class="ibtn igreen">Comenzar actualización</a>
	<? } else { ?>
		<a class="ibtn ired">Es necesario que cumpla con los requisitos anteriores</a>
	<? } ?>
	</p>
	</div>

	<section id="complete" hidden>
		<h1>Disfrute de su nuevo poder ;)</h1>

		<p>
			Todo ha salido estupendo y hemos actualizado correctamente la base de datos de su aplicación, ahora puede proceder a actualizar los archivos de BeatRock.
		</p>

		<p>
			Gracias por su interés en el proyecto y recuerde eliminar la carpeta <b>/Update/</b> ;)
		</p>

		<p>
			<a href="<?=PATH?>" class="ibtn iblue">Continuar</a>
		</p>
	</section>
	</div>

</div>

<script>
<?
if(!empty($result))
{
	if($result == 'OK')
	{
	?>
	$('.pre').hide();
	$('#process-form').hide();
	$('#complete').removeAttr('hidden');
	<?
	} else {
	?>
	alert('¡Ha ocurrido un error durante la actualización! Intentelo nuevamente.');
	<?
} }
?>
</script>