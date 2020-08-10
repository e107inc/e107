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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/download/e_list.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

class list_download
{
	function __construct($parent)
	{
		$this->parent = $parent;
	}

	function getListData()
	{
		$list_caption = $this->parent->settings['caption'];
		$list_display = ($this->parent->settings['open'] ? "" : "none");

		if($this->parent->mode == "new_page" || $this->parent->mode == "new_menu" )
		{
			$qry = " AND download_datestamp>".$this->parent->getlvisit();
		}
		else
		{
			$qry = '';
		}

		$bullet = $this->parent->getBullet($this->parent->settings['icon']);

		$qry = "SELECT d.download_id, d.download_name, d.download_author, d.download_datestamp,
		   dc.download_category_id, dc.download_category_name, dc.download_category_class
		   FROM #download AS d
		   LEFT JOIN #download_category AS dc ON d.download_category=dc.download_category_id
		   WHERE dc.download_category_class REGEXP '".e_CLASS_REGEXP."' AND d.download_class REGEXP '".e_CLASS_REGEXP."' AND d.download_active != '0' ".$qry."
		   ORDER BY download_datestamp DESC LIMIT 0,".intval($this->parent->settings['amount'])." ";

		$downloads = $this->parent->e107->sql->db_Select_gen($qry);
		if($downloads == 0)
		{
			$list_data = LIST_DOWNLOAD_2;
		}
		else
		{
			$list_data = array();
			while($row = $this->parent->e107->sql->db_Fetch())
			{
				$record = array();
				$rowheading = $this->parent->parse_heading($row['download_name']);
				$record['icon'] = $bullet;
				$record['heading'] = "<a href='".e_BASE."download.php?view.".$row['download_id']."'>".$rowheading."</a>";
				$record['author'] = (vartrue($this->parent->settings['author']) ? $row['download_author'] : "");
				$record['category'] = (vartrue($this->parent->settings['category']) ? "<a href='".e_BASE."download.php?list.".$row['download_category_id']."'>".$row['download_category_name']."</a>" : "");
				$record['date'] = (vartrue($this->parent->settings['date']) ? $this->parent->getListDate($row['download_datestamp']) : "");
				$record['info'] = "";

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
