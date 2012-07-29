<!--
	Contenido de ejemplo
-->
<div class="content">
	<h2>
		<!--
			Ejemplo de módulos propios.
			Para ver el código visita /Kernel/Modules/Site/MySite.php
			(también para ver porque no se traduce automaticamente...)
		-->
		<?=MySite::HelloWorldLang()?>
	</h2>

	<div class="c1">
		<p>
			%regards%
		</p>
		
		<p>
			%remember.path1% %PATH% %remember.path2% <b><?=PATH?></b>
		</p>

		<p>
			%watch.vars1% <b>"<?=$hello?>"</b> %watch.vars2% <b>"$$hello$$"</b>
		</p>

		<p>
			%have.felt% <b>#TEMPLATES#index.tpl</b>
		</p>

		<p>
			%view.examples% <a href="%PATH%/demos/">%examples%</a>.
		</p>

		<p>
			<!--
				Nueva forma de usar constantes.
			-->
			#ROOT#
		</p>
	</div>

	<div class="c2">
		<!--
			Sistema de traducción en tiempo real, para ver más detalles
			vaya a /resources/beatrock/js/functions.page.js
		-->
		<p>
			<a data-lng="es">Español</a>
			<a data-lng="en">English</a>
			<a data-lng="pt">Português</a>
		</p>

		<p>
			%language.live%
		</p>
	</div>
</div>

<!--
	Código CSS de la página actual [SOLO DEMOSTRACIÓN] (Puede borrarlo)
	Para ver el código CSS de todas las páginas, visita /resources/system/style.css
-->
<style>
/* Agregando fuente "Open Sans" de http://google.com/webfonts */
@import url(http://fonts.googleapis.com/css?family=Open+Sans:300,400,700,600);

/* Predeterminado */

a
{
	color: blue;
}

/* Cabecera */
header
{
	background: #F5F5F5;
	border-bottom: 1px solid #D8D8D8;
	padding: 10px 0;
}

/* Contenido */
h1
{
	color: #0489B1;
	font-family: "Open Sans", "Segoe UI Light", Tahoma, Arial;
	font-size: 35px;
	font-weight: 300;
	line-height: 45px;
	text-shadow: 0 0 3px rgba(32, 126, 207, .2);
}

.content h2
{
	font-family: "Open Sans", Tahoma, Arial;
	font-size: 25px;
	font-weight: 400;
}

.content a[data-lng]
{
	border-bottom: 1px solid #F2F2F2;
	display: block;
	padding: 8px 0;
	text-align: center;
}

.content a[data-lng]:last-child
{
	border-bottom: 0;
}

/* Contenido - Columnas */
.content .c1
{
	float: left;
	width: 660px;
}

.content .c2
{
	float: right;
	width: 300px;
}

/* Pie de página */
footer
{
	border-top: 1px solid #D8D8D8;
	color: gray;
	font-size: 11px;
	margin-top: 10px;
	padding: 10px 0;
}
</style>