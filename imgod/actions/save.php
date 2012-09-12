<?
require '../../Init.php';

if($G['type'] == 'site_config')
{
	foreach($_POST as $key => $value)
		Site::Update($key, _f($value, false));

	_SESSION('admin_correct', true);
	Core::Redirect(ADMIN . '/site_config');
}
?>