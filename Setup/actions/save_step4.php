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

$page['gzip'] = false;
require('../../Init.php');

foreach($P as $param => $value)
{
	$sep 	= explode('_', $param);
	$type 	= $sep[0];

	if($param == 'register')
		continue;

	if($type == 'stopwatch')
	{
		if(empty($value) OR !is_numeric($value))
			continue;

		$param = str_ireplace('stopwatch_', '', $param);

		Insert('site_timers', array(
			'action' 	=> $param,
			'time' 		=> $value,
			'nexttime' 	=> (time() + $value)
		));
	}
	else
	{
		if(is_array($value))
			$value = _f(json_encode($value), false);
		else
			$value = _c(Core::FixText($value));
		
		Site::Update($param, $value);
	}
}

if($P['register'] == 'true')
{
	$exitUrl 	= urlencode(PATH . '/Setup/finish.php?license_url=[license_url]&license_name=[license_name]');
	$styleSheet = urlencode('//resources.infosmart.mx/system/setup/style.commons.css');
	$logoUrl 	= urlencode('//resources.infosmart.mx/infosmart/images/logo.png');

	Core::Redirect("http://creativecommons.org/license/?partner=InfoSmart&exit_url=$exitUrl&stylesheet=$styleSheet&partner_icon_url=$logoUrl");
}

Core::Redirect(PATH . '/Setup/finish.php');
?>