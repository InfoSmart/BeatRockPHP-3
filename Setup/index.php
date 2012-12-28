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

$page = 'index';

require 'Init.php';
require 'content/header.php';

$release 	= CheckRelease();
GetSystem();
?>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/es_LA/all.js#xfbml=1&appId=250213508414346";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<div class="info">
	<div class="wrapper">
		<div class="left">
			<h2>Introducción</h2>

			<p>
				¡Desarrollador de la nueva era! Bienvenid@ a la instalación de BeatRock, un potente Framework en PHP rápido, seguro y con inteligencia propia. BeatRock esta pensado para los desarrolladores que quieren optimizar su tiempo y productividad sin sacrificar el orden del código, las tareas automáticas ni la sencillez.
			</p>

			<p>
				Con controladores que van desde las herramientas más utilizadas en una aplicación hasta las distintas API's de servicios como Facebook, Twitter, Google+, Steam y VirusTotal.
			</p>
		</div>

		<figure>
			<img src="<?=RESOURCES_INS?>/systemv2/setup/images/index.png" />
		</figure>
	</div>
</div>

<div class="wrapper">
<div class="content">
	<div class="col1">
		<section>
			Entre sus distintas funciones, BeatRock le ofrece:

			<ul>
				<li>
					<a href="http://beatrock.infosmart.mx/wiki/Recuperación_Avanzada" target="_blank">Recuperación</a> en caso de perdida del archivo de configuración y la base de datos de forma automática.
				</li>

				<li>
					Creación de copias de seguridad tanto de la base de datos como de los archivos de la aplicación de forma automática y periodica.
				</li>

				<li>
					Reporte de errores, sospechas de inyección y recuperación del sistema por correo electrónico.
				</li>

				<li>
					Sistemas inteligentes para los Usuarios y las API's (Funciones para hacer más con menos)
				</li>

				<li>
					API's de los servicios más populares: Facebook, Twitter, Steam, Google+, VirusTotal, Gravatar, Microsoft Translate, Google Translate, IPInfoDB.
				</li>

				<li>
					Limpieza y optimización de la base de datos de forma periodica.
				</li>

				<li>
					Compatiblidad con <a href="http://jade-lang.com/" target="_blank">Jade</a>.
				</li>

				<li>
					Compatibilidad con Caché para MySQL, creación de arañas (robots), cURL, manipulación de imágenes (GD), conexión con servidores FTP y Memcached, soporte para Sockets.
				</li>

				<li>
					Aún más...
				</li>
			</ul>
		</section>

		<section>
			<h3>¿Esta usando la versión más reciente?</h3>

			<div class="table">		
				<li><b>Nombre código:</b> <?=$Info['code']?></li>
				<li><b>Versión:</b> <?=$Info['version.revision']?></li>
				<li><b>Fase:</b> <?=$Info['version.fase']?></li>
				<li><b>Fecha de creación:</b> <?=$Info['date']?></li>
				<li><b>Hora de creación:</b> <?=$Info['date_hour']?></li>
				<li><b>Nombre:</b> <?=$Info['version.code']?></li>
			</div>
		</section>

		<section>
			<h3>¿Su servidor esta preparado?</h3>

			<table cellspacing="0" cellpadding="0" class="intable">
				<thead>
					<tr>
						<th>Requisito</th>
						<th>Estado</th>
						<th>Más información</th>
					</tr>
				</thead>

				<tbody>
					<tr>
						<th>Permisos de escritura en /Setup/</th>
						<th><?=$system['setup']?></th>
						<th>Con ella podemos escribir un archivo de seguridad para evitar que terceros intenten acceder a la instalación.</th>
					</tr>

					<tr>
						<th>Permisos de escritura en /Kernel/</th>
						<th><?=$system['kernel']?></th>
						<th>BeatRock los necesita para poder escribir copias de seguridad y logs.</th>
					</tr>

					<tr>
						<th>Permisos de escritura en /App/</th>
						<th><?=$system['kernel']?></th>
						<th>BeatRock los necesita para poder escribir el archivo de configuración.</th>
					</tr>

					<tr>
						<th>Lectura del archivo /Setup/templates/Configuration</th>
						<th><?=$system['config']?></th>
						<th>Necesitamos que este archivo pueda ser leido.</th>
					</tr>

					<tr>
						<th>Lectura del archivo /Setup/templates/Database</th>
						<th><?=$system['db']?></th>
						<th>Necesitamos que este archivo pueda ser leido.</th>
					</tr>

					<tr>
						<th>Lectura del archivo /Setup/templates/Htaccess</th>
						<th><?=$system['htaccess']?></th>
						<th>Necesitamos que este archivo pueda ser leido.</th>
					</tr>

					<tr>
						<th>Lectura del archivo /Setup/templates/Webconfig</th>
						<th><?=$system['webconfig']?></th>
						<th>Necesitamos que este archivo pueda ser leido.</th>
					</tr>

					<tr>
						<th>cURL</th>
						<th><?=$system['curl']?></th>
						<th>cURL es necesario para muchas cosas en BeatRock, desde la creación de robots hasta el proxy falso.</th>
					</tr>

					<tr>
						<th>JSON</th>
						<th><?=$system['json']?></th>
						<th>JSON es necesario para poder leer y procesar información interna (incluyendo las API's)</th>
					</tr>

					<tr>
						<th>Etiqueta corta</th>
						<th><?=$system['shorttag']?></th>
						<th>La etiqueta corta es usada en el código de BeatRock y por lo tanto es necesaria.</th>
					</tr>

					<tr>
						<th>Versión de PHP</th>
						<th><?=$system['php']?></th>
						<th>Esta versión de BeatRock es compatible solamente con la versión 5.3+ (Usted tiene la: <?=PHP_VERSION?>)</th>
					</tr>
				</tbody>
			</table>
		</section>

		<section>
			<h3>Extras</h3>

			<p>
				Los siguientes requisitos no son obligatorios sin embargo tenerlos activados proporciona más funciones.
			</p>

			<table cellspacing="0" cellpadding="0" class="intable">
				<thead>
					<tr>
						<th>Requisito</th>
						<th>Estado</th>
						<th>Más información</th>
					</tr>
				</thead>

				<tbody>
					<tr>
						<th>Permisos Shell</th>
						<th><?=$system['shell']?></th>
						<th>La función <code>shell_exec</code> es necesaria para conocer el nivel de carga del servidor y procesar plantillas en <a href="http://jade-lang.com/" target="_blank">Jade</a>.</th>
					</tr>

					<tr>
						<th>Caché para MySQL</th>
						<th><?=$system['cache']?></th>
						<th>La extensión <code>mysqlnd_qc</code> es necesaria para activar la caché en las consultas MySQL</th>
					</tr>

					<tr>
						<th>Memcache</th>
						<th><?=$system['memcache']?></th>
						<th>La extensión <code>memcache</code> es necesaria para activar el uso de <a href="http://es.wikipedia.org/wiki/Memcached" target="_blank">Memcached</a> en BeatRock.</th>
					</tr>

					<tr>
						<th>SQLite 3</th>
						<th><?=$system['sqlite']?></th>
						<th>La extensión <code>sqlite3</code> es necesaria para activar la compatibilidad con bases de datos SQLite 3
					</tr>
				</tbody>
			</table>
		</section>
	</div>

	<div class="col2">
		<section class="center">
			<div class="fb-like-box" data-href="http://www.facebook.com/infosmart.beatrock" data-width="300" data-height="300" data-show-faces="true" data-border-color="white" data-stream="false" data-header="false"></div>
		</section>

		<section>
			<h4>¿Sabía que...?</h4>

			<p>
				Una de las metas en BeatRock es separar totalmente el HTML del PHP.
			</p>

			<p>
				BeatRock es uno de los pocos Frameworks en PHP compatibles con Jade.
			</p>

			<p>
				Con BeatRock solo necesita 2 funciones para tener la API de Facebook funcional.
			</p>
		</section>

		<section>
			<div class="release">
				<? if(!is_array($release)) { ?>
				<span class="error">Error con el sistema de comprobación.</span>
				<p>
					Lo sentimos, pero al parecer el sistema de comprobación de versión no se encuentra disponible.
				</p>

				<? } else if($release['code'] == 'ERROR') { ?>
				<span class="error">Actualización disponible.</span>
				<p>
					La versión <b><?=$release['ver']?></b> de BeatRock ya se encuentra disponible. Le recomendamos <a href="<?=$release['download']?>">descargarla</a> para evitar posibles bugs o problemas de seguridad con esta versión.
				</p>

				<? } else if($release['code'] !== 'ERROR') { ?>
				<span class="error">BeatRock esta actualizado.</span>
				<p>
					Puede continuar con la instalación.
				</p>
				<? } ?>
			</div>
		</section>

		<section class="center">
			<? if($ready == true) { ?>
			<a data-to="step2" class="ibtn">Continuar</a>
			<? } else {  ?>
			<a class="ibtn ired">Es necesario cumplir todos los requisitos</a>
			<? } ?>
		</section>
	</div>
</div>