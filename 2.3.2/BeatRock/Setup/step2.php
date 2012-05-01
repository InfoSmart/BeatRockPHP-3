<?php####################################################### 					 BeatRock				   	   ######################################################### Framework avanzado de procesamiento para PHP.   ######################################################### InfoSmart � 2011 Todos los derechos reservados. #### http://www.infosmart.mx/						   ######################################################### http://beatrock.infosmart.mx/				   #######################################################require('Init.php');if(is_numeric($P['sec_level']) AND !empty($P['sec_hash'])){	define("NO_FOOTER", true);	exit(Encrypte("BeatRock-InfoSmart-@", $P['sec_level'], $P['sec_hash']));}	file_put_contents("./SECURE", "SECURE - " . IP . " - " . URL);$_SESSION['install']['secure'] = true;$step['mysql_host'] = "localhost";$step['mysql_name'] = "beatrock";$step['site_path'] = str_replace("/Setup/step2", "", URL);$step['site_admin'] = $step['site_path'] . "/imgod";$step['site_resources'] = $step['site_path'] . "/resources/beatrock";$step['site_resources_sys'] = $step['site_path'] . "/resources/system";$step['security_hash'] = Random(80, true, true, true);$step['security_test'] = Encrypte("BeatRock-InfoSmart-@", 4, $step['security_hash']);if(isset($P['save']) OR isset($P['show'])){	foreach($P as $param => $value)		$step[$param] = $value;			if(empty($step['mysql_host']))		$error[] = "El Host para la conexi�n al servidor MySQL no es v�lido.";			if(empty($step['mysql_name']))		$error[] = "El Nombre de usuario para la conexi�n al servidor MySQL no es v�lido.";			if(empty($step['mysql_pass']))		$error[] = "Por seguridad, es necesario que escribas una contrase�a para la conexi�n al servidor MySQL.";			if(empty($step['mysql_name']))		$error[] = "Por favor escribe el nombre de la base de datos.";			if(empty($step['site_path']))		$error[] = "Por favor escribe la direcci�n de la aplicaci�n.";			if(empty($step['site_admin']))		$error[] = "Por favor escribe la direcci�n de la administraci�n.";			if(empty($step['site_resources']))		$error[] = "Por favor escribe la direcci�n de los recursos de tu aplicaci�n.";			if(empty($step['site_resources_sys']))		$error[] = "Por favor escribe la direcci�n de los recursos globales para tus aplicaciones.";			if($step['security_level'] < 0 OR $step['security_level'] > 5 OR !is_numeric($step['security_level']))		$error[] = "Selecciona un sistema de codificaci�n v�lido.";			if(empty($step['security_hash']) OR strlen($step['security_hash']) < 20)		$error[] = "Por favor escribe una clave de codificaci�n con m�s de 20 caracteres.";			if(!empty($step['errors_email_to']) AND !isValid($step['errros_email_to']))		$error[] = "Por favor escribe un correo electr�nico de reportes v�lido.";			if($step['server_limit_load'] < 50 OR $step['server_limit_load'] > 100 OR !is_numeric($step['server_limit_load']))		$error[] = "Selecciona un limite de carga de CPU v�lido.";				if($step['logs_save'] == "error" OR $step['logs_save'] == "warning" OR $step['logs_save'] == "info" OR $step['logs_save'] == "onerror")		$step['logs_save'] = "'$step[logs_save]'";				if(empty($error))		$sql = mysql_connect($step['mysql_host'], $step['mysql_user'], $step['mysql_pass']) or $error[] = "No hemos podido establecer una conexi�n con el Servidor MySQL. Revise que los datos de conexi�n sean correctos y que el servidor se encuentre disponible.";			if(empty($error))		mysql_query("CREATE DATABASE IF NOT EXISTS $step[mysql_name];") or $error[] = "No se ha podido crear la base de datos correctamente, es probable que el usuario que hayas escrito para la conexi�n no tenga los permisos necesarios.";			if(empty($error))		mysql_select_db($step['mysql_name']) or $error[] = "No se ha podido encontrar la base de datos para BeatRock. Este es un error desconocido, intentalo de nuevo m�s tarde.";			if(empty($error))	{		if($P['nodb'] !== "true")			CreateDB($sql);					file_put_contents("../Kernel/Secret_Hash", $step['security_hash']);				if(isset($P['save']))		{			$config = file_get_contents("Configuration");						foreach($step as $param => $value)				$config = str_replace("{" . $param . "}", $value, $config);						$write = file_put_contents("../Kernel/Configuration.php", $config);			file_put_contents("../Kernel/Configuration.Backup.php", $config);						if(!$write)			{				header("Location: ./Config");				exit;			}						header("Location: ./step3");			exit;		}		else if(isset($P['show']))		{			foreach($step as $param => $value)				$_SESSION['step2'][$param] = $value;							header("Location: ./Config");			exit;		}	}}$page['name'] = "Configuraci�n Maestra";require('Header.php');?><div class="content">	<p>		BeatRock precisa de un archivo de configuraci�n para definir algunas de sus funcionalidades, el siguiente paso crear� el archivo de configuraci�n automaticamente acorde al siguiente formulario:	</p>		<div class="of"></div>		<form action="./step2?do=save" method="POST">		<section>			<h2>Base de datos (MySQL)</h2>						<p>				<label for="mysql_host">Host:</label>				<input type="text" name="mysql_host" id="mysql_host" placeholder="localhost" value="<?=$step['mysql_host']?>" required autofocus autocomplete="off" />								<span>Host a donde se realizar� la conexi�n al servidor MySQL. Predeterminado: localhost</span>			</p>						<p>				<label for="mysql_user">Nombre de usuario:</label>				<input type="text" name="mysql_user" id="mysql_user" placeholder="root" value="<?=$step['mysql_user']?>" required autocomplete="off" />								<span>Nombre de usuario para la autenticaci�n con el servidor MySQL.</span>			</p>						<p>				<label for="mysql_pass">Contrase�a:</label>				<input type="password" name="mysql_pass" id="mysql_pass" value="<?=$step['mysql_pass']?>" required autocomplete="off" />								<span>Contrase�a para la autenticaci�n con el servidor MySQL.</span>			</p>						<p>				<label for="mysql_name">Nombre de la base de datos:</label>				<input type="text" name="mysql_name" id="mysql_name" value="<?=$step['mysql_name']?>" required autocomplete="off" />								<span>Nombre de la base de datos, tenga en cuenta que la instalaci�n la crear� automaticamente en caso de que no exista.</span>			</p>						<p>				<label for="mysql_alias">Prefijo de las tablas:</label>				<input type="text" name="mysql_alias" id="mysql_alias" value="<?=$step['mysql_alias']?>" autocomplete="off" />								<span>Si mantendr� varias copias de BeatRock en una sola base de datos escriba un Prefijo para las tablas, esto evitar� un conflicto entre ellas. Por ejemplo: "myapp_".</span>			</p>						<p>				<label>Optimizaci�n al iniciar:</label>								<select name="mysql_optimize" class="btn" required>					<option value="false">Desactivado</option>					<option value="true">Activado</option>				</select>								<span>Si lo activa BeatRock optimizar� la base de datos en cada ejecuci�n. Solo activelo si se causa un error con la base de datos que no se pueda solucionar automaticamente.</span>			</p>						<p>				<label>Reparaci�n al iniciar:</label>								<select name="mysql_repair" class="btn" required>					<option value="false">Desactivado</option>					<option value="true">Activado</option>				</select>								<span>Si lo activa BeatRock reparar� la base de datos en cada ejecuci�n. Solo activelo si se causa un error con la base de datos que no se pueda solucionar automaticamente.</span>			</p>						<p>				<label>Optimizaci�n en error:</label>								<select name="mysql_optimize_error" class="btn" required>					<option value="true">Activado</option>					<option value="false">Desactivado</option>				</select>								<span>En caso de que se produzca un error relacionado con la base de datos, BeatRock optimizar� todas las tablas para intentar reparar el problema.</span>			</p>						<p>				<label>Reparaci�n en error:</label>								<select name="mysql_repair_error" class="btn" required>					<option value="true">Activado</option>					<option value="false">Desactivado</option>				</select>								<span>En caso de que se produzca un error relacionado con la base de datos, BeatRock reparar� todas las tablas para intentar reparar el problema.</span>			</p>		</section>				<section>			<h2>Ubicaci�n de la aplicaci�n</h2>						<p>				<label>Zona de formato de fecha:</label>								<select name="site_country" class="btn" required>					<option value="Pacific/Midway">(GMT-11:00) Midway Island, Samoa</option>					<option value="America/Adak">(GMT-10:00) Hawaii-Aleutian</option>					<option value="Etc/GMT+10">(GMT-10:00) Hawaii</option>					<option value="Pacific/Marquesas">(GMT-09:30) Marquesas Islands</option>					<option value="Pacific/Gambier">(GMT-09:00) Gambier Islands</option>					<option value="America/Anchorage">(GMT-09:00) Alaska</option>					<option value="America/Ensenada">(GMT-08:00) Tijuana, Baja California</option>					<option value="Etc/GMT+8">(GMT-08:00) Pitcairn Islands</option>					<option value="America/Los_Angeles">(GMT-08:00) Pacific Time (US & Canada)</option>					<option value="America/Denver">(GMT-07:00) Mountain Time (US & Canada)</option>					<option value="America/Chihuahua">(GMT-07:00) Chihuahua, La Paz, Mazatlan</option>					<option value="America/Dawson_Creek">(GMT-07:00) Arizona</option>					<option value="America/Belize">(GMT-06:00) Saskatchewan, Central America</option>					<option value="America/Mexico_City" selected>(GMT-06:00) Guadalajara, Mexico City, Monterrey</option>					<option value="Chile/EasterIsland">(GMT-06:00) Easter Island</option>					<option value="America/Chicago">(GMT-06:00) Central Time (US & Canada)</option>					<option value="America/New_York">(GMT-05:00) Eastern Time (US & Canada)</option>					<option value="America/Havana">(GMT-05:00) Cuba</option>					<option value="America/Bogota">(GMT-05:00) Bogota, Lima, Quito, Rio Branco</option>					<option value="America/Caracas">(GMT-04:30) Caracas</option>					<option value="America/Santiago">(GMT-04:00) Santiago</option>					<option value="America/La_Paz">(GMT-04:00) La Paz</option>					<option value="Atlantic/Stanley">(GMT-04:00) Faukland Islands</option>					<option value="America/Campo_Grande">(GMT-04:00) Brazil</option>					<option value="America/Goose_Bay">(GMT-04:00) Atlantic Time (Goose Bay)</option>					<option value="America/Glace_Bay">(GMT-04:00) Atlantic Time (Canada)</option>					<option value="America/St_Johns">(GMT-03:30) Newfoundland</option>					<option value="America/Araguaina">(GMT-03:00) UTC-3</option>					<option value="America/Montevideo">(GMT-03:00) Montevideo</option>					<option value="America/Miquelon">(GMT-03:00) Miquelon, St. Pierre</option>					<option value="America/Godthab">(GMT-03:00) Greenland</option>					<option value="America/Argentina/Buenos_Aires">(GMT-03:00) Buenos Aires</option>					<option value="America/Sao_Paulo">(GMT-03:00) Brasilia</option>					<option value="America/Noronha">(GMT-02:00) Mid-Atlantic</option>					<option value="Atlantic/Cape_Verde">(GMT-01:00) Cape Verde Is.</option>					<option value="Atlantic/Azores">(GMT-01:00) Azores</option>					<option value="Europe/Belfast">(GMT) Greenwich Mean Time : Belfast</option>					<option value="Europe/Dublin">(GMT) Greenwich Mean Time : Dublin</option>					<option value="Europe/Lisbon">(GMT) Greenwich Mean Time : Lisbon</option>					<option value="Europe/London">(GMT) Greenwich Mean Time : London</option>					<option value="Africa/Abidjan">(GMT) Monrovia, Reykjavik</option>					<option value="Europe/Amsterdam">(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna</option>					<option value="Europe/Belgrade">(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague</option>					<option value="Europe/Brussels">(GMT+01:00) Brussels, Copenhagen, Madrid, Paris</option>					<option value="Africa/Algiers">(GMT+01:00) West Central Africa</option>					<option value="Africa/Windhoek">(GMT+01:00) Windhoek</option>					<option value="Asia/Beirut">(GMT+02:00) Beirut</option>					<option value="Africa/Cairo">(GMT+02:00) Cairo</option>					<option value="Asia/Gaza">(GMT+02:00) Gaza</option>					<option value="Africa/Blantyre">(GMT+02:00) Harare, Pretoria</option>					<option value="Asia/Jerusalem">(GMT+02:00) Jerusalem</option>					<option value="Europe/Minsk">(GMT+02:00) Minsk</option>					<option value="Asia/Damascus">(GMT+02:00) Syria</option>					<option value="Europe/Moscow">(GMT+03:00) Moscow, St. Petersburg, Volgograd</option>					<option value="Africa/Addis_Ababa">(GMT+03:00) Nairobi</option>					<option value="Asia/Tehran">(GMT+03:30) Tehran</option>					<option value="Asia/Dubai">(GMT+04:00) Abu Dhabi, Muscat</option>					<option value="Asia/Yerevan">(GMT+04:00) Yerevan</option>					<option value="Asia/Kabul">(GMT+04:30) Kabul</option>					<option value="Asia/Yekaterinburg">(GMT+05:00) Ekaterinburg</option>					<option value="Asia/Tashkent">(GMT+05:00) Tashkent</option>					<option value="Asia/Kolkata">(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi</option>					<option value="Asia/Katmandu">(GMT+05:45) Kathmandu</option>					<option value="Asia/Dhaka">(GMT+06:00) Astana, Dhaka</option>					<option value="Asia/Novosibirsk">(GMT+06:00) Novosibirsk</option>					<option value="Asia/Rangoon">(GMT+06:30) Yangon (Rangoon)</option>					<option value="Asia/Bangkok">(GMT+07:00) Bangkok, Hanoi, Jakarta</option>					<option value="Asia/Krasnoyarsk">(GMT+07:00) Krasnoyarsk</option>					<option value="Asia/Hong_Kong">(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi</option>					<option value="Asia/Irkutsk">(GMT+08:00) Irkutsk, Ulaan Bataar</option>					<option value="Australia/Perth">(GMT+08:00) Perth</option>					<option value="Australia/Eucla">(GMT+08:45) Eucla</option>					<option value="Asia/Tokyo">(GMT+09:00) Osaka, Sapporo, Tokyo</option>					<option value="Asia/Seoul">(GMT+09:00) Seoul</option>					<option value="Asia/Yakutsk">(GMT+09:00) Yakutsk</option>					<option value="Australia/Adelaide">(GMT+09:30) Adelaide</option>					<option value="Australia/Darwin">(GMT+09:30) Darwin</option>					<option value="Australia/Brisbane">(GMT+10:00) Brisbane</option>					<option value="Australia/Hobart">(GMT+10:00) Hobart</option>					<option value="Asia/Vladivostok">(GMT+10:00) Vladivostok</option>					<option value="Australia/Lord_Howe">(GMT+10:30) Lord Howe Island</option>					<option value="Etc/GMT-11">(GMT+11:00) Solomon Is., New Caledonia</option>					<option value="Asia/Magadan">(GMT+11:00) Magadan</option>					<option value="Pacific/Norfolk">(GMT+11:30) Norfolk Island</option>					<option value="Asia/Anadyr">(GMT+12:00) Anadyr, Kamchatka</option>					<option value="Pacific/Auckland">(GMT+12:00) Auckland, Wellington</option>					<option value="Etc/GMT-12">(GMT+12:00) Fiji, Kamchatka, Marshall Is.</option>					<option value="Pacific/Chatham">(GMT+12:45) Chatham Islands</option>					<option value="Pacific/Tongatapu">(GMT+13:00) Nuku'alofa</option>					<option value="Pacific/Kiritimati">(GMT+14:00) Kiritimati</option>				</select>								<span>Seleccione la zona de formato de fecha usada para esta aplicaci�n, la misma afecta a todas las funciones de tiempo de PHP.</span>			</p>						<p>				<label for="site_path">Direcci�n de la aplicaci�n:</label>				<input type="text" name="site_path" id="site_path" value="<?=$step['site_path']?>" required autocomplete="off" />								<span>Escriba la direcci�n de la aplicaci�n sin "http://" al principio ni "/" al final.</span>			</p>						<p>				<label for="site_admin">Direcci�n de la administraci�n:</label>				<input type="text" name="site_admin" id="site_admin" value="<?=$step['site_admin']?>" required autocomplete="off" />								<span>Escriba la direcci�n de la administraci�n de la aplicaci�n sin "http://" al principio ni "/" al final.</span>			</p>						<p>				<label for="site_resources">Direcci�n de los Recursos de la aplicaci�n:</label>				<input type="text" name="site_resources" id="site_resources" value="<?=$step['site_resources']?>" required autocomplete="off" />								<span>Escriba la direcci�n de los recursos (CSS, Imagenes, JavaScript) de la aplicaci�n. Sin "http://" al principio ni "/" al final.</span>			</p>						<p>				<label for="site_resources_sys">Direcci�n de los Recursos globales:</label>				<input type="text" name="site_resources_sys" id="site_resources_sys" value="<?=$step['site_resources_sys']?>" required autocomplete="off" />								<span>Escriba la direcci�n de los recursos globales (CSS, Imagenes, JavaScript) que ser�n usadas en sus aplicaciones. Sin "http://" al principio ni "/" al final.</span>			</p>		</section>				<section>			<h2>Seguridad</h2>						<p>				<label>Modo seguro:</label>								<select name="security_enabled" class="btn" required>					<option value="false">Desactivado</option>					<option value="true">Activado</option>				</select>								<span>El Modo seguro filtra las variables <b>"$_GET, $_POST, $_REQUEST y $_SESSION"</b> antes de poder usarlas o verlas en la aplicaci�n. Este sistema puede ocacionar errores o resultados no deseados en aplicaciones avanzadas.</span>			</p>						<p>				<label>Sistema de codificaci�n:</label>								<select name="security_level" id="security_level" class="btn" required>					<option value="0">Sin codificaci�n</option>					<option value="1">Codificaci�n MD5</option>					<option value="2">Codificaci�n SHA1</option>					<option value="3">Codificaci�n avanzada de SHA1 & SHA256</option>					<option value="4" selected>Codificaci�n avanzada de SHA1 & SHA256 con MD5</option>					<option value="5">Codificaci�n personalizada reversible</option>				</select>								<span>Seleccione el sistema de codificaci�n usada para encriptar cadenas, por ejemplo las contrase�as.</span>			</p>						<p>				<label for="security_hash">Clave de codificaci�n:</label>				<input type="text" name="security_hash" id="security_hash" value="<?=$step['security_hash']?>" required />								<span>La clave de codificaci�n proporciona m�s seguridad a la hora de cifrar cadenas, sin ella las cadenas no ser�n cifradas correctamente as� que procure guardarlo en un lugar seguro.</span>			</p>						<p>				<label>Prueba de codificaci�n:</label>				<input type="text" id="security_test" value="<?=$step['security_test']?>" disabled readonly />								<span>Esta es la cadena "BeatRock-InfoSmart-@" encriptada con su configuraci�n de codificaci�n actual.</span>			</p>		</section>				<section>			<h2>Errores & Logs</h2>						<p>				<label>Ocultar errores de la base de datos:</label>								<select name="errors_hidden" class="btn" required>					<option value="false">Desactivado</option>					<option value="true">Activado</option>				</select>								<span>En caso de que se produzca un error con la base de datos BeatRock intentar� solucionarlo y ocultar el error, de esta manera el usuario no se dar� cuenta de que se ha producido el error. Si activa esta opci�n se activar�n las opciones <b>"Optimizaci�n en error" y "Reparaci�n en error"</b> de la secci�n MySQL. Es recomendable activarlo en el estado final. (Cuando la aplicaci�n este terminada)</span>			</p>						<p>				<label>Reporte de errores:</label>								<select name="errors_report" class="btn" required>					<option value="E_ALL">Todos los errores, advertencias y alertas.</option>					<option value="E_ALL & ~E_NOTICE & ~E_DEPRECATED">Todos los errores y advertencias excepto alertas.</option>					<option value="E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED" selected>Todos los errores excepto advertencias y alertas.</option>					<option value="E_ERROR & E_CORE_ERROR & E_COMPILE_ERROR & E_PARSE & E_RECOVERABLE_ERROR">Solo errores del n�cleo PHP y fatales.</option>					<option value="~E_ALL">No mostrar errores (No recomendado)</option>				</select>								<span>Seleccione que errores desea que capture el reporte de errores de PHP.</span>			</p>						<p>				<label for="errors_email_to">Correo electr�nico de reportes:</label>				<input type="email" name="errors_email_to" id="errors_email_to" value="" autocomplete="off" />								<span>En caso de que se produzca un error se enviar� un correo electr�nico a esta direcci�n. Tenga en cuenta que si no tiene  un servidor de correo activo se usar� la cuenta oficial de BeatRock en el servidor de InfoSmart para enviarlo.</span>			</p>						<p>				<label>Captura de Logs:</label>								<select name="logs_capture" class="btn" required>					<option value="true">Activado</option>					<option value="false">Desactivado</option>				</select>								<span>Seleccione si desea capturar los Logs de BeatRock.</span>			</p>						<p>				<label>Guardado de Logs:</label>								<select name="logs_save" class="btn" required>					<option value="onerror">Solo en errores</option>					<option value="error">Solo errores</option>					<option value="warning">Solo alertas</option>					<option value="info">Solo informativas</option>					<option value="true">Todos</option>					<option value="false">Ninguno</option>				</select>								<span>Seleccione si desea guardar los Logs de BeatRock. Es recomendable desactivarlo ya que puede causar una sobrecarga en el servidor o consumir toda la memoria del disco de almacenamiento.</span>			</p>		</section>				<section>			<h2>Servidor</h2>						<p>				<label>Compresi�n GZIP:</label>								<select name="server_gzip" class="btn" required>					<option value="true">Activado</option>					<option value="false">Desactivado</option>				</select>								<span>Seleccione si desea usar la compresi�n GZIP. Activarla hace a su aplicaci�n m�s r�pida y menos pesada.</span>			</p>						<details>				<summary>�Como funciona GZIP?</summary>								<center>					<iframe width="450" height="250" src="http://www.youtube-nocookie.com/embed/Mjab_aZsdxw?rel=0&hd=1&hl=es&cc_lang_pref=es&cc_load_policy=1" frameborder="0" allowfullscreen></iframe>				</center>			</details>						<p>				<label>Compresi�n HTML:</label>								<select name="server_compression" class="btn" required>					<option value="true">Activado</option>					<option value="false">Desactivado</option>				</select>								<span>Seleccione si desea usar la compresi�n HTML. Activarla quita los espacios inecesarios a la hora de mostrar el HTML de la aplicaci�n haciendolo menos pesado y m�s r�pido de cargar. Puede ocacionar errores si se trabaja con JavaScript en HTML.</span>			</p>						<p>				<label>Solicitud de nombre Host/DNS:</label>								<select name="server_host" class="btn" required>					<option value="true">Activado</option>					<option value="false">Desactivado</option>				</select>								<span>Seleccione si desea activar la solicitud de Host/DNS del usuario, de esta manera podr� obtener m�s informaci�n acerca de el, sin embargo causa un retraso peque�o a la hora de ejecutar la aplicaci�n.</span>			</p>						<p>				<label>Uso del protocolo seguro (HTTPS)</label>								<select name="server_ssl" class="btn" required>					<option value="null">Opcional</option>					<option value="true">Forzar uso</option>					<option value="false">Forzar NO uso</option>				</select>								<span>Seleccione de que manera desea usar el protocolo seguro SSL (HTTPS).</span>			</p>						<p>				<label>Recuperaci�n avanzada:</label>								<select name="server_backup" class="btn" required>					<option value="true">Activado</option>					<option value="false">Desactivado</option>				</select>								<span>Seleccione si desea activar la recuperaci�n avanzada. Activandolo BeatRock detectar� si el archivo de configuraci�n o la base de datos han desaparecido si es as� los restaurar� sin avisarle al usuario del problema.</span>			</p>						<p>				<label>Memoria limite de Apache:</label>								<select name="server_limit" class="btn" required>					<option value="0">Desactivado</option>					<option value="52428800">50 MB</option>					<option value="83886080">80 MB</option>					<option value="104857600">100 MB</option>					<option value="157286400" selected>150 MB</option>					<option value="209715200">200 MB</option>					<option value="314572800">300 MB</option>					<option value="419430400">400 MB</option>					<option value="524288000">500 MB</option>				</select>								<span>Seleccione la memoria limite para el proceso de Apache <b>(httpd)</b>, en caso de que supere la cantidad seleccionada se mostrar� una p�gina de "Sobrecarga".</span>			</p>						<p>				<label for="server_limit_load">Limite de carga del CPU:</label>								<select name="server_limit_load" class="btn" required>					<option value="0">Desactivado</option>					<option value="30">30%</option>					<option value="40">40%</option>					<option value="50">50%</option>					<option value="60">60%</option>					<option value="70">70%</option>					<option value="80" selected>80%</option>					<option value="90">90%</option>					<option value="95">95%</option>				</select>								<span>Seleccione el limite de carga media del CPU (Procesador), en caso de que supere la cantidad seleccionada se mostrar� una p�gina de "Sobrecarga".</span>			</p>		</section>				<section>			<h2>Otros</h2>						<p>				<input type="checkbox" name="nodb" value="true" /> No crear la base de datos, solo el archivo de configuraci�n. (Seleccionelo si ya ha creado la base de datos y las tablas)			</p>		</section>				<p>			<input type="submit" name="save" value="Guardar configuraci�n" title="Crear el archivo de configuraci�n." class="ibtn" />			<input type="submit" name="show" value="Mostrar configuraci�n" title="Mostrar el texto del archivo de configuraci�n." class="ibtn iblue" />		</p>	</form></div>