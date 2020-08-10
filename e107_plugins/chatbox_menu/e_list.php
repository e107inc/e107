<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Chatbox e_list Handler
 *
*/
if (!defined('e107_INIT')) { exit; }

class list_chatbox_menu
{
	function __construct($parent)
	{
		$this->parent = $parent;
	}

	function getListData()
	{
		$sql = e107::getDb();
		$tp = e107::getParser();
		$list_caption = $this->parent->settings['caption'];
		$list_display = ($this->parent->settings['open'] ? "" : "none");

		if($this->parent->mode == "new_page" || $this->parent->mode == "new_menu" )
		{
			$lvisit = $this->parent->getlvisit();
			$qry = "cb_datestamp>".$lvisit;
		}
		else
		{
			$qry = "cb_id != '0' ";
		}
		$qry .= " ORDER BY cb_datestamp DESC LIMIT 0,".intval($this->parent->settings['amount']);

		$bullet = $this->parent->getBullet($this->parent->settings['icon']);

		if(!$chatbox_posts = $this->parent->e107->sql->gen("SELECT * FROM #chatbox WHERE ".$qry))
		{ 
			$list_data = LIST_CHATBOX_2;
		}
		else
		{
			while($row = $this->parent->e107->sql->fetch())
			{
				$cb_id = substr($row['cb_nick'] , 0, strpos($row['cb_nick'] , "."));
				$cb_nick = substr($row['cb_nick'] , (strpos($row['cb_nick'] , ".")+1));
				$cb_message = ($row['cb_blocked'] ? CHATBOX_L6 : str_replace("<br />", " ", $tp->toHTML($row['cb_message'])));
		//	$rowheading = $this->parent->parse_heading($cb_message);

				//<a href='".e_BASE."user.php?id.$cb_id'>".$cb_nick."</a>
				$uparams = array('id' => $cb_id, 'name' => $cb_nick);
				$link = e107::getUrl()->create('user/profile/view', $uparams);
				$userlink = "<a href='".$link."'>".$cb_nick."</a>";
				$record['icon'] = $bullet;
			//	$record['heading'] = $rowheading;
				$record['author'] = ($this->parent->settings['author'] ? ($cb_id != 0 ? $userlink : $cb_nick) : "");
				$record['category'] = "";
				$record['date'] = ($this->parent->settings['date'] ? ($row['cb_datestamp'] ? $this->parent->getListDate($row['cb_datestamp']) : "") : "");
				$record['info'] = $cb_message;

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

