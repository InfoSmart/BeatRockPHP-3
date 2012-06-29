<div class="wrapper">
<div class="content">
	<h1>"Demos": Demostraciones del poder de BeatRock.</h1>

	<p>
		A continuación te mostramos una serie de ejemplos del poder de BeatRock, da clic en una para verla en acción.
	</p>

	<?php 
	foreach($pages as $p)
	{
		if($p == 'index.php')
			continue;
	?>
	<p>
		Ir a <a href="%PATH%/demos/<?=$p?>">%PATH%/demos/<?=$p?></a>
	</p>
	<?php }	?>
</div>

<style>
h1
{
	font-family: "Segoe UI Light", Tahoma, Arial;
	font-weight: bold;
}

footer
{
	border-top: 1px solid #D8D8D8;
	color: gray;
	font-size: 12px;
	margin-top: 10px;
	padding: 10px 0;
}
</style>