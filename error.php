<?
require 'Init.php';

$error = array();

if($G['code'] == '500')
{
	$error['title'] = 'Error interno';
	$error['desc'] 	= '¡WTF! Algo raro esta sucediendo aquí dentro.';
	$error['more'] 	= 'Lo sentimos pero actualmente estamos experimentando problemas técnicos graves. No te preocupes ya estamos peleando con nuestros ingenieros para solucionar este problema lo más pronto posible.';
}

$tpl = Tpl::Process(TEMPLATES_BIT . 'Error.Code', true);
echo $tpl;
?>