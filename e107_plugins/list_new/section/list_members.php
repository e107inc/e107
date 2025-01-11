<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Members e_list Handler
 *
*/

/**
 *	Users interface for list_new plugin
 *
 *	@package e107_plugins
 *	@subpackage list_new
 */
if (!defined('e107_INIT')) { exit; }

class list_members
{
	function __construct($parent)
	{
		$this->parent = $parent;
	}

	function getListData()
	{
		$sql = e107::getDb();
		$list_caption = $this->parent->settings['caption'];
		$list_display = ($this->parent->settings['open'] ? "" : "none");

		$qry = '';
		if($this->parent->mode == "new_page" || $this->parent->mode == "new_menu" )
		{
			$qry = "user_join>".$this->parent->getlvisit()." AND ";
		}
		$qry .= " user_ban=0 ORDER BY user_join DESC LIMIT 0,".intval($this->parent->settings['amount']);

		$bullet = $this->parent->getBullet($this->parent->settings['icon']);

		if(!$this->parent->e107->sql->gen("SELECT user_id,user_name,user_join FROM #user WHERE ".$qry))
		{ 
			$list_data = LIST_MEMBER_2;
		}
		else
		{
			while($row = $this->parent->e107->sql->fetch())
			{
				$record = array();    
				$rowheading = $this->parent->parse_heading($row['user_name']);
				//<a href='".e_BASE."user.php?id.".$row['user_id']."'>".$rowheading."</a>
				$uparams = array('id' => $row['user_id'], 'name' => $rowheading);
				$link = e107::getUrl()->create('user/profile/view', $uparams);
				$userlink = "<a href='".$link."'>".$rowheading."</a>";
				$record['icon'] = $bullet;
				$record['heading'] = (USER ? $userlink : $rowheading);
				$record['category'] = '';
				$record['author'] = '';
				$record['date'] = (vartrue($this->parent->settings['date']) ? $this->parent->getListDate($row['user_join']) : "");
				$record['info'] = '';
				$list_data[] = $record;
			}
		}
		//return array with 'records', (global)'caption', 'display'
		return array(
			'records'=>$list_data, 
			'caption'=>$list_caption, 
			'display'=>$list_display
		);
	}
}

