<div class="wrapper">
<div class="content">
	<h1>Módulo Core: Transforma texto con direcciones web a links.</h1>

	<form action="%PATH_NOW%" method="POST">
		<p>
			<label for="str">Escribe algo con una dirección web:</label>
			<input type="text" name="str" value="¡Me gusta http://beatrock.infosmart.mx/! Y también http://www.infosmart.mx/" required />
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