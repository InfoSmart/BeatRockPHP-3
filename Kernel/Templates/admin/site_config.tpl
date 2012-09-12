<div class="c2">
	<h2>Configuración del sitio</h2>

	<div class="box-correct">
		Los cambios se han guardado con éxito.
	</div>

	<p>
		<input type="submit" value="Guardar cambios" form="form" />
	</p>

	<form action="%ADMIN%/actions/save.php?type=site_config" method="POST" class="col" id="form">
		<? 
		foreach($site as $key => $value) 
		{
			$translate = Core::Translate(str_ireplace('_', ' ', $key));
		?>
		<p>
			<label><?=Core::FixText($translate)?></label>
			<input type="text" name="<?=$key?>" value="<?=$value?>" />
		</p>
		<? } ?>
	</form>

	<p>
		<input type="submit" value="Guardar cambios" form="form" />
	</p>
</div>
</div>