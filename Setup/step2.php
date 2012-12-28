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

$page = 'step2';

if($_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest')
	require 'content/header.php';

file_put_contents('./SECURE', 'SECURE - ' . IP . ' - ' . URL);
$_SESSION['install']['secure'] = true;

$step['sql_host'] 		= 'localhost';
$step['sql_port'] 		= 3306;

$step['site_path'] 				= str_ireplace('/Setup/step2.php', '', URL);
$step['site_resources'] 		= $step['site_path'] . '/resources/app';
$step['site_resources_global'] 	= $step['site_path'] . '/resources/global';

$step['security_hash'] 		= Random(80, true, true, true);
$step['memcache_port'] 		= 11211;
?>
<div class="info">
	<div class="wrapper">
		<div class="left">
			<h2>Configuración</h2>

			<p>
				BeatRock precisa de un archivo de configuración que contenga los ajustes más importantes del Framework como los datos para la conexión al servidor MySQL, la ubicación de la aplicación y la seguridad a tomar.
			</p>

			<p>
				Completa el formulario de más abajo para que BeatRock pueda crear el archivo de configuración.
			</p>

			<p>
				Puede obtener más información del archivo de configuración y la utilidad de sus parámetros en nuestra <a href="http://beatrock.infosmart.mx/wiki/Configuración" target="_blank">wiki</a>.
			</p>
		</div>

		<figure>
			<img src="<?=RESOURCES_INS?>/systemv2/setup/images/step2.png" />
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
				<h3>SQL</h3>

				<p>
					<label>Tipo de servidor SQL</label>
					<input type="radio" name="sql_type" id="sql_type" value="mysql" checked /> MySQL<br />
					<input type="radio" name="sql_type" id="sql_type" value="sqlite" <? if(!extension_loaded('sqlite3')) { ?>disabled title="SQLite no es soportado por su servidor."<? } ?> /> SQLite 3
				</p>

				<div class="only_mysql">
					<p>
						<label>Host / Dirección IP</label>
						<input type="text" name="sql_host" value="<?=$step['sql_host']?>" required placeholder="localhost" />
						<span class="h">El nombre Host o la dirección IP donde se realizará la conexión al servidor MySQL.</span>
					</p>

					<p>
						<label>Nombre de usuario</label>
						<input type="text" name="sql_user" required placeholder="root" />
						<span class="h">El nombre de usuario que usaremos para conectarnos al servidor MySQL.</span>
					</p>

					<p>
						<label>Contraseña</label>
						<input type="password" name="sql_pass" required />
						<span class="h">La contraseña que usaremos para conectarnos al servidor MySQL.</span>
					</p>

					<p>
						<label>Nombre de la base de datos</label>
						<input type="text" name="sql_name" required placeholder="beatrock" x-webkit-speech speech />
						<span class="h">Escriba el nombre que le dará a su base de datos. Nota: Si no la ha creado, BeatRock intentará crearla.</span>
					</p>

					<p>
						<label>Prefijo de la base de datos</label>
						<input type="text" name="sql_prefix" placeholder="app_"  />
						<span class="h">Puede escribir un prefijo para las tablas de la base de datos. (Opcional)</span>
					</p>

					<p>
						<label>Puerto</label>
						<input type="number" name="sql_port" value="<?=$step['sql_port']?>" required placeholder="3306" />
						<span class="h">El puerto del servidor MySQL, generalmente es <b>3306</b></span>
					</p>
				</div>

				<div class="only_sqlite">
					<p>
						<label>Nombre de la base de datos</label>
						<input type="text" name="sql_lite_name" required placeholder="beatrock" x-webkit-speech speech disabled />
						<span class="h">Escriba el nombre que le dará a su base de datos. Sin la extensión .sqlite</span>
					</p>
				</div>

				<p>
					<label>Reparación en caso de error</label>
					<select name="sql_repair_error" class="btn">
						<option value="true">Activado</option>
						<option value="false">Desactivado</option>
					</select>
					<span class="h">En caso de que se ocasione un error en una consulta ¿Intentar repararlo automáticamente?</span>
				</p>
			</div>

			<div class="col2">
				<section>
					<h4>¿Sabía que...?</h4>

					<p>
						BeatRock es compatible con <a href="http://www.php.net/manual/es/book.mysqli.php?beta=1" target="_blank">MySQLi</a> y <a href="http://www.php.net/manual/es/book.sqlite3.php?beta=1" target="_blank">SQLite</a>.
					</p>

					<div class="only_sqlite">
						<p>
							Su base de datos <b>SQLite 3</b> será guardada dentro de la carpeta /App/
						</p>

						<p>
							Puede usar herramientas como <a href="https://addons.mozilla.org/es/firefox/addon/sqlite-manager/" target="_blank">SQLite Manager de Firefox</a>, <a href="http://www.sqlabs.net/sqlitemanager.php" target="_blank">SQLite Manager</a> y <a href="http://www.sqliteexpert.com/" target="_blank">SQLite Expert</a> para administrar su base de datos.
						</p>

						<p>
							BeatRock deniega cualquier intento de acceso/descarga  a los archivos <b>.sqlite</b> de su aplicación. No se preocupe por el lugar donde lo guardara.
						</p>
					</div>

					<p class="only_mysql">
						BeatRock es capaz de solucionar problemas relacionados a consultas MySQL de manera automática
					</p>

					<p>
						Si por accidente o por un ataque de seguridad su base de datos es eliminada BeatRock puede recuperarla automáticamente sin notificarle a sus visitantes
					</p>

					<p>
						BeatRock le reportará por correo electrónico si uno de sus visitantes intenta realizar una inyección SQL.
					</p>

					<p>
						La función para realizar consultas es <code>q()</code>
					</p>

					<p>
						Para usar el prefijo en una consulta solo basta con escribir <code>{DA}</code> dentro de la consulta. Es decir: <code>q('SELECT * FROM {DA}users');</code>. Si no escribio un prefijo <code>{DA}</code> se traducirá a nada.
					</p>

					<p>
						Puede usar el controlador "Query" para crear consultas de forma inteligente, es decir: <code>Query('users')->Select()->Add('email', 'webmaster@infosmart.mx')->Run();</code> es lo mismo a <code>SELECT * FROM {DA}users WHERE email = 'webmaster@infosmart.mx'</code>
					</p>
				</section>
			</div>
		</section>

		<section>
			<div class="col1">
				<h3>Ubicación</h3>

				<p>
					<label>Zona horaria</label>
					<select name="site_timezone" class="btn" required>
						<option value="Pacific/Midway">(GMT-11:00) Midway Island, Samoa</option>
						<option value="America/Adak">(GMT-10:00) Hawaii-Aleutian</option>
						<option value="Etc/GMT+10">(GMT-10:00) Hawaii</option>
						<option value="Pacific/Marquesas">(GMT-09:30) Marquesas Islands</option>
						<option value="Pacific/Gambier">(GMT-09:00) Gambier Islands</option>
						<option value="America/Anchorage">(GMT-09:00) Alaska</option>
						<option value="America/Ensenada">(GMT-08:00) Tijuana, Baja California</option>
						<option value="Etc/GMT+8">(GMT-08:00) Pitcairn Islands</option>
						<option value="America/Los_Angeles">(GMT-08:00) Pacific Time (US & Canada)</option>
						<option value="America/Denver">(GMT-07:00) Mountain Time (US & Canada)</option>
						<option value="America/Chihuahua">(GMT-07:00) Chihuahua, La Paz, Mazatlan</option>
						<option value="America/Dawson_Creek">(GMT-07:00) Arizona</option>
						<option value="America/Belize">(GMT-06:00) Saskatchewan, Central America</option>
						<option value="America/Mexico_City" selected>(GMT-06:00) Guadalajara, Mexico City, Monterrey</option>
						<option value="Chile/EasterIsland">(GMT-06:00) Easter Island</option>
						<option value="America/Chicago">(GMT-06:00) Central Time (US & Canada)</option>
						<option value="America/New_York">(GMT-05:00) Eastern Time (US & Canada)</option>
						<option value="America/Havana">(GMT-05:00) Cuba</option>
						<option value="America/Bogota">(GMT-05:00) Bogota, Lima, Quito, Rio Branco</option>
						<option value="America/Caracas">(GMT-04:30) Caracas</option>
						<option value="America/Santiago">(GMT-04:00) Santiago</option>
						<option value="America/La_Paz">(GMT-04:00) La Paz</option>
						<option value="Atlantic/Stanley">(GMT-04:00) Faukland Islands</option>
						<option value="America/Campo_Grande">(GMT-04:00) Brazil</option>
						<option value="America/Goose_Bay">(GMT-04:00) Atlantic Time (Goose Bay)</option>
						<option value="America/Glace_Bay">(GMT-04:00) Atlantic Time (Canada)</option>
						<option value="America/St_Johns">(GMT-03:30) Newfoundland</option>
						<option value="America/Araguaina">(GMT-03:00) UTC-3</option>
						<option value="America/Montevideo">(GMT-03:00) Montevideo</option>
						<option value="America/Miquelon">(GMT-03:00) Miquelon, St. Pierre</option>
						<option value="America/Godthab">(GMT-03:00) Greenland</option>
						<option value="America/Argentina/Buenos_Aires">(GMT-03:00) Buenos Aires</option>
						<option value="America/Sao_Paulo">(GMT-03:00) Brasilia</option>
						<option value="America/Noronha">(GMT-02:00) Mid-Atlantic</option>
						<option value="Atlantic/Cape_Verde">(GMT-01:00) Cape Verde Is.</option>
						<option value="Atlantic/Azores">(GMT-01:00) Azores</option>
						<option value="Europe/Belfast">(GMT) Greenwich Mean Time : Belfast</option>
						<option value="Europe/Dublin">(GMT) Greenwich Mean Time : Dublin</option>
						<option value="Europe/Lisbon">(GMT) Greenwich Mean Time : Lisbon</option>
						<option value="Europe/London">(GMT) Greenwich Mean Time : London</option>
						<option value="Africa/Abidjan">(GMT) Monrovia, Reykjavik</option>
						<option value="Europe/Amsterdam">(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna</option>
						<option value="Europe/Belgrade">(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague</option>
						<option value="Europe/Brussels">(GMT+01:00) Brussels, Copenhagen, Madrid, Paris</option>
						<option value="Africa/Algiers">(GMT+01:00) West Central Africa</option>
						<option value="Africa/Windhoek">(GMT+01:00) Windhoek</option>
						<option value="Asia/Beirut">(GMT+02:00) Beirut</option>
						<option value="Africa/Cairo">(GMT+02:00) Cairo</option>
						<option value="Asia/Gaza">(GMT+02:00) Gaza</option>
						<option value="Africa/Blantyre">(GMT+02:00) Harare, Pretoria</option>
						<option value="Asia/Jerusalem">(GMT+02:00) Jerusalem</option>
						<option value="Europe/Minsk">(GMT+02:00) Minsk</option>
						<option value="Asia/Damascus">(GMT+02:00) Syria</option>
						<option value="Europe/Moscow">(GMT+03:00) Moscow, St. Petersburg, Volgograd</option>
						<option value="Africa/Addis_Ababa">(GMT+03:00) Nairobi</option>
						<option value="Asia/Tehran">(GMT+03:30) Tehran</option>
						<option value="Asia/Dubai">(GMT+04:00) Abu Dhabi, Muscat</option>
						<option value="Asia/Yerevan">(GMT+04:00) Yerevan</option>
						<option value="Asia/Kabul">(GMT+04:30) Kabul</option>
						<option value="Asia/Yekaterinburg">(GMT+05:00) Ekaterinburg</option>
						<option value="Asia/Tashkent">(GMT+05:00) Tashkent</option>
						<option value="Asia/Kolkata">(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi</option>
						<option value="Asia/Katmandu">(GMT+05:45) Kathmandu</option>
						<option value="Asia/Dhaka">(GMT+06:00) Astana, Dhaka</option>
						<option value="Asia/Novosibirsk">(GMT+06:00) Novosibirsk</option>
						<option value="Asia/Rangoon">(GMT+06:30) Yangon (Rangoon)</option>
						<option value="Asia/Bangkok">(GMT+07:00) Bangkok, Hanoi, Jakarta</option>
						<option value="Asia/Krasnoyarsk">(GMT+07:00) Krasnoyarsk</option>
						<option value="Asia/Hong_Kong">(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi</option>
						<option value="Asia/Irkutsk">(GMT+08:00) Irkutsk, Ulaan Bataar</option>
						<option value="Australia/Perth">(GMT+08:00) Perth</option>
						<option value="Australia/Eucla">(GMT+08:45) Eucla</option>
						<option value="Asia/Tokyo">(GMT+09:00) Osaka, Sapporo, Tokyo</option>
						<option value="Asia/Seoul">(GMT+09:00) Seoul</option>
						<option value="Asia/Yakutsk">(GMT+09:00) Yakutsk</option>
						<option value="Australia/Adelaide">(GMT+09:30) Adelaide</option>
						<option value="Australia/Darwin">(GMT+09:30) Darwin</option>
						<option value="Australia/Brisbane">(GMT+10:00) Brisbane</option>
						<option value="Australia/Hobart">(GMT+10:00) Hobart</option>
						<option value="Asia/Vladivostok">(GMT+10:00) Vladivostok</option>
						<option value="Australia/Lord_Howe">(GMT+10:30) Lord Howe Island</option>
						<option value="Etc/GMT-11">(GMT+11:00) Solomon Is., New Caledonia</option>
						<option value="Asia/Magadan">(GMT+11:00) Magadan</option>
						<option value="Pacific/Norfolk">(GMT+11:30) Norfolk Island</option>
						<option value="Asia/Anadyr">(GMT+12:00) Anadyr, Kamchatka</option>
						<option value="Pacific/Auckland">(GMT+12:00) Auckland, Wellington</option>
						<option value="Etc/GMT-12">(GMT+12:00) Fiji, Kamchatka, Marshall Is.</option>
						<option value="Pacific/Chatham">(GMT+12:45) Chatham Islands</option>
						<option value="Pacific/Tongatapu">(GMT+13:00) Nuku'alofa</option>
						<option value="Pacific/Kiritimati">(GMT+14:00) Kiritimati</option>
					</select>
					<span class="h">Seleccione la zona horaria predeterminada de tu aplicación. En la configuración del servidor (más abajo) podrás configurar si deseas usar la zona horaria del usuario en vez de esta.</span>
				</p>

				<p>
					<label>Ubicación de la aplicación</label>
					<input type="text" name="site_path" value="<?=$step['site_path']?>" required placeholder="" />
					<span class="h">Escriba la dirección web donde podrá ser accesible su aplicación (Sin http:// al principio ni / al final)
				</p>

				<p>
					<label>Ubicación de los recursos</label>
					<input type="text" name="site_resources" value="<?=$step['site_resources']?>" required placeholder="" />
					<span class="h">Escriba la dirección web donde podrá ser accesible a los recursos de su aplicación (Sin http:// al principio ni / al final)
				</p>

				<p>
					<label>Ubicación de los recursos globales</label>
					<input type="text" name="site_resources_global" value="<?=$step['site_resources_global']?>" required placeholder="" />
					<span class="h">Escriba la dirección web donde podrá ser accesible a los recursos globales (Sin http:// al principio ni / al final)
				</p>
			</div>

			<div class="col2">
				<p>
					La zona horaria le permite a las funciones de tiempo de PHP poder mostrarse en el horario que escoja. (Por ejemplo: <code>date()</code>)
				</p>

				<p>
					BeatRock usa la constante <code>PATH</code> y la variable de plantilla <code>%PATH%</code> para referirse a la dirección web base de la aplicación. (Estos ya tienen el protocolo http:// o https:// al principio)
				</p>

				<p>
					<li>%PATH% -> Dirección web base de la aplicación</li>

					<li>%RESOURCES% -> Dirección web base de los recursos de la aplicación</li>

					<li>%RESOURCES_SYS% -> Dirección web base de los recursos globales</li>
				</p>

				<p>
					<code><?=_c('<a href="%PATH%/about">Acerca de nosotros</a>')?></code>
				</p>

				<p>
					<code><?=_c('<img src="%RESOURCES%/images/beatrock.png" />')?></code>
				</p>
			</div>
		</section>

		<section>
			<div class="col1">
				<h3>Seguridad</h3>

				<p>
					<label>Nivel de encriptación</label>
					<select name="security_level" class="btn" required>
						<option value="0">Sin encriptación</option>
						<option value="1">MD5</option>
						<option value="2">SHA1</option>
						<option value="3">SHA256 con SHA1</option>
						<option value="4" selected>SHA256 con SHA1 y MD5</option>
						<option value="5">Encriptación reversible</option>
					</select>
					<span class="h">Seleccione el nivel de encriptación usada por la función <code>Core::Encrypt</code></span>
				</p>

				<p>
					<label>Clave de encriptación</label>
					<input type="text" name="security_hash" value="<?=$step['security_hash']?>" required />
					<span class="h">La clave ofrece una encriptación única para la aplicación.</span>
				</p>

				<p>
					<label>Uso de lista negra para el sistema Anti-DDoS</label>
					<select name="security_antiddos" class="btn" required>
						<option value="true">Activado</option>
						<option value="false">Desactivado</option>
					</select>
					<span class="h">Si esta activado las direcciones IP sospechosas se anotaran en un archivo JSON llamado "black_ip" y antes de la inicialización de BeatRock se verificará si la IP del visitante esta en este archivo.</span>
				</p>
			</div>

			<div class="col2">
				<section>
					<p>
						BeatRock le ofrece niveles de encriptación avanzados para proteger información delicada.
					</p>

					<p>
						La clave de encriptación es importante, BeatRock guardará una copia de la misma en un archivo llamado "SECRET" en la carpeta /Kernel/. Si cambia o pierde esta clave toda la información encriptada (Por ejemplo las contraseñas de los usuarios registrados) podrían dañarse.
					</p>

					<p>
						Si selecciona el nivel "Encriptación reversible" podrá usar la función <code>Core::Decrypt</code> para desencriptar información encriptada con este nivel.
					</p>

					<p>
						La "Encriptación reversible" también toma en cuenta su "clave de encriptación" por lo que si cambia o pierde esta clave no podrá desencriptar.
					</p>
				</section>
			</div>
		</section>

		<section>
			<div class="col1">
				<h3>Errores & Logs</h3>

				<p>
					<label>Mostrar información técnica de los errores</label>
					<select name="errors_details" class="btn" required>
						<option value="true">Activado</option>
						<option value="false">Desactivado</option>
					</select>
					<span class="h">Permite mostrar o no la información técnica de los errores. Recomendación: Desactivar al pasar al estado de producción.</span>
				</p>

				<p>
					<label>Ocultar errores</label>
					<select name="errors_hidden" class="btn" required>
						<option value="false">Desactivado</option>
						<option value="true">Activado</option>
					</select>
					<span class="h">Si esta activado BeatRock tratará de ocultar los errores y reanudará la sesión cuando sean solucionados, si los errores no se solucionan se redireccionará al visitante a la página de inicio.</span>
				</p>

				<p>
					<label>Correo electrónico de reportes</label>
					<input type="email" name="errors_email_reports" />
					<span class="h">Los reportes de errores, posibles inyecciones y recuperaciones del sistema se enviarán a esta dirección de correo electrónico.</span>
				</p>

				<p>
					<label>Capturar logs</label>
					<select name="logs_capture" class="btn" required>
						<option value="true">Activado</option>
						<option value="false">Desactivado</option>
					</select>
					<span class="h">Si esta activado BeatRock guardará los logs recibidos en una variable, desactivarlo puede liberar un poco de memoria pero sacrificando los logs.</span>
				</p>

				<p>
					<label>Guardado de logs</label>
					<select name="logs_save" class="btn" required>
						<option value="false">Desactivado</option>
						<option value="onerror" selected>Solo cuando ocurra un error</option>
						<option value="warning">Solo logs de alerta</option>
						<option value="error">Solo logs de error</option>
						<option value="memcache">Solo logs de memcache</option>
						<option value="all">Siempre (No recomendado)</option>
					</select>
					<span class="h">Si esta activado BeatRock guardará los logs recibidos en una variable, desactivarlo puede liberar un poco de memoria pero sacrificando los logs.</span>
				</p>
			</div>

			<div class="col2">
				<section>
					<p>
						La información técnica contiene información acerca del error, la parte del código donde ocurrio, archivo involucrado, posibles soluciones e informacion del visitante.
					</p>

					<p>
						"Ocultar errores" es una opción para aquellas aplicaciones en donde mostrar que ocurrio un error no es la opción.
					</p>

					<p>
						Si no tiene un servidor de correo local (Como Mercury) BeatRock usará el servidor de correo de InfoSmart para enviarle los correos electrónicos de reporte. ¡No se preocupe!
					</p>
				</section>
			</div>
		</section>

		<section>
			<div class="col1">
				<h3>Servidor</h3>

				<p>
					<label>Compresión GZIP</label>
					<select name="server_gzip" class="btn" required>
						<option value="true">Activado</option>
						<option value="false">Desactivado</option>
					</select>
					<span class="h">La compresión GZIP permite comprimir el contenido de su aplicación para que la carga sea más rápida.</span>
				</p>

				<p>
					<label>Solicitar Host/DNS del visitante</label>
					<select name="server_host" class="btn" required>
						<option value="true">Activado</option>
						<option value="false">Desactivado</option>
					</select>
					<span class="h">Si esta activado BeatRock intentará solicitar el nombre Host/DNS del visitante, con ella puede obtener más información del mismo (como la compañia de internet que usa)</span>
				</p>

				<p>
					<label>Auto-detectar zona horaria del visitante</label>
					<select name="server_timezone" class="btn" required>
						<option value="true">Activado</option>
						<option value="false">Desactivado</option>
					</select>
					<span class="h">Si esta activado las funciones de tiempo de PHP funcionaran en la zona horaria del visitante (reemplazando la zona horaria de la aplicación)</span>
				</p>

				<p>
					<label>Uso del protocolo seguro HTTPS</label>
					<select name="server_ssl" class="btn" required>
						<option value="true">Obligar uso</option>
						<option value="null" selected>Opcional</option>
						<option value="false">No usar</option>
					</select>
					<span class="h">Permite decidir de que forma se usará el protolo HTTPS en su aplicación.</span>
				</p>

				<p>
					<label>Recuperación avanzada</label>
					<select name="server_backup" class="btn" required>
						<option value="true">Activado</option>
						<option value="false">Desactivado</option>
					</select>
					<span class="h">Permite usar este sistema para la recuperación automática del archivo de configuración y/o la base de datos.</span>
				</p>
			</div>

			<div class="col2">
				<section>
					<p>
						<iframe width="340" height="191" src="https://www.youtube.com/embed/Mjab_aZsdxw" frameborder="0" allowfullscreen></iframe>
					</p>

					<p>
						Nota: La compresión GZIP puede usar recursos excesivos del CPU.
					</p>

					<p>
						Auto-detectar la zona horaria del visitante le da la posibilidad de representar fechas y tiempos en el horario del visitante.
					</p>

					<p>
						Para usar el protocolo HTTPS es necesario instalar un certificado de seguridad en el servidor web.
					</p>

					<p>
						La recuperación avanzada es un sistema inteligente que le permite a BeatRock restaurar el archivo de configuración y/o la base de datos en caso de que estos sean eliminados. <a href="http://beatrock.infosmart.mx/wiki/Recuperación_avanzada" target="_blank">Más información</a>
					</p>
				</section>
			</div>
		</section>

		<section>
			<div class="col1">
				<h3>Memcached</h3>

				<p>
					<label>Host / Dirección IP</label>
					<input type="text" name="memcache_host" placeholder="localhost" />
					<span class="h">El nombre Host o la dirección IP donde se realizará la conexión al servidor Memcached.</span>
				</p>

				<p>
					<label>Puerto</label>
					<input type="number" name="memcache_port" value="<?=$step['memcache_port']?>" placeholder="11211" />
					<span class="h">El puerto del servidor Memcached, generalmente es <b>11211</b></span>
				</p>
			</div>

			<div class="col2">
				<p>
					Estado de la extensión memcache: <b><?=($system['memcache'] == true) ? 'Activado' : 'Desactivado'?></b>
				</p>

				<p>
					Si no tiene la extensión memcache activada no podrá usar esta característica.
				</p>

				<p>
					<a href="http://es.wikipedia.org/wiki/Memcached" target="_blank">Memcached</a> es un sistema de caché basado en la memoria RAM, tener un servidor activo y configurarlo con BeatRock le permitirá guardar las sesiones e información importante/pesada en el servidor Memcached y de esta forma poder liberar recursos del sistema.
				</p>

				<p>
					Si deja el campo de "Host/Dirección IP" vacio se desactivará esta característica.
				</p>

				<p>
					Puede usar la función <code>_CACHE(key, value)</code> para guardar información en el servidor memcached (Si el servidor esta desactivado la información se guardará en sesiones normales o <code>_SESSION(key, value)</code>)
				</p>
			</div>
		</section>

		<section>
			<a id="send_step2" class="ibtn ibig">Crear archivo de configuración</a>
		</section>
	</form>
</div>