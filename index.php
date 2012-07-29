<?php
require 'Init.php';

$hello = 'Hola querido mundo';

// Plantilla: /Kernel/Templates/index.tpl
// Subcabecera: /Kernel/Headers/SubHeader.php

$page['id'] 			= 'index';
$page['name'] 			= 'Inicio';

$page['lang'] 			= (!empty($G['lang'])) ? $G['lang'] : LANG;
$page['lang.sections'] 	= array('page.welcome');
?>