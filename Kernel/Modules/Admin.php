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
	public static function Examine()
	{
		$last = Core::TheCache('examine_time');
		$cache = Core::TheCache('examine_res');

		if($last < Core::Time(10, 3, true) OR empty($last) OR empty($cache))
		{
			Core::TheCache('examine_res', MySQL::Examine());
			Core::TheCache('examine_time', time());
		}

		return Core::TheCache('examine_res');
	}

	public static function GetMenu()
	{
		global $examine;
		$result = Array();

		foreach($examine['tables'] as $table)
		{
			$name = $table['name'];
			$sep = explode('_', $name);

			if($name == 'users' OR $name == 'wordsfilter')
				continue;
			if($sep[0] == 'site' OR $sep[0] == 'users')
				continue;

			if(!Contains('_', $name, true))
			{
				$filter = str_replace('_', ' ', $sep[0]);
				$filter = str_replace('-', ' ', $filter);

				$result[] = Array($sep[0], Core::Translate($filter));
			}
			else
				$result[] = Array($name, $table['translated']);
		}

		return $result;
	}

	public static function GetCount($table, $where = '')
	{
		$q = 'SELECT null FROM ' . $table;

		if(!empty($where))
			$q .= ' ' . $where;

		return Rows($q);
	}

	public static function GetVisits($table, $limit = 10)
	{
		return query("SELECT * FROM {DA}$table ORDER BY date DESC LIMIT $limit");
	}

	public static function FixName($name, $id = '')
	{
		global $cc;
		$name = str_replace('_', ' ', $name);

		if($name !== 'id')
			$name = Core::Translate($name);

		return $name;
	}

	public static function TableExist($tb)
	{
		global $examine;
		$more = Array();

		foreach($examine['tables'] as $table)
		{
			$t = $table['name'];

			if($tb == $t)
				return Array($table);

			$n = explode('_', $t);

			if($tb == $n[0])
				$more[] = $table;
		}

		if(!empty($more))
			return $more;

		return false;
	}
}
?>