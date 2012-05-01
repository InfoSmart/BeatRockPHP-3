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
	
$code = BitRock::$details['code'];
$info = BitRock::$details['info'];
$res = BitRock::$details['res'];

## --------------------------------------------------
## PLANTILLA DE ERROR.
## --------------------------------------------------
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<title>¡Uy! Algo aquí anda mal...</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-15" />
	<meta http-equiv="content-language" content="es" />
	
	<meta name="robots" content="noodp, nofollow" />

	<link href="//resources.infosmart.mx/system/css/style.css" rel="stylesheet" />
	
	<style>
	body {
		font-family: "Segoe UI", Arial, sans-serif;
	}
	
	header {
		padding: 5px 25px !important;		
		border-bottom: 1px solid #D8D8D8;

		margin-bottom: 10px;
		border-radius: 0 0 5px 5px;
	}
	
	header h1 {
		font-size: 27px;
		line-height: 35px;
		
		font-weight: normal;
	}
	
	header h1 .msg {
		float: left;
		
		color: #8A0808;
		text-shadow: 0 1px 1px white;
	}
	
	header h1 .sys {
		float: right;		
		color: #045FB4;
	}
	
	.box .c1 {
		float: left;
		width: 550px;
	}
	
	.box .c2 {
		float: right;
		width: 300px;
	}
	
	.box section {
		margin: 30px 0;
	}
	
	.box details {
		padding: 5px 10px;
		margin: 10px 0;
		
		word-break: break-word;
		overflow: hidden;
		
		border: 1px solid white;
		border-bottom: 2px solid #E6E6E6;
	}
	
	.box section h2,
	.box details summary {
		color: #666;
		font-size: 17px;
		
		font-weight: normal;
		margin: 15px 0;
	}
	
	.box details[open] {
		border: 1px solid #E6E6E6;
		border-bottom-width: 2px;
		
		border-radius: 0 0 5px 5px;
	}
	
	.box section h3 {
		font-size: 20px;
		color: #298A08;
		
		font-weight: normal;
		text-shadow: 0 1px 1px white;
	}
	
	.box section figure {
		float: right;
	}
	
	.box .console {
		color: black;
		font-family: "Consolas", Segoe UI, Arial, sans-serif;
	}
	
	.box .more b {
		font-size: 14px;
		display: block;
	}
	
	.box .about b {
		font-size: 16px;
		display: block;
		
		text-shadow: 0 1px 1px rgba(255, 255, 255, .4);
		margin-bottom: 5px;
	}
	
	footer {
		font-size: 12px;
		border-top: 1px solid #F2F2F2;
		
		padding: 10px 0;
		color: gray;
	}
	</style>
</head>
<body>
	<div class="page">
		<div class="wrapper">
			<header class="bx">
				<h1 class="clearfix">
					<label class="msg">Houston, tenemos un problema...</label>
					<label class="sys"><?=$Info['version.name']?></label>
				</h1>
			</header>
			
			<div class="box">
				<div class="c1">
					<p>
						¡OMG! Lo sentimos pero ha ocurrido un problema interno que no hemos podido solucionar automaticamente y no es posible continuar.
					</p>
					
					</p>
						Puedes volver a intentarlo más tarde o tratar de avisar del problema a uno de nuestros ingenieros.
					</p>
					
					<details open>						
						<summary>¿Qué ha pasado?</summary>
						
						<p class="about">
							<b>%title%. (<?=$code?>)</b>%details%
						</p>
					</details>
					
					<?php if(!empty($info['solution'])) { ?>
					<details>
						<summary>¿Como puedo solucionarlo?</summary>
						
						<p>
							%solution%
						</p>
					</details>
					<?php } ?>
					
					<details open>
						<summary>Más información</summary>
						
						<p class="more">
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
										$param = "Consulta SQL";
									if($param == "last")
										$param = "Último error";
							?>
							<b><?=$param?>:</b> <?=$value?><br />
							<?php } } ?>
							
							<b>Navegador web:</b> <?=Core::GetBrowser()?><br />
							<b>Sistema operativo:</b> <?=Core::GetOS()?><br />
							
							<b>Versión del Kernel:</b> <?=$Info['version.full']?>
						</p>
						
						<p>
							Si hay más información disponible proporcionesela a un ingeniero o sitio de reporte de errores para solucionar el problema más fácilmente.
						</p>
					</details>
					
					<details>
						<summary>Consola</summary>
						<p>La consola le proporciona más información acerca de lo que pasa en BeatRock.</p>
						
						<div class="console">
							<?php BitRock::printLog()?>
						</div>
					</details>
					
					<section>
						<h2>Anuncios</h2>
						
						<div class="center">
							<script>
							google_ad_client = "ca-pub-9402920437660061";
							google_ad_slot = "0645116619";
							google_ad_width = 468;
							google_ad_height = 60;
							</script>
							<script src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
						</div>
					</section>
				</div>
				
				<div class="c2">
					<figure class="center">
						<img src="//localhost/resources/system/images/error/Sad.png" title="¡Oops!" alt="¡Oops!" />
					</figure>
					
					<?php if($mail_result) { ?>
					<section>
						<h3>¡Ayuda en camino!</h3>
						<p>
							El problema ha sido reportado por correo electrónico y trataremos de resolverlo lo más pronto posible.
						</p>
						
						<p>Para más información y reporte de otros problemas por favor envie un correo electrónico a: <a href="mailto:<?=$config['errors']['email.to']?>"><?=$config['errors']['email.to']?></a></p>
					</section>
					<?php } ?>
					
					<section>
						<p>Por favor vuelva a la <a onclick="history.back()">página anterior</a> o a la <a href="<?=PATH?>">página de inicio</a>.</p>
					</section>
				</div>
			</div>
			
			<footer>
				<label class="left">
					<a href="http://www.infosmart.mx/" target="_blank">InfoSmart</a>. Todos los derechos reservados.
				</label>
				
				<label class="right">
					<a href="http://beatrock.infosmart.mx/" target="_blank">BeatRock</a>
				</label>
			</footer>
		</div>
	</div>
</body>
</html>