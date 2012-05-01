<?php
## --------------------------------------------------
## PLANTILLA DE CORREO PARA REPORTE DE ERRORES.
## --------------------------------------------------

$code = BitRock::$details['code'];
$info = BitRock::$details['info'];
$res = BitRock::$details['res'];
?>
<html>
	<head>
		 
	</head>
	<body style="color: #444444 !important; font-family: 'Ubuntu', Droid Sans, Segoe UI, Lucida Grande, Tahoma, Verdana, Arial; font-size: 13px; margin: 0; padding: 0; text-shadow: 0 1px 0 rgba(0,0,0, .1);">
		<h2 style="background: #F5F5F5; border: 1px solid #D8D8D8; border-radius: 3px; color: black !important; font-size: 20px; font-weight: normal; line-height: 35px; margin: 15px 0; padding: 10px;">Ha ocurrido un problema en <?=SITE_NAME?></h2>
		
		<p>
			Se ha enviado este correo para notificarle que ha ocurrido un error en el sistema de BeatRock en <a href=3D"<?=PATH?>"><?=SITE_NAME?></a>.<br />
			Le recomendamos que solucione este problema lo más pronto posible para evitar posibles conflictos o amenazas de seguridad.
		</p>
		
		<p>
			A continuación se presenta más información:
		</p>
		
		<div class="info">
			<span style="display: block; font-weight: bold;">Código del error:</span>
			<?=$code?><br />
			
			<span style="display: block; font-weight: bold;">Titulo del error:</span>
			<?=$info['title']?><br />
			
			<span style="display: block; font-weight: bold;">Detalles del error:</span>
			<?=$info['details']?><br />
			
			<span style="display: block; font-weight: bold;">Dirección del error:</span>
			<?=PATH_NOW?><br />
			
			<?php
			if(is_array($res))
			{
				foreach($res as $param => $value)
				{
					if(empty($value))
						continue;
										
					if($param == "response")
						$param = "Respuesta";
					if($param == "file")
						$param = "Archivo / Directorio";
					if($param == "function")
						$param = "Función";
					if($param == "line")
						$param = "Línea";
					if($param == "out_file")
						$param = "Archivo de salida";
					if($param == "query")
						$param = "Última consulta MySQL";
					if($param == "last")
						$param = "Último error";
			?>
			<span style="display: block; font-weight: bold;"><?=$param?>:</span> <?=$value?><br />
			<?php } } ?>

			<span style="display: block; font-weight: bold;">Consola:</span>
			<div style="background: #F5F5F5; padding: 15px;"><?=nl2br(BitRock::printLog(false))?></div><br />
		</div>
		
		<cite>Este correo electrónico ha sido enviado automaticamente por el sistema de BeatRock en tu sitio web. Los mensajes enviados a la misma no serán leidos ni procesados.</cite>
		
		<p>
			<b><?=SITE_NAME?> & InfoSmart</b><br />
			<?=PATH?><br />
			http://www.infosmart.mx/<br />
			Todos los derechos reservados.
		</p>
	</body>
</html>