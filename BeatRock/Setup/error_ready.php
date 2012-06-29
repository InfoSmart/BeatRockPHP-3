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

$page['id'] = "ready";
require('Init.php');

$page['name'] = "Error de instalación";
require('Header.php');
?>
<div class="content">
	<h2>Al parecer ya estamos listos...</h2>
	
	<p>
		Lo sentimos, pero BeatRock ha encontrado que el archivo de configuración ya existe o alguien más ya se encuentra en la instalación, como metodo de seguridad se le ha denegado el acceso.
	</p>
	
	<h3>¿Qué puedo hacer?</h3>
	
	<p>
		- Si su aplicación ya esta lista, elimine el directorio de instalación <b>"/Setup/"</b>.<br />
		- Elimine el archivo de configuración <b>"/Kernel/Configuration.php"</b>.<br />
		- Elimine el archivo de seguridad <b>"/Setup/SECURE"</b>.<br />
		- Salga de esta página y vaya por un café.
	</p>
</div>