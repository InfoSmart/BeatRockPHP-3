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
		<style type="text/css">		
		body {	
			color: #595959;
			background: white;	
			
			font-family: "Droid Sans", Segoe UI, Lucida Grande, Arial, sans-serif;
			font-size: 13px;
			
			text-align: left;				
			direction: ltr;
		}
		
		h2 {
			font-size: 23px;
			font-weight: normal;
			
			color: #084B8A;
			line-height: 35px;
			
			text-shadow: 0 1px 1px white;
			opacity: .9;
			
			margin: 15px 0;
		}
		
		cite {
			font-style: normal;
			font-size: 11px;
			
			color: #6E6E6E;
			margin: 8px 0;
		}
		
		.info span {
			font-weight: bold;
			display: block;
		}
		
		.info .console {
			padding: 15px;
			background: #F5F5F5;
		}
		</style>
	</head>
	<body>
		<h2>Ha ocurrido un problema en <?=SITE_NAME?></h2>
		
		<p>
			Se ha enviado este correo para notificarle que ha ocurrido un error en el sistema de BeatRock en <a href="<?=PATH?>"><?=SITE_NAME?></a>.<br />
			Le recomendamos que solucione este problema lo más pronto posible para evitar posibles conflictos o amenazas de seguridad.
		</p>
		
		<p>
			A continuación se presenta más información:
		</p>
		
		<div class="info">
			<span>Código del error:</span>
			<?=$code?><br />
			
			<span>Titulo del error:</span>
			<?=$info['title']?><br />
			
			<span>Detalles del error:</span>
			<?=$info['details']?><br />
			
			<span>Dirección del error:</span>
			<?=PROTOCOL . URL?><br />
			
			<span>Consola:</span>
			<div class="console"><?=nl2br(BitRock::printLog(false))?></div><br />
			
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
			?>
			<span><?=$param?>:</span> <?=$value?><br />
			<?php } } ?>
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