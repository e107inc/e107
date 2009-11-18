<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/pm/pm_func.php,v $
 * $Revision: 1.7 $
 * $Date: 2009-11-18 01:05:53 $
 * $Author: e107coders $
 */

if (!defined('e107_INIT')) { exit; }

function pm_getInfo($which = "inbox")
{
	static $pm_info;
	global $sql, $pref, $pm_prefs;
	if('clear' == $which)
	{
		unset($pm_info['inbox']);
		unset($pm_info['outbox']);
		return;
	}

	if('inbox' == $which)
	{
		$qry = "SELECT count(pm.pm_id) AS total, SUM(pm.pm_size)/1024 size, SUM(pm.pm_read = 0) as unread FROM #private_msg as pm WHERE pm.pm_to = ".USERID." AND pm.pm_read_del = 0";
	}
	else
	{
		$qry = "SELECT count(pm.pm_from) AS total, SUM(pm.pm_size)/1024 size, SUM(pm.pm_read = 0) as unread FROM #private_msg as pm WHERE pm.pm_from = ".USERID." AND pm.pm_sent_del = 0";
	}

	if(!isset($pm_info[$which]['total']))
	{
		$sql->db_Select_gen($qry);
		$pm_info[$which] = $sql->db_Fetch();
		if ($which == 'inbox' && ($pm_prefs['animate'] == 1 || $pm_prefs['popup'] == 1))
		{
			if($new = $sql->db_Count("private_msg", "(*)", "WHERE pm_sent > '".USERLV."' AND pm_read = 0 AND pm_to = '".USERID."' AND pm_read_del != 1"))
			{
				$pm_info['inbox']['new'] = $new;
			}
			else
			{
				$pm_info['inbox']['new'] = 0;
			}
		}
	}

	if(!isset($pm_info[$which]['limit']))
	{
		if(varset($pref['pm_limits'],0) > 0)
		{
			if($pref['pm_limits'] == 1)
			{
				$qry = "SELECT MAX(gen_user_id) AS inbox_limit, MAX(gen_ip) as outbox_limit FROM #generic WHERE gen_type='pm_limit' AND gen_datestamp IN (".USERCLASS_LIST.")";
			}
			else
			{
				$qry = "SELECT MAX(gen_intdata) AS inbox_limit, MAX(gen_chardata) as outbox_limit FROM #generic WHERE gen_type='pm_limit' AND gen_datestamp IN (".USERCLASS_LIST.")";
			}
			if($sql->db_Select_gen($qry))
			{
				$row = $sql->db_Fetch();
				$pm_info['inbox']['limit'] =  $row['inbox_limit'];
				$pm_info['outbox']['limit'] =  $row['outbox_limit'];
			}
			$pm_info['inbox']['limit_val'] = ($pref['pm_limits'] == 1 ? varset($pm_info['inbox']['total'],'') : varset($pm_info['inbox']['size'],''));
			if(!$pm_info['inbox']['limit'] || !$pm_info['inbox']['limit_val'])
			{
				$pm_info['inbox']['filled'] = 0;
			}
			else
			{
				$pm_info['inbox']['filled'] = number_format($pm_info['inbox']['limit_val']/$pm_info['inbox']['limit'] * 100, 2);
			}
			$pm_info['outbox']['limit_val'] = ($pref['pm_limits'] == 1 ? varset($pm_info['outbox']['total'],'') : varset($pm_info['outbox']['size'],''));
			if(!$pm_info['outbox']['limit'] || !$pm_info['outbox']['limit_val'])
			{
				$pm_info['outbox']['filled'] = 0;
			}
			else
			{
				$pm_info['outbox']['filled'] = number_format($pm_info['outbox']['limit_val']/$pm_info['outbox']['limit'] * 100, 2);
			}
		}
		else
		{
			$pm_info['inbox']['limit'] = "";
			$pm_info['outbox']['limit'] = "";
			$pm_info['inbox']['filled'] = "";
			$pm_info['outbox']['filled'] = "";
		}
	}
	return $pm_info;
}
?>