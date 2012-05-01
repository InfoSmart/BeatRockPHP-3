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

function CheckReady()
{
	if(file_exists('../Kernel/Configuration.php') OR file_exists('./SECURE'))
	{
		if($_SESSION['install']['secure'] !== true)
		{
			header("Location: ./error_ready");
			exit;
		}
	}
}

CheckReady();

if(!empty($G['license_name']) AND !empty($G['license_url']))
{
	Io::Write("../LICENSE.txt", "$G[license_name]\r\n$G[license_url]");
	$license = true;
}

if($G['do'] == "ok")
{
	Io::EmptyDir('../Setup/', true);
	exit("OK");
}

$page['name'] = "¡Hagamos magia!";
require('./Header.php');
?>
<div class="content">
	<h2>¡Todo listo! Hora de programar ;)</h2>
	
	<p>
		La instalación de BeatRock ha terminado con éxito y ahora puede disfrutar de todo su poder y recuerde ¡es libre! Haga lo que quiera con el, cree sus aplicaciones y mejore/revolucione la web.
	</p>
	
	<?php if($license == true) { ?>
	<div class="box-correct block">
		La información de tu licencia "<?php echo $G['license_name']; ?>" ha sido guardada en el directorio raiz de BeatRock.
	</div>
	<?php } ?>
	
	<details>
		<summary class="h3">Para comenzar</summary>
		
		<h4>- Plantillas</h4>
		
		<p>
			BeatRock usa un sistema de plantillas (.TPL) para mantener un orden entre código PHP y HTML sin embargo tenga en cuenta que en las plantillas es posible usar PHP.
		</p>
		
		<p>
			Las plantillas se localizan en el directorio <b>"/Kernel/Templates/"</b>, para comenzar abra con un <a href="http://notepad-plus-plus.org/" target="_blank" title="Notepad++">editor de texto PHP</a> el archivo <b>"index.tpl"</b> notese que si se dirije a la <a href="<?php echo PATH; ?>" target="_blank">página principal</a> verá el contenido de la plantilla.
		</p>
		
		<p>
			Las plantillas se cargan automaticamente y se usa la variable <b class="php">$page['id']</b> para definir el nombre de la plantilla a usar, notese que si abre <b>"index.php"</b> en el directorio raiz de BeatRock verá la línea <b class="php">$page['id'] = "index";</b> indicando que se usará la plantilla "index.tpl".
		</p>
		
		<p>
			Como habrá notado en el archivo "index.php" no es necesario llamar a una función para cargar la plantilla, BeatRock lo hará automaticamente si detecta la variable <b class="php">$page['id']</b>.
		</p>
		
		<h4>- Plantillas: "Acceso corto a páginas"</h4>
		
		<p>
			Anteriormente en BeatRock para crear distintas páginas (Ejemplo: index, about, news) era necesario crear un archivo PHP para cada una y poner la variable <b class="php">$page['id']</b> dentro de ellas para usar su respectiva plantilla. Este proceso resultaba algo tedioso y desordenado en caso de que la aplicación tuviera varias páginas y tuviera solamente contenido.
		</p>
		
		<p>
			Para solucionar esto hemos implementado el sistema de <b>"Acceso corto a páginas"</b> en la cual si es activado las páginas serán administradas por la tabla <b>"site_pages"</b> en la base de datos, de esta forma podrá administrar sus páginas desde la administración y sin crear un archivo PHP para cada una.
		</p>
		
		<p>
			Cabe mencionar que este sistema <b class="red">no esta terminado</b> y por lo tanto el uso de comodines "{*}" no funciona, más si la obtención de variables de externas (?woot=true&sample=true). Por otra parte, el uso de este sistema se recomienda a usuarios semi expertos que deseen un orden en sus páginas, de otra manera este sistema puede ser aún más tedioso que crear una página PHP para cada plantilla.
		</p>
		
		<p>
			Para activar este sistema es necesario que pueda usar el archivo <b>.htaccess</b> en su servidor web, abrirlo y descomentar la linea:<br /><b class="php">RewriteRule ^(([A-Za-z0-9\-_]+/)*[A-Za-z0-9\-_]+)?$ ./Kernel/Pages.php?id=$1</b> (Quitando el caracter # al inicio de la linea).
		</p>
		
		<p>
			Activandolo toda petición de dirección hacia su página será "redireccionada" al archivo <b>"/Kernel/Pages.php"</b> en donde podrá notar que hace una petición a la tabla "site_pages" (Función: <b class="php">Site::getPage</b>) para ver si la página existe, si no es así mostrar una página de Error 404.
		</p>
		
		<p>
			Cabe mencionar que si la página tendrá código PHP "extra" (Por ejemplo, si es una petición de una noticia, el PHP "extra" deberá ser la consulta a la base de datos para obtener tal noticia) el mismo se deberá establecer con una condición <b class="php">if</b> dentro del archivo "Pages.php".
		</p>
		
		<p>
			La variable para obtener la petición en el archivo Pages.php es <b class="php">$G['id']</b> o si lo prefiere, puede usar la variable <b class="php">$page['id']</b> que se define al haber una página con esta petición en la base de datos. Por la cual, un ejemplo para establecer código PHP "extra" sería <b class="php">if($page['id'] == "news") { echo $page['name']; }</b>
		</p>		
		
		<p>
			En caso de que la petición contenga un directorio <b>(Por ejemplo <?php echo PATH; ?>/news/woot)</b> se deberá establecer como valor de la columna <b>"request"</b> dentro de una nueva página en la tabla "site_pages" de esta manera <b>"news/woot"</b>.
		</p>
		
		<p>
			Si la página solicitada cuenta con parametros externos (?woot=true&sample=true) las mismas serán incluidas dentro de la variable <b class="php">$page['params']</b> como un Array. Por ejemplo si se solicita la página <b>"<?php echo PATH; ?>/news?id=300&title=Woots"</b> los parametros externos serían <b class="php">$page['params']['id']</b> y <b class="php">$page['params']['title']</b>.
		</p>
	</details>
	
	<details>
		<summary class="h3">Páginas</summary>
		
		<h4>Cabecera</h4>
		
		<p>
			Como todo desarrollador web conoce, un sitio web se divide practicamente en 3 secciones: <b>Cabecera, cuerpo y pie de página</b>. Y BeatRock conoce esta estructura muy bien, es por ello que dentro de la carpeta <b>"/Kernel/Headers/"</b> encontrarás la cabecera, subcabecera y el pie de página de tu aplicación web.
		</p>
		
		<p>
			Predeterminadamente BeatRock incluye la cabecera (Header.php) recomendada por la W3C y que es amigable con distintos navegadores web y robots de indexación. Recomendamos no editar la cabecera principal a menos que sea necesario, si desea agregar archivos de recursos (CSS, JavaScript, etc) le recomendamos usar las siguientes funciones:
		</p>
		
		<p>
			<b class="php">Tpl::addjQuery()</b> -> Agrega los archivos correspondientes para implementar jQuery en la página, por predeterminado se incluye automaticamente.
		</p>
		
		<p>
			<b class="php">Tpl::addMeta($name, $content, $type = 'name')</b> -> Agrega una Meta Etiqueta. Por ejemplo, si deseas agregar la meta etiqueta<br /><b class="php"><?php echo CleanText('<meta itemprop="name" content="InfoSmart">'); ?></b> en BeatRock la agregarias de esta manera: <b class="php">Tpl::addMeta("name", "InfoSmart", "itemprop");</b>
		</p>
		
		<p>
			<b class="php">Tpl::addStyle($file, $rel = 'stylesheet', $id, $media)</b> -> Agrega un archivo de estilo CSS. Ejemplo: <b class="php">Tpl::addStyle('http://resources.infosmart.mx/infosmart/css/style.page.css');</b>.
		</p>
		
		<p>
			<b class="php">Tpl::myStyle($file, $system = false, $external = false)</b> -> Agrega un archivo de estilo CSS que se encuentre dentro de tu carpeta de recursos <b>(<?php echo RESOURCES; ?>/css/)</b>. Ejemplo: <b class="php">Tpl::myStyle('style.page');</b>
		</p>
		
		<p>
			<b class="php">Tpl::addScript($file, $async = false, $id)</b> y <b class="php">Tpl::myScript($file, $system = false, $external = false)</b> -> Lo mismo que las funciones de arriba pero con archivos de JavaScript.
		</p>
		
		<p>
			<b class="php">Tpl::addVar($param, $value, $var = true)</b> -> Agrega una variable o función en JavaScript para que puedas usarlo después en otros archivos JavaScript. Ejemplo: <b class="php">Tpl::addVar("My_Name", "Kolesias123");</b> -> <b class="php"><?php echo CleanText('<script>alert(My_Name);</script>'); ?></b>
		</p>
		
		<p>
			<b class="php">Tpl::addStuff($html)</b> -> Agrega el código HTML indicado a la cabecera.
		</p>
		
		<p>
			<b class="php">Tpl::IETask($name, $url, $icon = "")</b> -> Agrega una accion para que el navegador "Internet Explorer 9+" pueda ejecutarlo en caso de que tu sitio sea anclado a la barra de tareas de Windows 7. Ejemplo: <b class="php">Tpl::IETask('¡Últimas noticias!', 'http://www.infosmart.mx/news');</b>
		</p>
		
		<p>
			<b class="php">Tpl::JavaAction($action)</b> -> Ejecuta una acción JavaScript al finalizar de cargar la página. Ejemplo: <b class="php">Tpl::JavaAction('alert("Woots"); ');</b>
		</p>
		
		<p>
			<b class="php">Tpl::JavaAlert($msg)</b> -> Ejecuta una alerta JavaScript al finalizar de cargar la página. Ejemplo: <b class="php">Tpl::JavaAlert("Woots");</b>
		</p>
		
		<h4>Variable de página.</h4>
		
		<p>
			Para fácilitar el sistema de ajustes de página BeatRock cuenta con "La variable de página" (<b class="php">$page</b>) que define los ajustes que tendra la página actual, por ejemplo:
		</p>
		
		<p>
			<b class="php">$page['id']</b> -> Nombre de la plantilla a usar en la página.
		</p>
		
		<p>
			<b class="php">$page['name']</b> -> Nombre de la página actual que se mostrará en vez del eslogan en el titulo de la página. Ejemplo: <b class="php">$page['name'] = "Noticias";</b> -> <b><?php echo SITE_NAME . " " . $site['site_separation'] . " Noticias"; ?></b>
			
		</p>
		
		<p>
			<b class="php">$page['site_name']</b> -> Nombre de la página actual que reemplazará todo el titulo de la página. Ejemplo: <b class="php">$page['site_name'] = "¡Wooots!";</b> -> <b>¡Wooots!</b>
		</p>
		
		<p>
			<b class="php">$page['header']</b> Valor booleano (true/false) que define si se incluira la cabecera.
		</p>
		
		<p>
			<b class="php">$page['footer']</b> Valor booleano (true/false) que define si se incluira el pie de página.
		</p>
		
		<p>
			<b class="php">$page['subheader']</b> En caso de se incluya la cabecera, define que Subcabecera se usará. Si se deja en blanco se usará "SubHeader", para definir ninguna se debe definir su valor como "none".
		</p>
	</details>
	
	<details>
		<summary class="h3">Preguntas al desarrollador</summary>
		
		<h4>¿Como has creado BeatRock?</h4>
		
		<p>
			Con conocimientos en PHP y un apróximado de 2 años dentro de la programación. BeatRock no es más que de una manera natural, una recolección de las funciones más importantes y necesarias a la hora de crear una aplicación web y todo un sistema inteligente para detectar errores y mantener la aplicación en buen estado sin que tu lo estes vigilando todo el tiempo.
		</p>
		
		<h4>¿Qué quieres lograr con BeatRock?</h4>
		
		<p>
			Qué los desarrolladores web (especialmente Mexicanos ¬¬) puedan crear aplicaciónes web de una manera más sencilla, rápida y con lo último en cuanto a tecnología web (PHP 5, HTML 5, CSS 3) además de usar politicas y directivas para mantener un orden en sus proyectos y un éxito en ellos. ¡Evolución y actualización!
		</p>
		
		<h4>¿Porque no usas el nuevo MySQLi?</h4>
		
		<p>
			<a href="http://php.net/manual/es/book.mysqli.php" target="_new">MySQL Improved</a>... Después de una investigación cientifica exhaustiva llegue a la conclusión de que las únicas mejoras "grandes" de esta extensión son que contiene un sistema de funcionamiento más fácil y completo y que se puede llamar por POO (Programación Orientada a Objetos), según varios desarrolladores en cuanto a rendimiento, sigue igual que el MySQL que todos conocemos.
		</p>
		
		<p>
			Por la cual y debido a unas dificultades que tuve al intentar poner MySQLi en el modulo MySQL de BeatRock, pues decidi no hacerlo ya que practicamente sería algo un poco inecesario. Es más, MySQLi es casi como manejear MySQL de BeatRock, por POO y toda la cosa... ¡Arf!
		</p>
		
		<p>
			En todo caso, puedes intentar implementarlo en BeatRock o usarlo por POO natural.
		</p>
		
		<h4>¿Porque la base de datos y el código esta en Ingles?</h4>
		
		<p>
			Como algunos sabrán soy originario de México y aunque sería "simbolico" hacer todo en español pues practicamente no se ven tan bien y podría haber dificultades a la hora de desarrollar. Miren la ventaja ¡Podrán aprender ingles más rápido y fácil!
		</p>
	</details>
	
	<h3>¿Ahora que?</h3>
	
	<p>
		- ¡Empieza a desarrollar tu aplicación!<br />
		- Lee la <a href="http://beatrock.infosmart.mx/docs">documentación</a> de BeatRock.<br />
		- Lee los comentarios en los archivos del Kernel, así entenderás su funcionamiento.<br />
		- Descarga <a href="http://notepad-plus-plus.org/" target="_blank">Notepad++</a> o <a href="http://www.sublimetext.com/2" target="_blank">Sublime Text 2</a> para desarrollar en PHP.<br />
		- Visita <a href="http://www.html5rocks.com/es/" target="_blank">HTML 5 Rocks</a> para ver de lo que es posible HTML 5.<br />
		- ¿Te gusta? Comprame un café por <a onclick="$('#coffe').submit();">Paypal</a>.
	</p>
	
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="coffe" target="_blank">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
		">
	</form>
	
	<p>
		<a id="finish" class="ibtn">Continuar y eliminar la instalación.</a>
	</p>
</div>
<?php require('Footer.php'); ?>