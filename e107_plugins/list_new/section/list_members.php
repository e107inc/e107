<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Members e_list Handler
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/list_new/section/list_members.php,v $
 * $Revision: 1.6 $
 * $Date: 2009-11-18 01:05:47 $
 * $Author: e107coders $
 *
*/
if (!defined('e107_INIT')) { exit; }

class list_members
{
	function list_members($parent)
	{
		$this->parent = $parent;
	}

	function getListData()
	{
		$list_caption = $this->parent->settings['caption'];
		$list_display = ($this->parent->settings['open'] ? "" : "none");

		$qry = '';
		if($this->parent->mode == "new_page" || $this->parent->mode == "new_menu" )
		{
			$qry = "user_join>".$this->parent->getlvisit()." AND ";
		}
		$qry .= " user_ban=0 ORDER BY user_join DESC LIMIT 0,".intval($this->parent->settings['amount']);

		$bullet = $this->parent->getBullet($this->parent->settings['icon']);

		if(!$this->parent->e107->sql->db_Select_gen("SELECT user_id,user_name,user_join FROM #user WHERE ".$qry))
		{ 
			$list_data = LIST_MEMBER_2;
		}
		else
		{
			while($row = $this->parent->e107->sql->db_Fetch())
			{
				$record = array();
				$rowheading = $this->parent->parse_heading($row['user_name']);
				$record['icon'] = $bullet;
				$record['heading'] = (USER ? "<a href='".e_BASE."user.php?id.".$row['user_id']."'>".$rowheading."</a>" : $rowheading);
				$record['category'] = '';
				$record['author'] = '';
				$record['date'] = (varsettrue($this->parent->settings['date']) ? $this->parent->getListDate($row['user_join']) : "");
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

?>