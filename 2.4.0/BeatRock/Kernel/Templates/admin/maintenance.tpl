<div class="c2">
	<div class="box-correct">
		La acción se ha completado con éxito.
	</div>

	<section>
		<h2>Mantenimiento</h2>
		<br />
		<a href="%ADMIN%/?page=maintenance&do=maintenance" title="Vaciar los directorios 'Backups, Temp, Logs y Cache'." class="btn1">Eliminar basura</a>
		<a href="%ADMIN%/?page=maintenance&do=maintenance_db" title="Vacia la tabla 'site_visits' y 'site_errors'." class="btn1">Vaciar información basura</a>
		<a href="%ADMIN%/?page=maintenance&do=optimize_db" class="btn1">Optimizar la base de datos</a>
		<a href="%ADMIN%/?page=maintenance&do=backup_db" class="btn1">Crear un backup de la DB</a>
		<a href="%ADMIN%/?page=maintenance&do=backup_app" class="btn1">Crear un backup de la aplicación</a>
		<a href="%ADMIN%/?page=maintenance&do=backup_total" class="btn1">Crear un backup total.</a>
	</section>

	<section>
		<h3>Optimizar tablas</h3>

		<form action="%ADMIN%/?page=maintenance&action=optimize" id="optimize" method="POST">
			<p>
				Selecciona las tablas a optimizar:<br /><br />
				<a id="select" data-form="optimize">Seleccionar todas</a>
			</p>
			
			<?php foreach($examine['tables'] as $table) { ?>
			<div class="box">
				<input type="checkbox" name="tables[]" value="<?php echo $table['name']; ?>" /> <?php echo $table['name']; ?><br />
			</div>
			<?php } ?>

			<div class="clear"></div>

			<p>
				<input type="submit" value="Continuar" class="btn1" />
			</p>
		</form>
	</section>

	<section>
		<h3>Ejecutar consulta</h3>

		<form action="%ADMIN%/?page=maintenance&action=query" method="POST">
			<p>
				<textarea name="query" class="code" style="width: 97%; height: 100px;"></textarea>
			</p>

			<p>
				<input type="submit" value="Ejecutar" class="btn1" />
			</p>
		</form>

		<?php if(isset($sql)) { ?>
		<h3>Resultado</h3>
		<?php if($sql == false) { echo 'La consulta no se ha ejecutado correctamente.'; } else { ?>

		<p>
			<b>Recurso:</b><br />
			<pre><?php print_r($sql['assoc']); ?></pre>
		</p>
		
		<p>
			<b>Rows:</b><br />
			<?php echo $sql['rows']; ?>
		</p>
		<?php } } ?>
	</section>
</div>

<div class="clear"></div>