<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Main
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/online_class.php,v $
 * $Revision: 1.5 $
 * $Date: 2009-09-21 21:43:44 $
 * $Author: e107steved $
*/

class e_online 
{
	function online($online_tracking = false, $flood_control = false) 
	{
		global $sql, $pref, $e107, $e_event, $tp, $online_timeout, $online_warncount, $online_bancount;
		global $members_online, $total_online, $member_list, $listuserson;


		if($online_tracking == true || $flood_control == true)
		{
			if(!isset($online_timeout)) $online_timeout = 300;
			if(!isset($online_bancount)) 
			{
				list($ban_access_guest,$ban_access_member) = explode(',',varset($pref['ban_max_online_access'],'100,200'));
				$online_bancount = max($ban_access_guest,50);					// Safety net for incorrect values
				if (USER)
				{
					$online_bancount = max($online_bancount,$ban_access_member);
				}
			}
			$online_warncount = $online_bancount * 0.9;		// Set warning threshold at 90% of ban threshold
			$page = (strpos(e_SELF, "forum_") !== FALSE) ? e_SELF.".".e_QUERY : e_SELF;
			$page = (strpos(e_SELF, "comment") !== FALSE) ? e_SELF.".".e_QUERY : $page;
			$page = (strpos(e_SELF, "content") !== FALSE) ? e_SELF.".".e_QUERY : $page;
			$page = $tp -> toDB($page, true);
			$ip = $e107->getip();
			$udata = (USER === true ? USERID.".".USERNAME : "0");
			if (USER)
			{
				// Find record that matches IP or visitor, or matches user info
				if ($sql->db_Select("online", "*", "(`online_ip` = '{$ip}' AND `online_user_id` = '0') OR `online_user_id` = '{$udata}'")) 
				{
					$row = $sql->db_Fetch();

					if ($row['online_user_id'] == $udata) {
						//Matching user record
						if ($row['online_timestamp'] < (time() - $online_timeout)) {
							//It has been at least 'timeout' seconds since this user has connected
							//Update user record with timestamp, current IP, current page and set pagecount to 1
							$query = "online_timestamp='".time()."', online_ip='{$ip}', online_location='{$page}', online_pagecount=1 WHERE online_user_id='{$row['online_user_id']}' LIMIT 1";
						} else {
							if (!ADMIN) {
								$row['online_pagecount'] ++;
							}
							// Update user record with current IP, current page and increment pagecount
							$query = "online_ip='{$ip}', `online_location` = '{$page}', `online_pagecount` = '".intval($row['online_pagecount'])."' WHERE `online_user_id` = '{$row['online_user_id']}' LIMIT 1";
						}
					} else {
						//Found matching visitor record (ip only) for this user
						if ($row['online_timestamp'] < (time() - $online_timeout)) {
							// It has been at least 'timeout' seconds since this user has connected
							// Update record with timestamp, current IP, current page and set pagecount to 1
							$query = "`online_timestamp` = '".time()."', `online_user_id` = '{$udata}', `online_location` = '{$page}', `online_pagecount` = 1 WHERE `online_ip` = '{$ip}' AND `online_user_id` = '0' LIMIT 1";
						} else {
							if (!ADMIN) {
								$row['online_pagecount'] ++;
							}
							//Update record with current IP, current page and increment pagecount
							$query = "`online_user_id` = '{$udata}', `online_location` = '{$page}', `online_pagecount` = ".intval($row['online_pagecount'])." WHERE `online_ip` = '{$ip}' AND `online_user_id` = '0' LIMIT 1";
						}
					}
					$sql->db_Update("online", $query);
				} 
				else 
				{
					$sql->db_Insert("online", " '".time()."', '0', '{$udata}', '{$ip}', '{$page}', 1, 0");
				}
			}
			else
			{
				//Current page request is from a visitor
				if ($sql->db_Select("online", "*", "`online_ip` = '{$ip}' AND `online_user_id` = '0'")) 
				{
					$row = $sql->db_Fetch();

					if ($row['online_timestamp'] < (time() - $online_timeout)) //It has been at least 'timeout' seconds since this ip has connected
					{
						//Update record with timestamp, current page, and set pagecount to 1
						$query = "`online_timestamp` = '".time()."', `online_location` = '{$page}', `online_pagecount` = 1 WHERE `online_ip` = '{$ip}' AND `online_user_id` = '0' LIMIT 1";
					} 
					else 
					{
						//Update record with current page and increment pagecount
						$row['online_pagecount'] ++;
						//   echo "here {$online_pagecount}";
						$query="`online_location` = '{$page}', `online_pagecount` = {$row['online_pagecount']} WHERE `online_ip` = '{$ip}' AND `online_user_id` = '0' LIMIT 1";
					}
					$sql->db_Update("online", $query);
				} 
				else 
				{
					$sql->db_Insert("online", " '".time()."', '0', '0', '{$ip}', '{$page}', 1, 0");
				}
			}

			if (ADMIN || ($pref['autoban'] != 1 && $pref['autoban'] != 2) || (!isset($row['online_pagecount']))) // Auto-Ban is switched off. (0 or 3)
			{
				$row['online_pagecount'] = 1;
			}

			if ($row['online_pagecount'] > $online_bancount && ($e107->ipDecode($row['online_ip'],TRUE) != "127.0.0.1")) 
			{
				include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_banlist.php');
				if ($e107->add_ban(2,str_replace('--HITS--',$row['online_pagecount'],BANLAN_78),$ip,0))
				{
					$e_event->trigger("flood", $ip);
					exit;
				}
			}
			if ($row['online_pagecount'] >= $online_warncount && $row['online_ip'] != "127.0.0.1") 
			{
				echo "<div style='text-align:center; font: 11px verdana, tahoma, arial, helvetica, sans-serif;'><b>".LAN_WARNING."</b><br /><br />".CORE_LAN6."<br /></div>";
				exit;
			}

			$sql->db_Delete("online", "`online_timestamp` < ".(time() - $online_timeout));

			$total_online = $sql->db_Count("online");
			if ($members_online = $sql->db_Select("online", "*", "online_user_id != '0' ")) {
				$member_list = '';
				$listuserson = array();
				while ($row = $sql->db_Fetch()) {
					$vals = explode(".", $row['online_user_id'], 2);
					$member_list .= "<a href='".e_BASE."user.php?id.{$vals[0]}'>{$vals[1]}</a> ";
					$listuserson[$row['online_user_id']] = $row['online_location'];
				}
			}
			define("TOTAL_ONLINE", $total_online);
			define("MEMBERS_ONLINE", $members_online);
			define("GUESTS_ONLINE", $total_online - $members_online);
			define("ON_PAGE", $sql->db_Count("online", "(*)", "WHERE `online_location` = '{$page}' "));
			define("MEMBER_LIST", $member_list);
		}
		else
		{
			define("e_TRACKING_DISABLED", true);
			define("TOTAL_ONLINE", "");
			define("MEMBERS_ONLINE", "");
			define("GUESTS_ONLINE", "");
			define("ON_PAGE", "");
			define("MEMBER_LIST", ""); //
		}
	}
}
