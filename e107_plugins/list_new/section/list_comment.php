<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Comment e_list Handler
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/list_new/section/list_comment.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

/**
 *	Comment interface for list_new plugin 
 *
 *	@package e107_plugins
 *	@subpackage list_new
 */

if (!defined('e107_INIT')) { exit; }

global $tp, $cobj;
if(!is_object($cobj))
{
	require_once(e_HANDLER."comment_class.php");
	$cobj = new comment;
}

class list_comment
{
	function __construct($parent)
	{
		$this->parent = $parent;
	}

	function getListData()
	{
		global $tp, $cobj;

		$list_caption = $this->parent->settings['caption'];
		$list_display = ($this->parent->settings['open'] ? "" : "none");

		$bullet = $this->parent->getBullet($this->parent->settings['icon']);

		$qry = '';
		if($this->parent->mode == "new_page" || $this->parent->mode == "new_menu" )
		{
			$qry = "comment_datestamp>".$this->parent->getlvisit();
		}

		$data = $cobj->getCommentData(intval($this->parent->settings['amount']), '0', $qry);

		if(empty($data))
		{
			$list_data = LIST_COMMENT_2;
		}
		else
		{
			$list_data = array();
			foreach($data as $row)
			{
				$record = array();
				$rowheading = $this->parent->parse_heading($row['comment_title']);
				$record['icon'] = $bullet;
				if($row['comment_url'])
				{
					$record['heading'] = "<a href='".$row['comment_url']."'>".$this->parent->e107->tp->toHTML($rowheading, true)."</a>";
				}
				else
				{
					$record['heading'] = $this->parent->e107->tp->toHTML($rowheading, true);
				}
				$category = '';
				if(vartrue($this->parent->settings['category']))
				{
					if($row['comment_category_url'])
					{
						$record['category'] = "<a href='".$row['comment_category_url']."'>".$row['comment_category_heading']."</a>";
					}
					else
					{
						$record['category'] = $row['comment_category_heading'];
					}
				}
				$record['author'] = (vartrue($this->parent->settings['author']) ? $row['comment_author'] : '');
				$record['date'] = (vartrue($this->parent->settings['date']) ? $this->parent->getListDate($row['comment_datestamp']) : "");
				$record['icon'] = $bullet;
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

