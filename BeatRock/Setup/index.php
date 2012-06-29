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

$page['id'] = 'index';
require 'Init.php';

$page['name'] = 'Bienvenido';
require 'Header.php';
?>
<div class="pre">
	<section class="left">
		<h2>Hagamos magia con PHP.</h2>
		<cite>Poderoso, inteligente y rápido. ¿Listo para maximizar su poder en PHP?</cite>

		<p>
			BeatRock es un Framework en PHP que fácilita el trabajo a los desarrolladores de aplicaciones web recreando a PHP como un lenguaje <b>"más hermoso, fácil e interactivo"</b>. Con herramientas útiles y potentes que pueden proporcionar a sus proyectos más funciones interactivas.
		</p>


		<p>
			BeatRock usa la tecnología de PHP 5 para hacer aplicaciones sorprendentes y compatibles con distintos navegadores web y robots de búsqueda. Una completa amistad con <b>HTML 5 y CSS 3</b> le proporcionara aplicaciones más novedosas y simples.
		</p>

		<p>
			Es sencillo, fácil de manejar y que al igual que en PHP es solo cuestión de practica para encontrar su potencial y usarlo al máximo.
		</p>

		<p>
			Hemos creado BeatRock usando los estandares recomendados de varias compañias, organizaciones y proyectos que crean el futuro de la Web, además su código es sencillo y de fácil comprensión para los desarrolladores semi-expertos en PHP 5 lo cual hace su edición más sencilla.
		</p>
	</section>

	<figure class="right">
		<img src="<?=RESOURCES_INS?>/system/setup/images/<?=$page['id']?>.png" />
	</figure>
</div>
</div>

<div class="content index">
	<div class="wrapper">
	<section class="version">
		<h2>¿Qué vamos a instalar?</h2>

		<div class="left">		
			<li><b>Nombre código:</b> <?=$Info['code']?></li>
			<li><b>Versión:</b> <?=$Info['version.revision']?></li>
			<li><b>Fase:</b> <?=$Info['version.fase']?></li>
			<li><b>Fecha de creación:</b> <?=$Info['date']?></li>
			<li><b>Hora de creación:</b> <?=$Info['date_hour']?></li>
			<li><b>Nombre:</b> <?=$Info['version.code']?></li>
		</div>

		<div class="right">
			<span><?=CheckRelease()?></span>
		</div>
	</section>

	<section>
		<h2>¿Su servidor esta preparado?</h2>

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
					<th>Carpeta "/Setup/" escribible</th>
					<th class="icon"><?=$status['setup']?></th>
					<th>
						Permite el bloqueo de otros intentos de instalación y la eliminación automática al terminar.
					</th>
				</tr>

				<tr>
					<th>Carpeta "/Kernel/" escribible</th>
					<th class="icon"><?=$status['kernel']?></th>
					<th>
						Permite la creación del archivo de configuración.
					</th>
				</tr>

				<tr>
					<th>Archivo "/Setup/templates/Configuration" leible</th>
					<th class="icon"><?=$status['config']?></th>
					<th>
						Permite la lectura de la plantilla del archivo de configuración.
					</th>
				</tr>

				<tr>
					<th>Archivo "/Setup/templates/DATABASE" leible</th>
					<th class="icon"><?=$status['db']?></th>
					<th>
						Permite la lectura de la plantilla de la base de datos.
					</th>
				</tr>

				<tr>
					<th>Archivo "/Setup/templates/Htaccess" leible</th>
					<th class="icon"><?=$status['htaccess']?></th>
					<th>
						Permite la lectura de la plantilla del archivo "htaccess".
					</th>
				</tr>

				<tr>
					<th>Archivo "/Setup/templates/Webconfig" leible</th>
					<th class="icon"><?=$status['webconfig']?></th>
					<th>
						Permite la lectura de la plantilla del archivo "web.config".
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
					<th>Versión de PHP - (Usted tiene: <?=phpversion()?>)</th>
					<th class="icon"><?=$status['php']?></th>
					<th>
						BeatRock solamente es compatible con las versiones <b>5.3</b> o superiores de PHP.
					</th>
				</tr>
			</tbody>
		</table>
	</section>
	
	<p class="center">
	<?php if($continue) { ?>	
		<a href="./step2.php" class="ibtn igreen">Comenzar instalación</a>
	<?php } else { ?>
		<a class="ibtn ired">Es necesario que cumpla con los requisitos anteriores</a>
	<?php } ?>
	</p>
	
	<figure class="res">
		<a href="http://www.w3schools.com/html5/default.asp" target="_blank" title="HTML 5"><img src="http://www.w3.org/html/logo/downloads/HTML5_Logo_512.png" /></a>
		<a href="http://php.net/?beta=1" target="_blank" title="PHP"><img src="<?php echo RESOURCES_INS; ?>/system/setup/images/PHP.png" /></a>
		<a href="http://www.opensource.org/" target="_blank" title="Open Source"><img src="http://www.opensource.org/files/garland_logo.png" /></a>
	</figure>
</div>
</div>