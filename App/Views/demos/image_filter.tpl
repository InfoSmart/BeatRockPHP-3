<div class="wrapper">
<div class="content">
	<h1>Módulo Gd: Aplicar filtro a una imagen.</h1>

	<form action="%PATH_NOW%" method="POST" enctype="multipart/form-data">
		<p>
			<label for="str">Selecciona una imagen:</label>
			<input type="file" name="image" required accept="image/png, image/jpeg" />
		</p>

		<p>
			<label for="filter">Selecciona un filtro:</label>
			<select name="filter">
				<option value="NEGATE">Negativo</option>
				<option value="GRAYSCALE">Escala de grises</option>
				<option value="EDGEDETECT">Resaltar bordes</option>
				<option value="EMBOSS">Relieve</option>
				<option value="MEAN_REMOVAL">Superficial</option>
			</select>
		</p>

		<p>
			<input type="submit" value="Enviar" />
		</p>
	</form>

	<p>
		Asegurate de ver el código fuente del ejemplo para saber como hacerlo :)
	</p>

	<p>
		<a href="%PATH%/demos/">Atras</a>
	</p>
</div>

<style>
input[type='text']
{
	border-radius: 5px;
	padding: 10px 5px;
	width: 500px;
}

.result
{
	border: 2px solid #D8D8D8;
	border-radius: 3px;
	padding: 10px;
}
</style>

<style>
h1
{
	font-family: "Segoe UI Light", Tahoma, Arial;
	font-weight: bold;
}

footer
{
	border-top: 1px solid #D8D8D8;
	color: gray;
	font-size: 12px;
	margin-top: 10px;
	padding: 10px 0;
}
</style>