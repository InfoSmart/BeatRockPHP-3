<div class="c2">
	<section>
		<h2>%translated%</h2>

		<div class="table-values">
			<?php if($cc > 0) { ?>
			<table>
				<tbody>
					<tr>
						<td><?php echo FixName($fields[0], $fields[0]); ?> (<?php echo $fields[0]; ?>)</td>
						<td><?php echo FixName($fields[1], $fields[1]); ?> (<?php echo $fields[1]; ?>)</td>
						<?php if(!empty($fields[2])) { ?><td><?php echo FixName($fields[2], $fields[2]); ?> (<?php echo $fields[2]; ?>)</td><?php } ?>
						<td>Acciones</td>
					</tr>

					<?php while($row = mysql_fetch_assoc($sql)) { ?>
					<tr>
						<td><?php echo FixName($row[$fields[0]], $fields[0]); ?> <label>(<?php echo $row[$fields[0]]; ?>)</label></td>
						<td><?php echo $row[$fields[1]]; ?></td>
						<?php if(!empty($fields[2])) { ?><td><?php echo $row[$fields[2]]; ?></td><?php } ?>
						<td>
							<a>Eliminar</a> - <a>Editar</a>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
			<?php } else { echo '<p>No hay valores.</p>'; } ?>
		</div>
	</section>

	<section>
		<h2>Agregar nuevo valor</h2>

		<form action="%ADMIN%/?admin=%sec%&do=new" method="POST" class="form1">
			<?php
			foreach($fields as $fi)
			{
				if($fi == "id")
					continue;
			?>
			<p>
				<label for="<?php echo $fi; ?>"><?php echo FixName($fi); ?> (<?php echo $fi; ?>)</label>
				<input type="text" name="<?php echo $fi; ?>" id="<?php echo $fi; ?>" />
			</p>
			<?php } ?>

			<p>
				<input type="submit" value="Enviar" class="btn1" />
			</p>
		</form>
	</section>
</div>

<div class="clear"></div>