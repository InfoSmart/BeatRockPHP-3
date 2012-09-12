<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="iso-8859-15" />
	
	<title>¡Oops! - $$error_title$$</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

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
	background: -moz-linear-gradient(top, #ffffff 0%, #ece9e9 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ffffff), color-stop(100%,#ece9e9));
	background: -webkit-linear-gradient(top, #ffffff 0%,#ece9e9 100%);
	background: -o-linear-gradient(top, #ffffff 0%,#ece9e9 100%);
	background: -ms-linear-gradient(top, #ffffff 0%,#ece9e9 100%);
	background: linear-gradient(to bottom, #ffffff 0%,#ece9e9 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#ece9e9',GradientType=0 );

		border-bottom: 2px solid #444444;
		padding: 10px;
	}
	
	header h1 
	{
		color: black;
		float: left;
		font-family: "Open Sans", Ubuntu, Segoe UI, Arial;
		font-size: 50px;
		font-weight: 300;
		line-height: 45px;
	}

	.fast-error aside
	{
		font-family: Arial;
		float: left;
		width: 450px;
	}

	.fast-error h3
	{
		font-family: "Open Sans", Ubuntu, Segoe UI, Arial;
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
				<h1>$$error_title$$</h1>
			</div>
		</header>

		<div class="cwrapper">
			<section class="fast-error">
				<aside>
					<h3>$$error_desc$$</h3>

					<p>
						$$error_more$$
					</p>

					<? if(!empty($details['report_code'])) { ?>
					<p>
						%report% <b><?=$details['report_code']?></b>
					</p>
					<? } ?>

					<p class="center">
						<a onclick="history.back()">Volver al pasado</a> 
						- <a onclick="document.location.reload()">Intentarlo nuevamente</a> 
						<? if(defined(PATH)) { ?>- <a href="<?=PATH?>">%go.home%</a><? } ?>
					</p>
				</aside>

				<figure>
					<img src="//resources.infosmart.mx/systemv2/images/error/Error.png" />
				</figure>
			</section>
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