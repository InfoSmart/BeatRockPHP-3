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
if(!defined('BEATROCK'))
	exit;

class Admin
{
	static function GetTodayVisits()
	{
		$today 		= date('d F Y');
		$time_start = strtotime($today . ' 00:00:01');
		$time_end 	= strtotime($today . ' 23:59:59');

		$q = query("SELECT * FROM {DA}site_visits WHERE date > $time_start AND date < $time_end ORDER BY date DESC");
		return $q;
	}

	static function GetYesterdayVisits()
	{
		$yesterday_time = mktime(0, 0, 0, date('m'), (date('d') - 1), date('Y'));
		$yesterday 		= date('d F Y', $yesterday_time);
		$time_start 	= strtotime($yesterday . ' 00:00:01');
		$time_end 		= strtotime($yesterday . ' 23:59:59');

		$q = query("SELECT * FROM {DA}site_visits WHERE date > $time_start AND date < $time_end ORDER BY date DESC");
		return $q;
	}

	static function GetWeekVisits()
	{
		$week_time_start 	= mktime(0, 0, 0, date('m'), (date('d') - (date('N') - 1)), date('Y'));
		$week_start 		= date('d F Y', $week_time_start);
		$week_time_end 		= mktime(0, 0, 0, date('m'), (date('d') + (6 - (date('N') - 1))), date('Y'));
		$week_end 			= date('d F Y', $week_time_end);

		$time_start 		= strtotime($week_start . ' 00:00:01');
		$time_end 			= strtotime($week_end . ' 23:59:59');

		$q = query("SELECT * FROM {DA}site_visits WHERE date > $time_start AND date < $time_end ORDER BY date DESC");
		return $q;
	}

	static function GetMonthVisits()
	{
		$month_time_start 	= mktime(0, 0, 0, date('m'), 0, date('Y'));
		$month_start 		= date('d F Y', $month_time_start);
		$month_time_end 		= mktime(0, 0, 0, date('m'), 31, date('Y'));
		$month_end 			= date('d F Y', $month_time_end);

		$time_start 		= strtotime($month_start . ' 00:00:01');
		$time_end 			= strtotime($month_end . ' 23:59:59');

		$q = query("SELECT * FROM {DA}site_visits WHERE date > $time_start AND date < $time_end ORDER BY date DESC");
		return $q;
	}
}
?>