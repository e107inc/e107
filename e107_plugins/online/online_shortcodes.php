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
 * $Revision: 1.5 $
 * $Date: 2009-12-28 17:53:11 $
 * $Author: e107steved $
*/
if (!defined('e107_INIT')) { exit; }


register_shortcode('online_shortcodes', true);
initShortcodeClass('online_shortcodes');

class online_shortcodes
{
	var $e107;
	var	$memberInfo = array();				// Site stats
	var	$currentMember = array('oid' => '0', 'oname' => '??', 'page' => 'lost');
	var	$currentUser = array();				// Information about current user (for last seen)
	var	$onlineMembersList = '';
	var	$gen;
	
	
	public function __construct()
	{
		$this->e107 = e107::getInstance();
		$this->memberInfo = e107::getConfig('history');
		$this->gen = e107::getDateConvert();
	}

	// Last Seen Menu
	function sc_lastseen_userlink()
	{
		return "<a href='".e_BASE."user.php?id.".$this->currentUser['user_id']."'>".$this->currentUser['user_name']."</a>";
	}

	function sc_lastseen_date()
	{
		$seen_ago = $this->gen->computeLapse($this->currentUser['user_currentvisit'], false, false, true, 'short');
		return ($seen_ago ? $seen_ago : '1 '.LANDT_09).' '.LANDT_AGO;
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
		if(e107::getConfig('menu')->get('online_show_memberlist', FALSE))
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
			$this->e107->ecache->set('online_menu_member_newest', $ret);
		}
		return $ret;
	}


	function sc_online_most()
	{
		return intval($this->memberInfo->get('most_members_online') + $this->memberInfo->get('most_guests_online'));
	}


	function sc_online_most_members()
	{
		return $this->memberInfo->get('most_members_online');
	}


	function sc_online_most_guests()
	{
		return $this->memberInfo->get('most_guests_online');
	}


	function sc_online_most_datestamp()
	{
		return $this->gen->convert_date($this->memberInfo->get('most_online_datestamp'), 'short');
	}



	//##### ONLINE MEMBER LIST EXTENDED 
	function sc_online_members_list_extended()
	{
		return $this->onlineMembersList;
	}


	function sc_online_member_image()
	{
		return "<img src='".e_IMAGE_ABS."admin_images/users_16.png' alt='' style='vertical-align:middle' />";
	}


	function sc_online_member_user()
	{
		return "<a href='".e_HTTP."user.php?id.{$this->currentMember['oid']}'>{$this->currentMember['oname']}</a>";
	}


	function sc_online_member_page()
	{
		global $ADMIN_DIRECTORY;
		return (!strstr($this->currentMember['pinfo'], $ADMIN_DIRECTORY) ? "<a href='".$this->currentMember['pinfo']."'>".$this->currentMember['page']."</a>" : $this->currentMember['page']);
	}
}

?>