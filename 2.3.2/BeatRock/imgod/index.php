<?php####################################################### 					 BeatRock				   	   ######################################################### Framework avanzado de procesamiento para PHP.   ######################################################### InfoSmart � 2011 Todos los derechos reservados. #### http://www.infosmart.mx/						   ######################################################### http://beatrock.infosmart.mx/				   #######################################################$page['admin'] = true;require('../Init.php');if(empty($G['page']))	$G['page'] = "index";if($G['page'] == "snapshot.server"){	header("Content-type: image/png");	echo Gd::SnapshotDesktop();	exit;}if($G['page'] == "index"){	if($P['notes'] == "save")	{		Site::updateConf("site_notes", $P['value']);		exit;	}	function GetCount($table, $where = "")	{		$q = "SELECT null FROM $table";		if(!empty($where))			$q .= " $where";		return query_rows($q);	}	Tpl::Set(Array(		'notes' => $site['site_notes']	));	$visits = query("SELECT * FROM {DA}site_visits LIMIT 8");}if($G['page'] == "maintenance"){	if($G['action'] == "optimize")	{		MySQL::Optimize($P['tables']);		Tpl::JavaAction('K.ShowBox("correct", 6000); ');	}	if($G['action'] == "query")	{		BitRock::$ignore = true;		$sql = MySQL::query_data($P['query']);		Tpl::JavaAction('K.ShowBox("correct", 6000); ');	}	if(!empty($G['do']))	{		Site::doTimer($G['do']);		Tpl::JavaAction('K.ShowBox("correct", 6000); ');	}}$last_examine = Core::theSession("examine_time");if($last_examine < Core::Time(10, 3, true) OR empty($last_examine) OR $G['do'] == "update"){	Core::theSession("examine_resource", MySQL::Examine());	Core::theSession("examine_time", time());}$examine = Core::theSession("examine_resource");if(!empty($G['admin'])){	$cc = 0;	function FixName($name, $id = "")	{		$name = str_replace("_", " ", $name);		if($cc < 100)		{			if($name !== "id" AND $id !== "id")				$name = Core::Translate($name);		}		return $name;	}	function TableExist($tb)	{		global $examine;		$more = Array();		foreach($examine['tables'] as $table)		{			$t = $table['name'];			if($tb == $t)				return Array($table);			$n = explode("_", $t);			if($tb == $n[0])				$more[] = $table;		}		if(!empty($more))			return $more;		return false;	}	$table = TableExist($G['admin']);		if($table == false)	{		header("Location: " . ADMIN);		exit;	}	Tpl::Set("sec", $G['admin']);	if(count($table) > 1)		$G['page'] = "admin_select";	else	{		$table = $table[0];		$fields = $table['fields'];		Tpl::Set($table);		$sql = query("SELECT * FROM {DA}$table[name] LIMIT 150");		$cc = mysql_num_rows($sql);		$G['page'] = "admin_panel";	}		/*	echo "<pre>";	print_r($table);	echo "</pre>";	*/	}$page['id'] = $G['page'];?>