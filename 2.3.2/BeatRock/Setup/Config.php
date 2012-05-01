<?php
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart © 2011 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

require('Init.php');

if(empty($_SESSION['step2']))
{
	header("Location: ./step2");
	exit;
}

$step = $_SESSION['step2'];
$config = file_get_contents("Configuration");
			
foreach($step as $param => $value)
	$config = str_replace("{" . $param . "}", $value, $config);

$page['name'] = "Archivo de Configuración";
require('Header.php');
?>
<div class="content">
	<p>
		La base de datos ha sido creada con éxito (En caso de que así lo seleccionará).<br />
		A continuación se muestra el texto de su archivo de configuración, copielo y guardelo en <b>"/Kernel/Configuration.php"</b>
	</p>
	
	<center>
		<textarea class="code config" readonly><?php echo $config; ?></textarea><br />
		<a onclick="$('.config').select();">Seleccionar texto</a>
	</center>
	
	<p>
		<b>Nota:</b> Si ha dado clic en el botón "Guardar configuración" del paso 2 y ha llegado aquí, significa que no se ha podido guardar el archivo de configuración automáticamente quizá por falta de permisos (CHMOD 0777).
	</p>
	
	<p>
		<a href="./step3" class="ibtn">Continuar</a>
		<a href="./step2" class="ibtn">Volver al paso 2</a>
	</p>
</div>