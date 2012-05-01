<div class="wrapper">
<div class="content">
	<h1>Módulo Core: Códigos BBC y Smilies.</h1>
	
	<form action="%PATH_NOW%" method="POST">
		<p>
			<label for="str">Escribe un mensaje en BBC:</label>
			<input type="text" name="str" value="[b]Hola[/b], [i]Woots[/i], [url=http://www.infosmart.mx/]InfoSmart[/url], :D, :(" required />
		</p>

		<p>
			<input type="checkbox" name="smilies" value="true" checked /> Usar caritas.
		</p>

		<p>
			<input type="submit" value="Enviar" />
		</p>
	</form>

	<?php if(!empty($str)) { ?>
	<p>
		<?=$str?>
	</p>
	<?php } ?>

	<p>
		Asegurate de ver el código fuente del ejemplo para saber como hacerlo :)
	</p>
</div>

<style>
h1
{
	font-weight: normal;
	margin-bottom: 30px;
}

input[type='text']
{
	border-radius: 5px;
	padding: 10px 5px;
	width: 500px;
}

footer
{
	border-top: 1px solid #D8D8D8;
	color: gray;
	font-size: 12px;
	margin-top: 10px;
	padding: 5px;
}
</style>