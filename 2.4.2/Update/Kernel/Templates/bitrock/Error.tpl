<?php
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart © 2011 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

// Acción ilegal.
if(!defined("BEATROCK"))
	exit;

$details = BitRock::$details;

/*
$report_code = BitRock::$details['']
$code = BitRock::$details['code'];
$info = BitRock::$details['info'];
$res = BitRock::$details['res'];
*/

## --------------------------------------------------
## PLANTILLA DE ERROR.
## --------------------------------------------------
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<title>Hemos encontrado un problema...</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	
	<meta charset="iso-8859-15" />	
	<meta name="robots" content="noodp, nofollow" />

	<link href="//resources.infosmart.mx/system/css/style.css" rel="stylesheet" />
	<script src="http://code.jquery.com/jquery-1.7.2.min.js"></script>
	
	<style>
	.cwrapper
	{
		margin: 4% auto;
		width: 600px;
	}	

	header 
	{		
		background: #ffffff;
		background: -moz-linear-gradient(top, #ffffff 0%, #f7f7f7 100%);
		background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ffffff), color-stop(100%,#f7f7f7));
		background: -webkit-linear-gradient(top, #ffffff 0%,#f7f7f7 100%);
		background: -o-linear-gradient(top, #ffffff 0%,#f7f7f7 100%);
		background: -ms-linear-gradient(top, #ffffff 0%,#f7f7f7 100%);
		background: linear-gradient(top, #ffffff 0%,#f7f7f7 100%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#f7f7f7',GradientType=0 );

		border-bottom: 2px solid #444444;
		padding-bottom: 10px;
	}
	
	header h1 
	{
		color: black;
		float: left;
		font-family: "Segoe UI Light", Open Sans, Ubuntu, Segoe UI, Arial;
		font-size: 40px;
		font-weight: normal;
		line-height: 45px;
	}

	.fast-error aside
	{
		float: left;
		width: 400px;
	}

	.fast-error h3
	{
		font-size: 20px;
		font-weight: normal;
	}

	.fast-error figure
	{
		float: right;
	}

	.details
	{
		display: none;
		font-family: "Segoe UI", Ubuntu, Arial;
	}

	.details .c1
	{
		float: left;
		width: 280px;
	}

	.details .c2
	{
		float: right;
		width: 290px;
	}

	.details h5
	{
		font-family: "Open Sans", Segoe UI Light, Ubuntu, Arial;
		font-size: 19px;
		font-weight: normal;
		margin-bottom: 25px;
	}

	.details b
	{
		display: block;
		font-family: "Droid Sans", Ubuntu, Segoe UI, Arial;
		margin-top: 15px;
	}

	.details b:first-child
	{
		margin-top: 0;
	}

	footer 
	{
		border-top: 2px solid #444444;
		color: gray;
		font-size: 12px;
		padding: 15px 0;
	}
	</style>

	<script>
	function ShowDetails()
	{
		$('.fast-error').hide();
		$('.details').fadeIn('slow');
	}
	</script>
</head>
<body>
	<div class="page">
		<header>
			<div class="wrapper">
				<h1>Houston, tenemos un problema...</h1>
			</div>
		</header>

		<div class="cwrapper">
			<section class="fast-error">
				<aside>
					<h3>Creo que has roto algo...</h3>

					<p>
						Al parecer se ha encontrado un problema que nuestros chimpancés superdesarrollados no han podido solucionar o descubrir.
					</p>

					<p>
						No te preocupes, les hemos reportado del problema y esperaremos a que todo vuelva a la normalidad en poco tiempo.
					</p>

					<?php if(!empty($details['report_code'])) { ?>
					<p>
						Si vez a alguno de esos chimpancés superdesarrollados indicales este código de reporte: <b><?=$details['report_code']?></b>
					</p>
					<?php } ?>

					<p class="center">
						<a onclick="history.back()">Volver al pasado</a> - <a onclick="document.location.reload()">Intentarlo nuevamente</a> - <a href="<?=PATH?>">Ir al inicio</a>
					</p>

					<?php if($config['errors']['details']) { ?>
					<p class="center">
						<a onclick="ShowDetails()" class="ibtn ismall">Pero, ¿que sucedio?</a>
					</p>
					<?php } ?>
				</aside>

				<figure>
					<img src="//resources.infosmart.mx/system/images/error/Error.png" />
				</figure>
			</section>

			<?php if($config['errors']['details']) { ?>
			<section class="details">
				<div class="c1">
					<section>						
						<h5>¿Qué ha pasado?</h5>
						
						<p>
							<b>%title%. (<?=$details['code']?>)</b>
							%details%
						</p>

						<?php if(!empty($details['report_code'])) { ?>
						<p>
							<b>Código de reporte:</b> <?=$details['report_code']?>
						</p>
						<?php } ?>
					</section>

					<?php if(!empty($details['info']['solution'])) { ?>
					<section>
						<h5>¿Como puedo solucionarlo?</h5>
						
						<p>
							%solution%
						</p>
					</section>
					<?php } ?>
				</div>

				<div class="c2">
					<section>
						<h5>Más información</h5>
						
						<p>
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
										$param = 'Archivo de entrada/salida';
									if($param == 'query')
										$param = 'Consulta SQL';
									if($param == 'last')
										$param = 'Último error PHP';
							?>
							<b><?=$param?>:</b> <?=$value?><br />
							<?php } } ?>
							
							<b>Agente del navegador:</b> <?=AGENT?><br />
							<b>Navegador web:</b> <?=Core::GetBrowser()?><br />
							<b>Sistema operativo:</b> <?=Core::GetOS()?><br />
							
							<b>Versión del Kernel:</b> <?=$Info['version.full']?>
						</p>
					</section>
				</div>
			</section>
			<?php } ?>
		</div>

		<footer>
			<div class="wrapper">
				<label class="left">
					<a href="http://www.infosmart.mx/" target="_blank">InfoSmart</a>. Todos los derechos reservados.
				</label>
				
				<label class="right">
					<a href="http://beatrock.infosmart.mx/" target="_blank">BeatRock v<?=$Info['version']?></a>
				</label>
			</div>
		</footer>
	</div>
</body>
</html>