<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Sitelinks configuration module - News
 *
 * $URL$
 * $Id$
 *
*/

if (!defined('e107_INIT')) { exit; }

//TODO Lans

class news_sitelink // include plugin-folder in the name.
{
	function config()
	{	
		$links = array();
		
		$links[] = array(
			'name'			=> "News Category List",
			'function'		=> "news_category_list",
			'description' 	=> ""
		);	
		
		$links[] = array(
			'name'			=> "News Category Pages",
			'function'		=> "news_category_page",
			'description' 	=> ""
		);	
			
		$links[] = array(
			'name'			=> "Last 10 News Items",
			'function'		=> "last_ten",
			'description' 	=> ""
		);

		
		return $links;
	}




	function news_category_page()
	{
		return $this->news_category_list('category');	
	}
	
	
	function news_cats() // BC
	{
		return $this->news_category_list();
	}


	function news_category_list($type=null) 
	{
		$sql = e107::getDb();
		$sublinks = array();
		
//		$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";
		$query = "SELECT * FROM #news_category ";
	//	$query .= "WHERE news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.") ";
		$query .= "	ORDER BY category_order ASC";
		
		if($type == null)
		{
			$type = 'short';	
		}
		
		$urlPath = 'news/list/'.$type;
		
		if($sql->gen($query))
		{		
			while($row = $sql->fetch())
			{

				$row['id'] = $row['category_id'];
				$sublinks[] = array(
					'link_name'			=> $row['category_name'],
					'link_url'			=> e107::getUrl()->create($urlPath, $row, array('allow' => 'id,category_sef,category_name,category_id')), // 'news.php?extend.'.$row['news_id'],
					'link_description'	=> $row['category_meta_description'],
					'link_button'		=> '',
					'link_category'		=> '',
					'link_order'		=> '',
					'link_parent'		=> '',
					'link_open'			=> '',
					'link_class'		=> 0
				);
			}
			
			$sublinks[] = array(
					'link_name'			=> LAN_MORE,
					'link_url'			=> e107::getUrl()->create('news/list/all'),  
					'link_description'	=> '',
					'link_button'		=> '',
					'link_category'		=> '',
					'link_order'		=> '',
					'link_parent'		=> '',
					'link_open'			=> '',
					'link_class'		=> 0
				);
				
			return $sublinks;
	    };
	}


	function last_ten()
	{
		$sql = e107::getDb();
		$sublinks = array();
		
		$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";
		$query = "SELECT * FROM #news WHERE news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.") ORDER BY news_datestamp DESC LIMIT 10";


		if($sql->gen($query))
		{		
			while($row = $sql->fetch())
			{
				$sublinks[] = array(
					'link_name'			=> $row['news_title'],
					'link_url'			=> e107::getUrl()->create('news/view/item', $row, array('allow' => 'news_sef,news_title,news_id')), // 'news.php?extend.'.$row['news_id'],
					'link_description'	=> $row['news_summary'],
					'link_button'		=> '',
					'link_category'		=> '',
					'link_order'		=> '',
					'link_parent'		=> '',
					'link_open'			=> '',
					'link_class'		=> intval($row['news_class'])
				);


			}
			
			$sublinks[] = array(
					'link_name'			=> LAN_MORE,
					'link_url'			=> e107::getUrl()->create('news/list/all'),  
					'link_description'	=> '',
					'link_button'		=> '',
					'link_category'		=> '',
					'link_order'		=> '',
					'link_parent'		=> '',
					'link_open'			=> '',
					'link_class'		=> intval($row['news_class'])
				);



				
			return $sublinks;
	    };
	}


	
}



?>