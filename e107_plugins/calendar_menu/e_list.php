<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Calendar e_list Handler
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/e_list.php,v $
 * $Revision: 1.6 $
 * $Date: 2009-11-18 01:05:23 $
 * $Author: e107coders $
 *
*/
if (!defined('e107_INIT')) { exit; }

class list_calendar_menu
{
	function list_calendar_menu($parent)
	{
		$this->parent = $parent;
	}

	function getListData()
	{
		$list_caption = $this->parent->settings['caption'];
		$list_display = ($this->parent->settings['open'] ? "" : "none");

		require_once('ecal_class.php');
		$ecal_class = new ecal_class;

		$current_day = $ecal_class->cal_date['mday'];
		$current_month = $ecal_class->cal_date['mon'];
		$current_year = $ecal_class->cal_date['year'];

		$current = mktime(0,0,0,$current_month, $current_day, $current_year);

		if($this->parent->mode == "new_page" || $this->parent->mode == "new_menu" )
		{
			$lvisit = $this->parent->getlvisit();
			$qry = " event_datestamp>".intval($lvisit)." AND ";
		}
		else
		{
			$qry = "";
		}

		$bullet = $this->parent->getBullet($this->parent->settings['icon']);

		$qry = "
		SELECT e.*, c.event_cat_name
		FROM #event AS e 
		LEFT JOIN #event_cat AS c ON c.event_cat_id = e.event_category 
		WHERE ".$qry." e.event_start>='$current' AND c.event_cat_class REGEXP '".e_CLASS_REGEXP."' 
		ORDER BY e.event_start ASC LIMIT 0,".intval($this->parent->settings['amount']);

		if(!$event_items = $this->parent->e107->sql->db_Select_gen($qry))
		{
			$list_data = LIST_CALENDAR_2;
		}
		else
		{
			while($row = $this->parent->e107->sql->db_Fetch())
			{
				$record = array();
				$tmp = explode(".", $row['event_author']);
				if($tmp[0] == "0")
				{
					$record['author'] = $tmp[1];
				}
				elseif(is_numeric($tmp[0]) && $tmp[0] != "0")
				{
					$record['author'] = (USER ? "<a href='".e_BASE."user.php?id.".$tmp[0]."'>".$tmp[1]."</a>" : $tmp[1]);
				}
				else
				{
					$record['author'] = "";
				}

				$rowheading = $this->parent->parse_heading($row['event_title']);
				$record['icon'] = $bullet;
				$record['heading'] = "<a href='".e_PLUGIN."calendar_menu/event.php?".$row['event_start'].".event.".$row['event_id']."'>".$rowheading."</a>";
				$record['category'] = $row['event_cat_name'];
				$record['date'] = ($this->parent->settings['date'] ? ($row['event_start'] ? $this->parent->getListDate($row['event_start']) : "") : "");
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