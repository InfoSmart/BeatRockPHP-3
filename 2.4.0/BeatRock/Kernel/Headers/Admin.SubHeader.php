<?php
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart © 2012 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

// Acción ilegal.
if(!defined("BEATROCK"))
	exit;

function DetectMenuTables()
{
	global $examine;
	$result = Array();

	foreach($examine['tables'] as $table)
	{
		$t = $table['name'];
		$n = explode("_", $t);

		if($t == "users" OR $t == "wordsfilter")
			continue;
		else if($n[0] == "site")
			continue;
		else
		{
			if(strpos($t, "_") !== false)
			{
				$f = str_replace("_", " ", $n[0]);
				$f = str_replace("-", " ", $f);
				
				$result[] = Array($n[0], Core::Translate($f));
			}
			else
			{
				$f = $t;
				$result[] = Array($t, $table['translated']);
			}
		}
	}

	return $result;
}

$tabs = DetectMenuTables();
?>
<header>
	<div class="wrapper">
		<figure>
			<h1>%SITE_NAME%</h1>
			<span><?php echo NormalDate(false, 3); ?>.</span>
		</figure>
	</div>
</header>

<div class="wrapper">
	<div class="content">
		<div class="c1">
			<nav>
				<a href="%ADMIN%">Muro</a>
				<a href="%ADMIN%/?admin=site">Sitio</a>
				<a href="%ADMIN%/?admin=users">Usuarios</a>
				
				<?php foreach($tabs as $a) { ?>
				<a href="%ADMIN%/?admin=<?php echo $a[0]; ?>"><?php echo $a[1]; ?></a>
				<?php } ?>

				<a href="%ADMIN%/?page=maintenance">Mantenimiento</a>
				<?php if(PHP_OS == "WINNT") { ?>
				<a href="%ADMIN%/?page=server">Servidor</a>
				<?php } ?>
			</nav>
		</div>



<!--
<div class="wrapper">
	<header>	
		<nav>
			<a href="%ADMIN%/">Estadisticas</a>
			<a href="%ADMIN%/general">Configuración general</a>
			<a href="%ADMIN%/news">Noticias</a>
			<a href="%ADMIN%/users">Usuarios</a>
			<a href="%ADMIN%/reports">Reportes</a>
			<a href="%ADMIN%/mysql">Mantenimiento & Servidor MySQL</a>
		</nav>
	</header>
-->