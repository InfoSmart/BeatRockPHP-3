<?php
## --------------------------------------------------
## PLANTILLA DE CORREO PARA REPORTE DE ERRORES.
## --------------------------------------------------

$details = BitRock::$details;
?>
<html>
	<head>
		 
	</head>
	<body style="color: #444444 !important; font-family: Droid Sans, Arial; font-size: 12px; margin: 0; padding: 0;">
		<h2 style="background: #F5F5F5; border: 1px solid #D8D8D8; border-radius: 1px; color: black !important; font-size: 23px; font-weight: normal; line-height: 35px; margin: 15px 0; padding: 20px 15px;">Ha ocurrido un problema en <?=SITE_NAME?></h2>
		
		<p>
			Se ha enviado este correo para notificarle que ha ocurrido un error en <a href="<?=PATH?>"><?=SITE_NAME?></a>.<br />
			Le recomendamos que solucione este problema lo más pronto posible para evitar posibles conflictos o amenazas de seguridad.
		</p>
		
		<p>
			A continuación se presenta más información:
		</p>
		
		<div class="info">
			<p>
				<span style="display: block; font-weight: bold;">Código de reporte:</span>
				<?=$details['report_code']?> <label style="color: gray; font-size: 11px;">(Para buscarlo en la base de datos)</label>
				<br />
			</p>

			<p>
				<span style="display: block; font-weight: bold;">Código del error:</span>
				<?=$details['info']['code']?><br />
			</p>
			
			<p>
				<span style="display: block; font-weight: bold;">Titulo del error:</span>
				<?=$details['info']['title']?><br />
			</p>
				
			<p>
				<span style="display: block; font-weight: bold;">Detalles del error:</span>
				<?=$details['info']['details']?><br />
			</p>
			
			<p>
				<span style="display: block; font-weight: bold;">Dirección del error:</span>
				<?=PATH_NOW?><br />
			</p>
			
				<?php
				if(is_array($details['res']))
				{
					foreach($details['res'] as $param => $value)
					{
						if(empty($value))
							continue;
											
						if($param == 'response')
							$param = 'Respuesta';
						if($param == 'file')
							$param = 'Archivo / Directorio';
						if($param == 'function')
							$param = 'Función';
						if($param == 'line')
							$param = 'Línea';
						if($param == 'out_file')
							$param = 'Archivo de salida';
						if($param == 'query')
							$param = 'Última consulta MySQL';
						if($param == 'last')
							$param = 'Último error PHP';
				?>
				<p>
					<span style="display: block; font-weight: bold;"><?=$param?>:</span> <?=$value?><br />
				</p>
				<?php } } ?>
		</div>

		<br /><br />
		
		<cite>Este correo electrónico ha sido enviado automaticamente por el sistema de BeatRock en tu sitio web. Los mensajes enviados a la misma no serán leidos ni procesados.</cite>

		<br /><br />
		
		<p>
			<b><?=SITE_NAME?></b><br />
			<?=PATH?><br />
			Todos los derechos reservados.
		</p>
	</body>
</html>