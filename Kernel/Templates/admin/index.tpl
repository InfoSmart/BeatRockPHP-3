<div class="c2">
	<h2>Estadísticas</h2>

	<section>
		<div class="count_big">
			<label class="left">Visitas el día de hoy:</label>
			<label class="right">$$visits_today_count$$</label>
		</div>

		<p>
			Últimas 10 visitas de hoy:
		</p>

		<table cellspacing="0" cellpadding="0" class="intable">
			<thead>
				<tr>
					<th>Dirección IP</th>
					<th>Navegador Web</th>
					<th>Proviene de</th>
					<th>Tipo de navegador</th>
				</tr>
			</thead>

			<tbody>
				<? $i = 0; while($row = fetch_assoc($visits_today)) {  if($i >= 10) { continue; } ++$i; ?>
				<tr>
					<th><?=$row['ip']?></th>
					<th><?=$row['browser']?></th>
					<th>
						<?=substr($row['referer'], 0, 40)?>

						<? if(!empty($row['referer'])) { ?>
						<a href="<?=$row['referer']?>" target="_blank" title="Abrir sitio en una nueva pestaña"><img src="%RESOURCES_SYS%/images/arrow_ne.png" /></a>
						<? } ?>
					</th>
					<th><?=$row['type']?></th>
				</tr>
				<? } ?>
			</tbody>
		</table>
	</section>

	<section>
		<div class="count_big">
			<label class="left">Visitas el día de ayer:</label>
			<label class="right">$$visits_yest_count$$</label>
		</div>
	</section>

	<section>
		<div class="count_big">
			<label class="left">Visitas durante esta semana:</label>
			<label class="right">$$visits_week_count$$</label>
		</div>
	</section>

	<section>
		<div class="count_big">
			<label class="left">Visitas durante este mes:</label>
			<label class="right">$$visits_month_count$$</label>
		</div>
	</section>
</div>
</div>