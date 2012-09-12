<?
$page['admin'] 	= true;
require '../Init.php';

$visits_today 		= Admin::GetTodayVisits();
$visits_today_count	= num_rows($visits_today);

$visits_yest 		= Admin::GetYesterdayVisits();
$visits_yest_count	= num_rows($visits_yest);

$visits_week 		= Admin::GetWeekVisits();
$visits_week_count 	= num_rows($visits_week);

$visits_month 		= Admin::GetMonthVisits();
$visits_month_count = num_rows($visits_month);

$page['id']		= array('nav', 'index');
$page['name']	= 'Administración: Inicio'
?>