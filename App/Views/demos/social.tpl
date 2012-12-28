<?=Social::PrepareFacebook();?>

<div class="wrapper">
<div class="content">
	<h1>Módulo Social: Conectarse con servicios sociales.</h1>

	<?php if(!isset($info)) { ?>
	<p>
		<b>Facebook, Twitter y Google</b> precisan de una ID y una Clave de tu aplicación registrada, abre el archivo <b><?=ROOT?>demos\social.php</b> para obtener más información.
	</p>

	<section>
		<h2>Facebook</h2>

		<a class="fb_button fb_button_medium" onclick="fbLogin()">
			<span class="fb_button_text">Entrar con Facebook</span>
		</a>
	</section>

	<section>
		<h2>Twitter</h2>

		<a href="%PATH%/demos/social.php?do=connect&service=twitter">
			<img src="%PATH%/Kernel/Modules/External/twitter/images/darker.png" />
		</a>
	</section>

	<section>
		<h2>Google</h2>

		<a href="%PATH%/demos/social.php?do=connect&service=google">
			Entrar con Google
		</a>

		<p>
			<b style="color: red">¡Atención!</b>: Esta API puede no funcionar correctamente.
		</p>
	</section>

	<section>
		<h2>Steam</h2>

		<a href="%PATH%/demos/social.php?do=connect&service=steam">
			<img src="http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_small.png" />
		</a>
	</section>
	<?php } else { ?>
	<h2>Esta es tu información:</h2>

	<?_r($info);?>

	<p>
		<a href="%PATH%/demos/social.php">Regresar</a>
	</p>
	<?php } ?>

	<p>
		Asegurate de ver el código fuente del ejemplo para saber como hacerlo :)
	</p>
</div>

<script>
function fbLogin()
{
	FB.login(function(e)
	{
		if(e.status !== 'unknown')
			document.location.replace('%PATH%/demos/social.php?do=connect&service=facebook');
	}, {scope: 'user_about_me, user_birthday, user_education_history, user_hometown, user_interests, user_location, user_status, user_website, user_work_history, email'});
}

function fbRegister()
{
	FB.login(function(e) 
	{
		if(e.status !== 'unknown')
			document.location.replace('%PATH%/demos/social.php?do=register&service=facebook');
	}, {scope: 'user_about_me, user_birthday, user_education_history, user_hometown, user_interests, user_location, user_status, user_website, user_work_history, email'});
}
</script>

<style>
h1
{
	font-weight: normal;
	margin-bottom: 30px;
}

input[type='text']
{
	border-radius: 5px;
	padding: 10px 5px;
	width: 500px;
}

.content section
{
	border-bottom: 1px solid #D8D8D8;
	padding-bottom: 20px;
}

footer
{
	border-top: 1px solid #D8D8D8;
	color: gray;
	font-size: 12px;
	margin-top: 10px;
	padding: 5px;
}
</style>