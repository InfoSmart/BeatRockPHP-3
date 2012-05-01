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

$page['name'] = "Bienvenido";
$page['id'] = "index";
require('Header.php');
?>
<div class="content">
	<h2>Hagamos magia con PHP.</h2>
	
	<p>
		<b>¡Bienvenid@!</b> ¿Está listo para máximizar su potencial en PHP, disminuir su tiempo de trabajo y crear aplicaciones interactivas y potentes?<br />
		BeatRock es un Framework en PHP que fácilita el trabajo a los desarrolladores de aplicaciones web haciendo a PHP "más hermoso" con herramientas útiles y potentes que pueden servirle durante su trabajo.
	</p>
	
	<p>
		BeatRock usa la tecnología de PHP 5 para hacer aplicaciones sorprendentes y compatibles con distintos navegadores web, robots de búsqueda y una buena amistad con HTML 5 y CSS 3. Es sencillo, fácil de manejar y que al igual que en PHP es solo cuestión de practica para encontrar su potencial y usarlo al máximo.
	</p>
	
	<p>
		Usando los estandares recomendados de varias compañias y organizaciones que crean el futuro de la Internet hemos creado BeatRock, además su código es sencillo y de fácil comprensión para los desarrolladores semi-expertos en PHP 5 lo cual hace su edición más fácil.
	</p>
	
	<p>
		El objetivo final de BeatRock es que los desarrolladores puedan crear aplicaciones web más interactivas y más actualizadas en cuanto a estandares de tecnología web y que además puedan proporcionala de manera gratuita y libre.
	</p>
	
	<p>
		BeatRock esta bajo la licencia de <a href="http://creativecommons.org/licenses/by-sa/2.5/mx/" target="_blank">Creative Commons "Atribución-Licenciamiento Recíproco"</a> que le permite <b>usarla, editarla, venderla, olerla, comerla, casarse con ella y demás...</b> ¡Haga lo que quiera con BeatRock y evolucione la Internet!
	</p>
	
	<p>
		Esta instalación le ayudará a crear la base de datos, el archivo de configuración y ajustar los datos de su aplicación (Nombre, descripción, logo, etc). <b>¡No es una CMS!</b> Es un kit de desarrollo (Framework) ;)
	</p>
	
	<div class="version">
		<center><h2>Información</h2></center>
		
		<li><b>Nombre código:</b> <?=$Info['code']?></li>
		<li><b>Versión:</b> <?=$Info['version.revision']?></li>
		<li><b>Fase:</b> <?=$Info['version.fase']?></li>
		<li><b>Fecha de creación:</b> <?=$Info['date']?></li>
		<li><b>Hora de creación:</b> <?=$Info['date_hour']?></li>
		<li><b>Nombre:</b> <?=$Info['version.code']?></li>

		<p class="center">
			<span><?=CheckRelease()?></span>
		</p>
	</div>
	
	<p class="center">
		<a href="./step2" class="ibtn">¡Comenzar instalación!</a><br />
	</p>
	
	<figure class="res">
		<a href="http://www.w3schools.com/html5/default.asp" target="_blank" title="HTML 5"><img src="http://www.w3.org/html/logo/downloads/HTML5_Logo_512.png" /></a>
		<a href="http://php.net/?beta=1" target="_blank" title="PHP"><img src="<?php echo RESOURCES_INS; ?>/system/setup/PHP.png" /></a>
		<a href="http://www.opensource.org/" target="_blank" title="Open Source"><img src="http://www.opensource.org/files/garland_logo.png" /></a>
	</figure>
</div>