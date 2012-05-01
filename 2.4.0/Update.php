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

require('Init.php');

if($G['do'] == 'update')
{
	$database = file_get_contents("http://beatrock.infosmart.mx/releases/PHP/Updates_2.3.2-2.4.0");
	$database = str_replace("{DB_ALIAS}", DB_ALIAS, $database);
	
	$db = explode(";", $database);
	$qs = Array();

	//BitRock::Backup();
	
	foreach($db as $query)
	{
		$query = trim($query);
			
		if(empty($query))
			continue;
			
		$qs[] = $query;
		
		BitRock::$ignore = true;
		mysql_query($query) or $eq = $query;
	}

	foreach($_POST as $param => $value)
	{
		$ps = explode("_", $param);

		if($ps[0] == "site")
		{		
			if(is_array($value))
			{
				foreach($value as $par => $val)
					$value[$par] = htmlentities($val);

				$value = json_encode($value);
			}

			Site::updateConf($param, mysql_real_escape_string($value));
			$qs[] = "UPDATE ".$config['mysql']['alias']."site_config SET result = '$value' WHERE var = '$param' LIMIT 1";
		}
	}
	
	if(empty($eq))
		$complete = true;
	else
		$error = true;
}

// Recursos de la instalación.
define("RESOURCES_INS", "//resources.infosmart.mx");
?>
<!DOCTYPE html>
<html>
<head>
	<title>Actualización de BeatRock</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	
	<meta charset="iso-8859-15" />
	<meta name="lang" content="es" />

	<meta name="publisher" content="InfoSmart." />
	<meta name="copyright" content="© 2012 InfoSmart. Desarrollado con BeatRock">
	
	<meta name="robots" content="noodp, nofollow, noindex" />
	
	<script>
	var Site = "<?php if(defined("PATH")) { echo PATH; } else { echo '.'; } ?>";
	var Path = "<?php if(defined("PATH")) { echo PATH; } else { echo '.'; } ?>";
	var Resources_Sys = "<?=RESOURCES_INS?>";
	</script>
	
	<link href="<?=RESOURCES_INS?>/system/css/style.css" rel="stylesheet" />
	<link href="<?=RESOURCES_INS?>/system/setup/style.install.css" rel="stylesheet" />
	
	<script src="<?=RESOURCES_INS?>/system/js/jquery.js"></script>
	<script src="<?=RESOURCES_INS?>/system/js/functions.kernel.js"></script>
	<script src="<?=RESOURCES_INS?>/system/setup/functions.install.js"></script>
