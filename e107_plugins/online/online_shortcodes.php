<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Online shortcodes
 *
*/
if (!defined('e107_INIT')) { exit; }


register_shortcode('online_shortcodes', true);
initShortcodeClass('online_shortcodes');

class online_shortcodes
{
	protected $e107;
	public $memberInfo = array();				// Site stats
	public $currentMember = array('oid' => '0', 'oname' => '??', 'page' => 'lost');
	public $currentUser = array();				// Information about current user (for last seen)
	public $onlineMembersList = '';
	protected $gen;
	
	
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
		return $seen_ago;
		// return ($seen_ago ? $seen_ago : '1 '.LANDT_09).' '.LANDT_AGO;
	}


	function sc_online_tracking_disabled()
	{
		$url = e_ADMIN."users.php?mode=main&amp;action=prefs";

		$srch = array("[","]");
		$repl = array("<a href='".$url."'>", "</a>");

		$message = str_replace($srch,$repl, LAN_ONLINE_TRACKING_MESSAGE);
		return e107::getParser()->toHTML($message, true);

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
			$total_members = e107::getDb()->count('user','(*)',"where user_ban='0'");
			$this->e107->ecache->set("online_menu_member_total", $total_members);
		}
		return $total_members;
	}


	function sc_online_member_newest()
	{
		$sql = e107::getDb();
		$ret = $this->e107->ecache->retrieve('online_menu_member_newest', 120);
		if($ret == false) 
		{
			$newest_member_sql = $sql->select('user', 'user_id, user_name', "user_ban='0' ORDER BY user_join DESC LIMIT 1");
			$row = $sql->fetch();
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


	function sc_online_member_image($parm='')
	{
		if($parm == 'avatar')
		{
			$userData = array(
				'user_image' => $this->currentMember['oimage'],
				'user_name'	=> $this->currentMember['oname']
			); 
			
			return e107::getParser()->toAvatar($userData); 
			
		//	return e107::getParser()->parseTemplate("{USER_AVATAR=".$this->currentMember['oimage']."}",true);	
		}
		
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