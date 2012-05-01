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

$page['id'] = "status";
require('Init.php');

$page['name'] = "Error de preparación";
require('Header.php');
?>
<div class="content">
	<p>
		¡UY! Lo sentimos pero se han encontrado errores de preparación, antes de instalar BeatRock por favor corrija los errores encontrados:
	</p>
	
	<section class="center status">
		<p>
			<label class="f">Versión de PHP 5.3+:</label>
			<?php echo $status['php']; ?>
		</p>
		
		<p>
			<label class="f">Permisos de escritura en el directorio "/Setup/":</label>
			<?php echo $status['setup']; ?>
		</p>
		
		<p>
			<label class="f">Permisos de escritura en el directorio "/Kernel/":</label>
			<?php echo $status['kernel']; ?>
		</p>
		
		<p>
			<label class="f">Permisos de lectura en el archivo "/Setup/Configuration":</label>
			<?php echo $status['config']; ?>
		</p>
		
		<p>
			<label class="f">Permisos de lectura en el archivo "/Setup/Database":</label>
			<?php echo $status['db']; ?>
		</p>
		
		<p>
			<label class="f">Permisos de lectura en el archivo "/Setup/Htaccess":</label>
			<?php echo $status['htaccess']; ?>
		</p>
		
		<p>
			<label class="f">Modulo cURL:</label>
			<?php echo $status['curl']; ?>
		</p>
		
		<p>
			<label class="f">Modulo JSON:</label>
			<?php echo $status['json']; ?>
		</p>
	</section>
	
	<p>
		<a href="./" class="ibtn">Intentarlo nuevamente</a>
	</p>
</div>