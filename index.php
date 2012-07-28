<?php
require 'Init.php';

$hello = 'Hola querido mundo';

$page['id'] 			= 'index';
$page['name'] 			= 'Inicio';

$page['lang'] 			= (!empty($G['lang'])) ? $G['lang'] : LANG;
$page['lang.sections'] 	= array('page.welcome');
?>