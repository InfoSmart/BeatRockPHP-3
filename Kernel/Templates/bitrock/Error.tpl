<?
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
if(!defined('BEATROCK'))
	exit;

$details = BitRock::$details;

## --------------------------------------------------
## PLANTILLA DE ERROR.
## --------------------------------------------------
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<title>%have.problem%</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	
	<meta charset="iso-8859-15" />	
	<meta name="robots" content="noodp, nofollow" />

	<link href="//resources.infosmart.mx/systemv2/css/style.css" rel="stylesheet" />
	<script src="http://code.jquery.com/jquery-1.7.2.min.js"></script>
	
	<style>
	body
	{
		color: #848484;
	}

	.cwrapper
	{
		margin: 3% auto;
		width: 650px;
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
		width: 450px;
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

	.fast-error p
	{
		line-height: 21px;
	}

	.details
	{
		display: none;
		font-family: "Segoe UI", Ubuntu, Arial;
	}

	.details section
	{
		margin-bottom: 35px;
	}

	.details .c1
	{
		float: left;
		width: 290px;
	}

	.details .c2
	{
		float: right;
		width: 290px;
	}

	.details h5
	{
		font-family: Arial;
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
		font-size: 11px;
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
				<h1>%houston%</h1>
			</div>
		</header>

		<div class="cwrapper">
			<section class="fast-error">
				<aside>
					<h3>%break.something%</h3>

					<p>
						%found.problem%
					</p>

					<p>
						%dont.worry%
					</p>

					<? if(!empty($details['report_code'])) { ?>
					<p>
						%report% <b><?=$details['report_code']?></b>
					</p>
					<? } ?>

					<p class="center">
						<a onclick="history.back()">%back%</a> 
						- <a onclick="document.location.reload()">%retry%</a> 
						<? if(defined(PATH)) { ?>- <a href="<?=PATH?>">%go.home%</a><? } ?>
					</p>

					<? if($config['errors']['details'] OR empty($details['report_code'])) { ?>
					<div class="center">
						<a onclick="ShowDetails()" class="ibtn ismall">%what.happened%</a>
					</div>
					<? } ?>
				</aside>

				<figure>
					<img src="//resources.infosmart.mx/systemv2/images/error/Error.png" />
				</figure>
			</section>

			<? if($config['errors']['details'] OR empty($details['report_code'])) { ?>
			<section class="details">
				<div class="c1">
					<section>						
						<h5>%what.happened2%</h5>
						
						<p>
							<b>%title%</b>
							%details%
						</p>

						<? if(!empty($details['report_code'])) { ?>
						<p>
							<b>%report.code%</b> <?=$details['report_code']?>
						</p>
						<? } ?>
					</section>

					<? if(!empty($details['info']['solution'])) { ?>
					<section>
						<h5>%how.fix%</h5>
						
						<p>
							%solution%
						</p>
					</section>
					<? } ?>
				</div>

				<div class="c2">
					<section>
						<h5>%more.information%</h5>
						
						<p>
							<b>%error.code%</b> %code%<br />

							<?
							if(is_array($details['res']))
							{
								foreach($details['res'] as $param => $value)
								{
									if(empty($value))
										continue;
							?>
							<b><?='%'.$param.'%'?>:</b> <?=$value?><br />
							<? } } ?>
							
							<b>%agent%:</b> <?=AGENT?><br />
							<b>%browser%:</b> <?=Core::GetBrowser()?><br />
							<b>%os%:</b> <?=Core::GetOS()?><br />
							
							<b>%version%:</b> <?=$Info['version.full']?>
						</p>
					</section>
				</div>
			</section>
			<? } ?>
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