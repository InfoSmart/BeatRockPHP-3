<div class="c2">
	<h2>Selecciona sección</h2>

	<p>
		Selecciona la sección/tabla que deseas administrar:
	</p>

	<?php foreach($table as $r) { ?>
	<a href="%ADMIN%/?admin=<?php echo $r['name']; ?>" class="link clear">
		<label class="left">
			<?php echo $r['translated']; ?>
		</label>

		<label class="right">
			<?php echo $r['name']; ?>
		</label>
	</a>
	<?php } ?>
</div>

<div class="clear">