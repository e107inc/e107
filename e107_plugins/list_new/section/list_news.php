<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * News e_list Handler
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/list_new/section/list_news.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

/**
 *	News interface for list_new plugin
 *
 *	@package e107_plugins
 *	@subpackage list_new
 */

if (!defined('e107_INIT')) { exit; }

class list_news
{
	function __construct($parent)
	{
		$this->parent = $parent;
	}

	function getListData()
	{
		if($this->parent->mode == "new_page" || $this->parent->mode == "new_menu" )
		{
			$qry = " n.news_datestamp>".$this->parent->getlvisit();
		}
		else
		{
			$qry = " (n.news_start=0 || n.news_start < ".time().") AND (n.news_end=0 || n.news_end>".time().") ";
		}

		$bullet = $this->parent->getBullet($this->parent->settings['icon']);

		$list_caption = $this->parent->settings['caption'];
		$list_display = (vartrue($this->parent->settings['open']) ? '' : LAN_NONE);

		$qry = "
		SELECT n.*, c.category_id AS news_category_id, c.category_name AS news_category_name, u.user_id AS news_author_id, u.user_name AS news_author_name
		FROM #news AS n
		LEFT JOIN #news_category AS c ON c.category_id = n.news_category
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		WHERE ".$qry." AND n.news_class REGEXP '".e_CLASS_REGEXP."'
		ORDER BY n.news_datestamp DESC LIMIT 0,".intval($this->parent->settings['amount']);

		if(!$this->parent->e107->sql->db_Select_gen($qry))
		{
			$list_data = LIST_NEWS_2;
		}
		else
		{
			$list_data = array();
			while($row=$this->parent->e107->sql->db_Fetch())
			{
				$row['news_title'] = $this->parse_news_title($row['news_title']);
				$rowheading = $this->parent->parse_heading($row['news_title']);

				$record = array();
				$record['icon'] = $bullet;
				$record['heading'] = "<a href='".e_BASE."news.php?item.".$row['news_id']."'>".$rowheading."</a>";

				$record['author'] = '';
				if(vartrue($this->parent->settings['author']))
				{
					if($row['news_author'] == 0)
					{
						$record['author'] = $row['news_author'];
					}
					else
					{
						if(vartrue($row['news_author_name']))
						{
							//$record['author'] = "<a href='".e_BASE."user.php?id.".$row['news_author_id']."'>".$row['news_author_name']."</a>";
							$uparams = array('id' => $row['news_author_id'], 'name' => $row['news_author_name']);
							$link = e107::getUrl()->create('user/profile/view', $uparams);
							$record['author'] = "<a href='".$link."'>".$row['news_author_name']."</a>";
						}
					}
				}

				$record['category'] = '';
				if(vartrue($this->parent->settings['category']))
				{
					$record['category'] = "<a href='".e_BASE."news.php?cat.".$row['news_category_id']."'>".$row['news_category_name']."</a>";
				}

				$record['date'] = '';
				if(vartrue($this->parent->settings['date']))
				{
					$record['date'] = $this->parent->getListDate($row['news_datestamp']);
				}

				$record['info'] = '';

				//collect each result
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

	//helpers
	function parse_news_title($title)
	{
		// copied from the rss creation, but added here to make sure the url for the newsitem is to the news.php?item.X
		// instead of the actual hyperlink that may have been added to a newstitle on creation
		$search = array();
		$replace = array();
		$search[0] = "/\<a href=\"(.*?)\">(.*?)<\/a>/si";
		$replace[0] = '\\2';
		$search[1] = "/\<a href='(.*?)'>(.*?)<\/a>/si";
		$replace[1] = '\\2';
		$search[2] = "/\<a href='(.*?)'>(.*?)<\/a>/si";
		$replace[2] = '\\2';
		$search[3] = "/\<a href=&quot;(.*?)&quot;>(.*?)<\/a>/si";
		$replace[3] = '\\2';
		$search[4] = "/\<a href=&#39;(.*?)&#39;>(.*?)<\/a>/si";
		$replace[4] = '\\2';
		$search[5] = "/\<a href=&#039;(.*?)&#039;>(.*?)<\/a>/si";
		$replace[5] = '\\2';
		return preg_replace($search, $replace, $title);
	}
}
