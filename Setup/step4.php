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

$page = 'step4';

// Recursos de la instalación.
define('RESOURCES_INS', '//resources.infosmart.mx');

if($_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest')
	require 'content/header.php';
?>
<div class="info">
	<div class="wrapper">
		<div class="left">
			<h2>Aplicación</h2>

			<p>
				¡Ya casi acabamos! ahora sigue lo más importante, definir la información de tu aplicación: <b>Nombre de la aplicación, descripción, logo, etc</b>
			</p>

			<p style="color: red">
				¡Lo sentimos! Esta versión aún es BETA y este paso esta incompleto, no sirve. Aún así ya puedes acceder a tu <a href="<?=PATH?>">aplicación</a>.
			</p>
		</div>

		<figure>
			<img src="<?=RESOURCES_INS?>/systemv2/setup/images/step4.png" />
		</figure>
	</div>
</div>

<div class="wrapper">
<div class="content">
	<div class="box-error" id="error">
	</div>

	<form>		
		<section>
			<div class="col1">
				<p>
					<label>Nombre</label>
					<input type="text" name="site_name" value="<?=$site['site_name']?>" placeholder="Mi aplicación" autofocus x-webkit-speech speech />
					<span class="h">Escribe el nombre de tu aplicación</span>
				</p>

				<p>
					<label>Separación de titulo</label>
					<input type="text" name="site_separation" value="<?=$site['site_separation']?>" placeholder="-" />
					<span class="h">Escribe un caracter que será usado para separar el nombre de la aplicación del nombre de una página.</span>
				</p>

				<p>
					<label>Separación de titulo</label>
					<textarea name="site_keywords" value="<?=$site['site_keywords']?>" placeholder="infosmart, beatrock"></textarea>
					<span class="h">Escriba una serie de palabras separadas por comas (,) que definan el contenido y uso de su aplicación.</span>
				</p>

				<p>
					<label>Mapa del sitio</label>
					
					<select name="site_sitemap" class="btn">
						<option value="false">No</option>
						<option value="true">Si</option>	
					</select>
					
					<span class="h">Seleccione si su aplicación tendrá un "mapa del sitio". El mismo será ubicado en <b><?=PATH?>/sitemap</b>.</span>
				</p>

				<p>
					<label>Favicon</label>
					<b><?=RESOURCES?>/images/</b><input type="text" name="site_favicon" id="site_favicon" value="<?=$site['site_favicon']?>" placeholder="favicon.ico" autocomplete="off" class="short" />
					
					<span class="h">Escriba el nombre del archivo de su imagen Favicon.</span>
				</p>

				<p>
					<label for="register_all_visits">Registrar todas las visitas:</label>
					<select name="register_all_visits" class="btn">
						<option value="false">No</option>
						<option value="true">Si</option>	
					</select>
					
					<span class="h">Seleccione si desea registrar todas las visitas y accesos a su sitio, desactivarlo aliviana el peso de su base de datos.</span>
				</p>
			</div>
		</section>
	</form>
</div>