</head>
<body>
	<div class="page" id="page">
		<header>
			<figure>
				<img src="<?=RESOURCES_INS?>/system/setup/Logo.png" />
			</figure>
			
			<h1>BeatRock 2.3.2 -> 2.4.0</h1>
		</header>
		
		<div class="content">
			<?php if($complete == true) { ?>
			<h2>¡Gracias por actualizar BeatRock!</h2>
			
			<p>
				La actualización se ha realizado correctamente, ahora proceda a eliminar este archivo.
			</p>
			
			<p>
				<b>Cambios realizados:</b>
			</p>
			
			<p>
				<?php foreach($qs as $q) { ?>
				<label style="font-family: Consolas; font-size: 12px;"><?=$q?></label><br /><br />
				<?php } ?>
			</p>

			<p>
				<b>¡Nota!</b> Le pedimos que agregue estas líneas de código a su archivo de configuración:
			</p>

			<textarea style="width: 700px; height: 500px;">
/*####################################################
##	MEMCACHE
####################################################*/

// Memcache - Host de conexión.
$config['memcache']['host'] = "";
// Memcache - Puerto de conexión.
$config['memcache']['port'] = 11211;
			</textarea>
			<?php } else if($error == true) { ?>
			<h2>¡Uy! Ocurrio un error</h2>
			
			<p>
				Ha ocurrido un error al intentar ejecutar '<?=$eq?>'. Intentelo de nuevo.
			</p>
			
			<?php } else { ?>
			<p>
				Bienvenido al Asistente de actualización de BeatRock, el asistente actualizará de forma automatica su base de datos para aceptar la nueva versión de BeatRock. Tenga en cuenta que es necesario una conexión a Internet.
			</p>
			
			<p>
				Por ahora tendrá que remplazar cuidadosamente los archivos de BeatRock para actualizarlo complementamente, visite el <a href="http://dev.infosmart.mx/forum/index.php#c2">foro</a>, seguro encontrará un tema sobre como hacerlo.
			</p>
			
			<p>
				Al proceder se creará un Backup de aplicación con base de datos, por la cual si surge algún error podrá restaurar su aplicación con el Backup, aún así sugerimos crear un Backup de forma manual.
			</p>

			<form action="<?=PATH?>/Update.php?do=update" method="POST">
				<section>
					<h2>General</h2>

					<p>
						<label for="site_locale">Lugar de la aplicación:</label>
						<input type="text" name="site_locale" id="site_locale" value="<?=$site['site_locale']?>" placeholder="es_MX" required autocomplete="off" maxlength="5" />
						
						<span>Escriba el formato del lugar/ubicación de la aplicación. (lenguaje)_(territorio)</span>
					</p>
				</section>

				<section>
					<h2>Open Graph</h2>

					<p>
						<label for="site_type">Tipo de aplicación:</label>

						<select name="site_type" id="site_type" class="btn">
							<option value="website">Sitio web normal</option>
							<option value="music.album">Aplicación musical para un albúm</option>
							<option value="music.radio_station">Aplicación para estación de radio</option>
							<option value="video.movie">Aplicación para una pelicula</option>
							<option value="video.tv_show">Aplicación para un Show de TV</option>
							<option value="video.other">Aplicación visual normal</option>
							<option value="book">Aplicación para un libro</option>
							<option value="profile">Blog personal / Perfil</option>
						</select>

						<span>Selecciona que tipo de aplicación crearás, si es una aplicación independiente propia selecciona "Sitio web normal".</span>
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

					<div class="oo" data-to="music.radio_station">
						<p>
							<label for="music:creator">Creador de la radio:</label>
							<input type="text" name="site_og[music:creator]" disabled />
						</p>
					</div>

					<div class="oo" data-to="video.movie">
						<p>
							<label for="video:actor">Actor principal:</label>
							<input type="text" name="site_og[video:actor]" disabled />
						</p>

						<p>
							<label for="video:director">Director:</label>
							<input type="text" name="site_og[video:director]" disabled />
						</p>

						<p>
							<label for="video:writer">Escritor:</label>
							<input type="text" name="site_og[video:writer]" disabled />
						</p>

						<p>
							<label for="video:duration">Duración:</label>
							<input type="text" name="site_og[video:duration]" disabled />
						</p>

						<p>
							<label for="video:release_date">Fecha de lanzamiento:</label>
							<input type="text" name="site_og[video:release_date]" disabled />
						</p>

						<p>
							<label for="video:tag">Palabras clave:</label>
							<input type="text" name="site_og[video:tag]" disabled />
						</p>
					</div>

					<div class="oo" data-to="video.tv_show">
						<p>
							<label for="video:actor">Actor principal:</label>
							<input type="text" name="site_og[video:actor]" disabled />
						</p>

						<p>
							<label for="video:director">Director:</label>
							<input type="text" name="site_og[video:director]" disabled />
						</p>

						<p>
							<label for="video:writer">Escritor:</label>
							<input type="text" name="site_og[video:writer]" disabled />
						</p>

						<p>
							<label for="video:release_date">Fecha de lanzamiento:</label>
							<input type="text" name="site_og[video:release_date]" disabled />
						</p>

						<p>
							<label for="video:tag">Palabras clave:</label>
							<input type="text" name="site_og[video:tag]" disabled />
						</p>
					</div>

					<div class="oo" data-to="book">
						<p>
							<label for="book:author">Autor:</label>
							<input type="text" name="site_og[book:author]" disabled />
						</p>

						<p>
							<label for="book:release_date">Fecha de lanzamiento:</label>
							<input type="text" name="site_og[book:release_date]" disabled />
						</p>

						<p>
							<label for="book:tag">Palabras clave:</label>
							<input type="text" name="site_og[book:tag]" disabled />
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

					<p>
						<a href="http://ogp.me/" target="_blank">Más información de Open Graph</a>
					</p>
				</section>
				
				<p class="center">
					<input type="submit" class="ibtn" value="Actualizar" />
				</p>
			</form>
			<?php } ?>
		</div>
	</div>
	
	<?=$audio?>
</body>
</html>