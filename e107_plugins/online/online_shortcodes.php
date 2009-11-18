<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Main
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/online/online_shortcodes.php,v $
 * $Revision: 1.4 $
 * $Date: 2009-11-18 01:05:53 $
 * $Author: e107coders $
*/
if (!defined('e107_INIT')) { exit; }


register_shortcode('online_shortcodes', true);
initShortcodeClass('online_shortcodes');

class online_shortcodes
{
	var $e107;
	
	function online_shortcodes()
	{
		$this->e107 = e107::getInstance();
	}

	// Last Seen Menu
	function sc_lastseen_userlink()
	{
		global $row;
		return "<a href='".e_BASE."user.php?id.".$row['user_id']."'>".$row['user_name']."</a>";
	}

	function sc_lastseen_date()
	{
		global $gen, $row;
		$seen_ago = $gen->computeLapse($row['user_currentvisit'], false, false, true, 'short');
		return ($seen_ago ? $seen_ago : "1 ".LANDT_09)." ".LANDT_AGO;
	}


	function sc_online_tracking_disabled()
	{
		return $this->e107->tp->toHTML(LAN_ONLINE_TRACKING_MESSAGE,TRUE);
	}

	
	// Online Menu
	function sc_online_guests()
	{
		return GUESTS_ONLINE;
	}

	function sc_online_members()
	{
		return MEMBERS_ONLINE;
	}

	function sc_online_members_list()
	{
		global $menu_pref;
		if($menu_pref['online_show_memberlist'])
		{
			return (MEMBERS_ONLINE ? MEMBER_LIST : '');
		}
	}


	function sc_online_page()
	{
		return ON_PAGE;
	}


	function sc_online_members_total()
	{
		$total_members = $this->e107->ecache->retrieve("online_menu_member_total", 120);
		if($total_members == false) 
		{
			$total_members = $this->e107->sql->db_Count('user','(*)',"where user_ban='0'");
			$this->e107->ecache->set("online_menu_member_total", $total_members);
		}
		return $total_members;
	}


	function sc_online_member_newest()
	{
		$ret = $this->e107->ecache->retrieve('online_menu_member_newest', 120);
		if($ret == false) 
		{
			$newest_member_sql = $this->e107->sql->db_Select('user', 'user_id, user_name', "user_ban='0' ORDER BY user_join DESC LIMIT 1");
			$row = $this->e107->sql->db_Fetch();
			$ret = "<a href='".e_HTTP."user.php?id.".$row['user_id']."'>".$row['user_name']."</a>";
			$this->e107->ecache->set("online_menu_member_newest", $ret);
		}
		return $ret;
	}


	function sc_online_most()
	{
		global $menu_pref;
		return intval($menu_pref['most_members_online'] + $menu_pref['most_guests_online']);
	}


	function sc_online_most_members()
	{
		global $menu_pref;
		return $menu_pref['most_members_online'];
	}


	function sc_online_most_guests()
	{
		global $menu_pref;
		return $menu_pref['most_guests_online'];
	}


	function sc_online_most_datestamp()
	{
		global $menu_pref, $gen;
		return $gen->convert_date($menu_pref['most_online_datestamp'], "short");
	}



	//##### ONLINE MEMBER LIST EXTENDED 
	function sc_online_member_list_extended()
	{
		global $ONLINE_MEMBERS_LIST_EXTENDED;
		return $ONLINE_MEMBERS_LIST_EXTENDED;
	}


	function sc_online_member_image()
	{
		return "<img src='".e_IMAGE."admin_images/users_16.png' alt='' style='vertical-align:middle' />";
	}


	function sc_online_member_user()
	{
		global $oid, $oname;
		return "<a href='".e_BASE."user.php?id.$oid'>$oname</a>";
	}


	function sc_online_member_page()
	{
		global $pinfo, $ADMIN_DIRECTORY, $online_location_page;
		return (!strstr($pinfo, $ADMIN_DIRECTORY) ? "<a href='".$pinfo."'>".$online_location_page."</a>" : $online_location_page);
	}
}

?>