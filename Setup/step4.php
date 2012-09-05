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

$page['name'] = 'Configuración de la aplicación';
require('./Header.php');
?>
<div class="pre">
	<section class="left">
		<h2>¿Como será su aplicación?</h2>
		<cite>Nombre, descripción, el eslogan y otras partes de su aplicación o sitio web.</cite>

		<p>
			Ahora proceda a darle nombre a su aplicación o sitio web, así como una descripción corta, un eslogan y otras configuraciones de acuerdo a lo que tratará o las funcionalidades que tendrá.
		</p>

		<p>
			Recuerde que el proposito principal de BeatRock es ahorrarle trabajo para que usted pueda desarrollar la idea principal de su aplicación directamente, algunas de estas configuraciones tendrán efecto directo en las cabeceras donde tanto robots de indexación como robots sociales podrán obtener información de su aplicación y ofrecer mejores experiencias a sus usuarios.
		</p>
	</section>

	<figure class="right">
		<img src="<?=RESOURCES_INS?>/systemv2/setup/images/step4.png" />
	</figure>
</div>

<div class="content">	
	<form action="./actions/save_step4.php" method="POST">
		<section open>
			<h2>General</h2>

			<div class="c1">			
				<p>
					<label for="site_name">Nombre de la aplicación:</label>
					<input type="text" name="site_name" id="site_name" value="<?=$site['site_name']?>" placeholder="Mi Aplicación" required autofocus autocomplete="off" x-webkit-speech speech />
					
					<span>Escribe el nombre de tu aplicación, la misma será mostrada en el titulo de la página.</span>
				</p>
			
				<p>
					<label for="site_separation">Separación de titulo:</label>
					<input type="text" name="site_separation" id="site_separation" value="<?=$site['site_separation']?>" placeholder="~" autocomplete="off" />
					
					<span>Escribe una separación de titulo que será usado para por ejemplo, separar el nombre de tu aplicación y el eslogan o nombre de la página.</span>
				</p>

				<p>
					<label for="site_keywords">Palabras clave de la aplicación:</label>
					<textarea name="site_keywords" id="site_keywords" placeholder="infosmart, beatrock" required><?=$site['site_keywords']?></textarea>
					
					<span>Escriba una serie de palabras separadas por comas (,) que indiquen referencias acerca del contenido de su aplicación.</span>
				</p>				
				
				<p>
					<label>Mapa del sitio:</label>
					
					<select name="site_sitemap" class="btn">
						<option value="false">No</option>
						<option value="true">Si</option>	
					</select>
					
					<span>Seleccione si su aplicación tendrá un "mapa del sitio" que será ubicado <b><?=PATH?>/sitemap</b>.</span>
				</p>

				<p>
					<label for="site_favicon">Favicon:</label>
					<?=RESOURCES?>/images/<input type="text" name="site_favicon" id="site_favicon" value="<?=$site['site_favicon']?>" placeholder="favicon.ico" autocomplete="off" class="short" />
					
					<span>Escriba el nombre del archivo de su imagen Favicon.</span>
				</p>

				<p>
					<label for="register_all_visits">Registrar todas las visitas:</label>
					<select name="register_all_visits" class="btn">
						<option value="false">No</option>
						<option value="true">Si</option>	
					</select>
					
					<span>Seleccione si desea registrar todas las visitas y accesos a su sitio, desactivarlo aliviana el peso de su base de datos.</span>
				</p>
			</div>

			<div class="c2">
				<p>
					<label for="site_slogan">Eslogan de la aplicación:</label>
					<input type="text" name="site_slogan" id="site_slogan" value="<?=$site['site_slogan']?>" placeholder="Tecnología limpia y creativa para todos" autocomplete="off" x-webkit-speech speech />
					
					<span>Escribe un eslogan para tu aplicación, una frase corta que describa de lo que trata.</span>
				</p>

				<p>
					<label>Codificación:</label>
					
					<select name="site_charset" class="btn" required>
						<option value="iso-8859-15">iso-8859-15</option>
						<option value="iso-8859-1">iso-8859-1</option>
						<option value="utf-8">utf-8</option>
					</select>
					
					<span>Selecciona la codificación de letras para la aplicación, para letras en español recomendamos usar <b>iso-8859-15</b>.</span>
				</p>

				<p>
					<label for="site_description">Descripción de la aplicación:</label>
					<textarea name="site_description" id="site_description" placeholder="Aplicación útil para todas las edades..."><?=$site['site_description']?></textarea>
					
					<span>Escriba la descripción de la aplicación.</span>
				</p>

				<p>
					<label>RSS:</label>
					
					<select name="site_rss" class="btn">
						<option value="false">No</option>
						<option value="true">Si</option>
					</select>
					
					<span>Seleccione si su aplicación tendrá un RSS de noticias.</span>
				</p>

				<p>
					<label>Dirección RSS:</label>
					
					<input type="text" name="site_rss_path" id="site_rss_path" value="<?=$site['site_rss_path']?>" placeholder="{RSS}" />					
					<span>Escriba la dirección de su página RSS.<br /><b>{RSS}</b> = <b><?=PATH?>/rss</b>.</span>
				</p>

				<p>
					<label for="site_logo">Logo:</label>
					<?=RESOURCES?>/images/<input type="text" name="site_logo" id="site_logo" value="<?=$site['site_logo']?>" placeholder="logo.png" autocomplete="off" class="short" />
					
					<span>Escriba el nombre del archivo de su imagen Logo.</span>
				</p>
			</div>
		</section>

		<section>
			<h2>Técnico</h2>

			<div class="c1">
				<p>
					<label for="site_compress">Compresión HTML:</label>

					<select name="site_compress" class="btn">
						<option value="false">Desactivado</option>
						<option value="true">Activado</option>						
					</select>

					<span>Comprime el código HTML de la aplicación quitando espacios innecesarios y comentarios haciendola menos pesada y más rápida de ejecutar, sin embargo puede ocacionar problemas con JavaScript incrustado dentro del HTML.</span>
				</p>

				<p>
					<label for="cpu_limit">Limite de carga del CPU:</label>
					
					<select name="cpu_limit" class="btn" required>
						<option value="0">Desactivado</option>
						<option value="50">50%</option>
						<option value="60">60%</option>
						<option value="70">70%</option>
						<option value="80">80%</option>
						<option value="90">90%</option>
						<option value="95">95%</option>
					</select>
					
					<span>Seleccione el limite de carga media del CPU (Procesador), en caso de que supere la cantidad seleccionada se mostrará una página de "Sobrecarga".</span>
				</p>

				<p>
					<label for="session_alias">Prefijo de las Sesiones:</label>
					<input type="text" name="session_alias" id="session_alias" value="<?=$site['session_alias']?>" placeholder="beatrock_" autocomplete="off" />
					
					<span>Escriba un prefijo para la definición de "$_SESSION", esto con el fin de evitar conflictos con otras aplicaciones.</span>
				</p>
				
				<p>
					<label for="cookie_alias">Prefijo de las Cookies:</label>
					<input type="text" name="cookie_alias" id="cookie_alias" value="<?=$site['cookie_alias']?>" placeholder="beatrock_" autocomplete="off" />
					
					<span>Escriba un prefijo para la definición de "$_COOKIE", esto con el fin de evitar conflictos con otras aplicaciones.</span>
				</p>

				<p>
					<label>Optimización de JavaScript:</label>
					
					<select name="site_optimized_javascript" class="btn">
						<option value="false">No</option>
						<option value="true">Si</option>
					</select>
					
					<span>La optimización de JavaScript carga los archivos JavaScript de su aplicación en el pie de página de la misma.</span>
				</p>
			</div>

			<div class="c2">
				<p>
					<label for="site_recovery">Recuperación avanzada:</label>

					<select name="site_recovery" class="btn">
						<option value="true">Activado</option>
						<option value="false">Desactivado</option>
					</select>

					<span>La recuperación avanzada recupera el archivo de configuración y la base de datos en caso de que hayan sido eliminados.</span>
				</p>

				<?php if(file_exists('../.htaccess')) { ?>
				<p>
					<label>Carga de memoria limite de Apache:</label>
					
					<select name="apache_limit" class="btn" required>
						<option value="0">Desactivado</option>
						<option value="52428800">50 MB</option>
						<option value="83886080">80 MB</option>
						<option value="104857600">100 MB</option>
						<option value="157286400">150 MB</option>
						<option value="209715200">200 MB</option>
						<option value="314572800">300 MB</option>
						<option value="419430400">400 MB</option>
						<option value="524288000">500 MB</option>
						<option value="629145600">600 MB</option>
						<option value="734003200">800 MB</option>
						<option value="1073741824">1 GB</option>
						<option value="1610612736">1.5 GB</option>
						<option value="2147483648">2 GB</option>
						<option value="5368709120">5 GB</option>
					</select>
					
					<span>Seleccione la carga de memoria limite para el proceso de Apache <b>(httpd)</b>, en caso de que supere la cantidad seleccionada se mostrará una página de "Sobrecarga".</span>
				</p>
				<?php } ?>

				<p>
					<label for="cookie_duration">Duración de la Cookie:</label>
					<input type="number" name="cookie_duration" id="cookie_duration" value="<?=$site['cookie_duration']?>" placeholder="300" required autocomplete="off" min="30" />
					
					<span>Especifique el tiempo de duración en minutos de las Cookie.</span>
				</p>
				
				<p>
					<label for="cookie_domain">Dominio válido de las Cookie:</label>
					<input type="text" name="cookie_domain" id="cookie_domain" value="<?=$site['cookie_domain']?>" placeholder="infosmart.mx" autocomplete="off" />
					
					<span>Escriba el dominio en donde será válido las Cookies, dejelo en blanco para omitir esta opción.</span>
				</p>
			</div>
		</section>

		<section>
			<h2>Idioma y traducción</h2>

			<div class="c1">
				<p>
					<label for="site_language">Idioma de la aplicación:</label>
					<input type="text" name="site_language" id="site_language" value="<?=$site['site_language']?>" placeholder="es" required autocomplete="off" maxlength="2" />
					
					<span>Escriba las dos primeras letras del idioma de la aplicación, la misma como una referencia para robots como Google y para un estandar recomendado por la W3C.</span>
				</p>
			</div>

			<div class="c2">
				<p>
					<label>Obligar idioma:</label>					
					<input type="text" name="site_translate" id="site_translate" value="<?=$site['site_translate']?>" placeholder="es" autocomplete="off" maxlength="2" />
					
					<span>Si desea obligar a usar un idioma/traducción en su aplicación indique las dos primeras letras del idioma, dejelo en blanco para usar el idioma nativo del usuario.</span>
				</p>
			</div>
		</section>
		
		<section>
			<h2>Información</h2>

			<div class="c1">			
				<p>
					<label for="site_version">Versión:</label>
					<input type="text" name="site_version" id="site_version" value="<?=$site['site_version']?>" placeholder="1.0.0" required autocomplete="off" />
					
					<span>Escriba la versión de su aplicación.</span>
				</p>

				<p>
					Use el archivo <b>humans.txt</b> para especificar los desarrolladores, diseñadores, creadores de un buen café y personas que participaron en la creación de esta aplicación. <a href="http://humanstxt.org/ES" target="_blank">Más información</a>
				</p>
			</div>

			<div class="c2">			
				<p>
					<label for="site_revision">Última revisión:</label>
					<input type="text" name="site_revision" id="site_revision" value="<?=$site['site_revision']?>" placeholder="27 de oct de <?=date('Y')?>" required autocomplete="off" />
					
					<span>Escriba la fecha de la última revisión o edición de su aplicación.</span>
				</p>

				<p>
					<label for="site_publisher">Empresa / Compañia / Organización distribuidora:</label>
					<input type="text" name="site_publisher" id="site_publisher" value="<?=$site['site_publisher']?>" placeholder="InfoSmart" required autocomplete="off" x-webkit-speech speech />
					
					<span>Escriba la empresa / compañia / organización que mantiene esta aplicación y se encarga de distribuirla.</span>
				</p>
			</div>			
		</section>

		<section>
			<h2>Open Graph</h2>

			<div class="c1">
				<p>
					<label for="site_locale">Lugar de la aplicación:</label>
					<input type="text" name="site_locale" id="site_locale" value="<?=$site['site_locale']?>" placeholder="es_LA" required autocomplete="off" maxlength="5" />
					
					<span>Escriba el formato del lugar/ubicación de la aplicación. (lenguaje)_(territorio)</span>
				</p>

				<div class="oo" data-to="music.album">
					<p>
						<label for="music:album">Albúm:</label>
						<input type="text" name="site_og[music:album]" disabled />
					</p>

					<p>
						<label for="music:musician">Músico/Artista/Banda:</label>
						<input type="text" name="site_og[music:musician]" disabled />
					</p>

					<p>
						<label for="music:release_date">Fecha de lanzamiento:</label>
						<input type="text" name="site_og[music:release_date]" disabled />
					</p>
				</div>

				<div class="oo" data-to="profile">
					<p>
						<label for="profile:first_name">Nombre:</label>
						<input type="text" name="site_og[profile:first_name]" disabled />
					</p>

					<p>
						<label for="profile:last_name">Apellidos:</label>
						<input type="text" name="site_og[profile:last_name]" disabled />
					</p>

					<p>
						<label for="profile:username">Nombre en la web (Nombre de usuario):</label>
						<input type="text" name="site_og[profile:username]" disabled />
					</p>

					<p>
						<label for="profile:gender">Sexo:</label>
						<select name="profile:gender" disabled>
							<option value="male">Hombre</option>
							<option value="female">Mujer</option>
						</select>
					</p>
				</div>
			</div>

			<div class="c2">
				<p>
					<label for="site_type">Tipo de aplicación:</label>

					<select name="site_type" id="site_type" class="btn">
						<option value="website">Sitio web normal</option>
						<option value="music.album">Aplicación musical para un albúm</option>
						<option value="video.other">Aplicación visual normal</option>
						<option value="profile">Blog personal / Perfil</option>
					</select>

					<span>Selecciona que tipo de aplicación crearás, si es una aplicación independiente propia selecciona "Sitio web normal".</span>
				</p>

				<p>
					<a href="http://ogp.me/" target="_blank">Más información de Open Graph</a>
				</p>
			</div>			
		</section>
		
		<section>
			<h2>Tareas cronometradas</h2>

			<div class="c1">			
				<p>
					<label>Optimización de la base de datos:</label>
					
					<select name="stopwatch_optimize_db" class="btn">
						<option value="">Desactivada</option>
						<option value="1440">A diario</option>
						<option value="2880">Cada 2 días</option>
						<option value="5760">Cada 4 días</option>
						<option value="10080">Cada semana</option>
						<option value="14400">Cada semana, 3 días</option>
						<option value="20160">Cada 2 semanas</option>
						<option value="44640">Cada mes</option>
						<option value="89280">Cada 2 meses</option>
						<option value="133920">Cada 3 meses</option>
					</select>
					
					<span>Optimiza todas la tablas de la base de datos.</span>
				</p>

				<p>
					<label>Recuperación de la base de datos:</label>
					
					<select name="stopwatch_backup_db" class="btn">
						<option value="">Desactivada</option>
						<option value="1440">A diario</option>
						<option value="2880">Cada 2 días</option>
						<option value="5760">Cada 4 días</option>
						<option value="10080">Cada semana</option>
						<option value="14400">Cada semana, 3 días</option>
						<option value="20160">Cada 2 semanas</option>
						<option value="44640">Cada mes</option>
						<option value="89280">Cada 2 meses</option>
						<option value="133920">Cada 3 meses</option>
					</select>
					
					<span>Crea un archivo SQL con los datos más recientes de la base de datos.</span>
				</p>

				<p>
					<label>Recuperación de los archivos de la aplicación y la base de datos:</label>
					
					<select name="stopwatch_backup_total" class="btn">
						<option value="">Desactivada</option>
						<option value="1440">A diario</option>
						<option value="2880">Cada 2 días</option>
						<option value="5760">Cada 4 días</option>
						<option value="10080">Cada semana</option>
						<option value="14400">Cada semana, 3 días</option>
						<option value="20160">Cada 2 semanas</option>
						<option value="44640">Cada mes</option>
						<option value="89280">Cada 2 meses</option>
						<option value="133920">Cada 3 meses</option>
					</select>
					
					<span>Crea un archivo ZIP con todos los archivos de la aplicación y un SQL de los datos de la base de datos.</span>
				</p>

				<!--
				<p>
					<label>Examinación de archivos malintencionados:</label>
					
					<select name="stopwatch_antimalware" class="btn">
						<option value="">Desactivada</option>
						<option value="1440">A diario</option>
						<option value="2880">Cada 2 días</option>
						<option value="5760">Cada 4 días</option>
						<option value="10080">Cada semana</option>
						<option value="14400">Cada semana, 3 días</option>
						<option value="20160">Cada 2 semanas</option>
						<option value="44640">Cada mes</option>
						<option value="89280">Cada 2 meses</option>
						<option value="133920">Cada 3 meses</option>
					</select>
					
					<span>Busca por archivos malintencionados y envia una copia de su aplicación al servicio de <a href="https://www.virustotal.com/" target="_blank">Virus Total</a>.</span>
				</p>
				-->
			</div>

			<div class="c2">			
				<p>
					<label>Limpieza de la base de datos:</label>
					
					<select name="stopwatch_maintenance_db" class="btn">
						<option value="">Desactivada</option>
						<option value="1440">A diario</option>
						<option value="2880">Cada 2 días</option>
						<option value="5760">Cada 4 días</option>
						<option value="10080">Cada semana</option>
						<option value="14400">Cada semana, 3 días</option>
						<option value="20160">Cada 2 semanas</option>
						<option value="44640">Cada mes</option>
						<option value="89280">Cada 2 meses</option>
						<option value="133920">Cada 3 meses</option>
					</select>
					
					<span>Limpia las tablas "site_errors" (Errores), "site_visits" (Visitas por IP) y "site_visits_total" (Visitas totales) de la base de datos.</span>
				</p>

				<p>
					<label>Recuperación de los archivos de la aplicación:</label>
					
					<select name="stopwatch_backup_app" class="btn">
						<option value="">Desactivada</option>
						<option value="1440">A diario</option>
						<option value="2880">Cada 2 días</option>
						<option value="5760">Cada 4 días</option>
						<option value="10080">Cada semana</option>
						<option value="14400">Cada semana, 3 días</option>
						<option value="20160">Cada 2 semanas</option>
						<option value="44640">Cada mes</option>
						<option value="89280">Cada 2 meses</option>
						<option value="133920">Cada 3 meses</option>
					</select>
					
					<span>Crea un archivo ZIP con todos los archivos de la aplicación.</span>
				</p>

				<p>
					<label>Limpieza de Recuperaciones, logs y archivos temporales:</label>
					
					<select name="stopwatch_maintenance_backups" class="btn">
						<option value="">Desactivada</option>
						<option value="1440">A diario</option>
						<option value="2880">Cada 2 días</option>
						<option value="5760">Cada 4 días</option>
						<option value="10080">Cada semana</option>
						<option value="14400">Cada semana, 3 días</option>
						<option value="20160">Cada 2 semanas</option>
						<option value="44640">Cada mes</option>
						<option value="89280">Cada 2 meses</option>
						<option value="133920">Cada 3 meses</option>
					</select>
					
					<span>Elimina los archivos dentro de los directorios "Logs", "Backups" y "Temp" del directorio "Kernel/BitRock/".</span>
				</p>
			</div>
		</section>
		
		<section>
			<h2>Otros</h2>

			<div class="c1">
				<p>
					<input type="checkbox" name="site_backups_servers" value="true" /> Usar los servidores de recuperación para el envío de backups.
					<span>Los Backups de archivos y base de datos serán enviados a los servidores de recuperación al momento que se crean.</span>
				</p>
			</div>

			<div class="c2">
				<p>
					<input type="checkbox" name="register" value="true" /> Seleccionar una licencia para mi aplicación.
					<span>Serás enviado a la página de Creative Commons para escojer una licencia que se adapte a tu aplicación, cuando termines serás redireccionado a la página de finalización y los datos de tu licencia se guardaran en el directorio raiz de BeatRock.</span>
				</p>
			</div>
		</section>
		
		<p>
			<input type="submit" value="Guardar y terminar" class="ibtn" />
		</p>
	</form>
</div>
<?php require('Footer.php')?>