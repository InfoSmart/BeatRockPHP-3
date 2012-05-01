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

if(!empty($G['license_name']) AND !empty($G['license_url']))
{
	Io::Write("../LICENSE.txt", "$G[license_name]\r\n$G[license_url]");
	$license = true;
}

/*
if($G['do'] == "ok")
{
	Io::EmptyDir('../Setup/', true);
	exit("OK");
}
*/

$page['name'] = "¡Hagamos magia!";
require('./Header.php');
?>
<div class="content">
	<h2>¡Todo listo! Hora de programar ;)</h2>
	
	<p>
		La instalación de BeatRock ha terminado con éxito y ahora puede desarrollar con todo su poder y recuerde ¡es libre! Haga lo que quiera con el, cree sus aplicaciones y mejore la web.
	</p>
	
	<?php if($license == true) { ?>
	<div class="box-correct block">
		La información de tu licencia "<?php echo $G['license_name']; ?>" ha sido guardada en el directorio raiz de BeatRock.
	</div>
	<?php } ?>
	
	<h3>¿Ahora que?</h3>
	
	<p>
		- ¡Empieza a desarrollar tu aplicación!<br />
		- Visita la sección de <b><a href="http://dev.infosmart.mx/forum/index.php?board=13.0" target="_blank">"Guías y tutoriales"</a></b> en nuestro foro para obtener más información acerca del funcionamiento de BeatRock.<br />
		- Lee los comentarios en los archivos del Kernel, así entenderás su funcionamiento.<br />
		- Te recomendamos <a href="http://notepad-plus-plus.org/" target="_blank">Notepad++</a> o <a href="http://www.sublimetext.com/2" target="_blank">Sublime Text 2</a> como IDE para desarrollar en PHP.<br />
		- Visita <a href="http://www.html5rocks.com/es/" target="_blank">HTML 5 Rocks</a> para ver de lo que es posible HTML 5.<br />
		- <b>¿Te gusta?</b> Comprame un café por <a onclick="$('#coffe').submit();">Paypal</a>.
	</p>
	
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="coffe" target="_blank">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="3QKEV9GQXTF62">
	</form>
	
	<p>
		<a id="finish" class="ibtn">Continuar y eliminar la instalación.</a>
	</p>
</div>
<?php require('Footer.php'); ?>