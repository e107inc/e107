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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/news/e_sitelink.php,v $
 * $Revision: 1.1 $
 * $Date: 2009-12-17 16:00:56 $
 * $Author: e107coders $
 *
*/

if (!defined('e107_INIT')) { exit; }

//TODO Lans

class news_sitelinks // include plugin-folder in the name.
{
	function config()
	{	
		$links = array();
			
		$links[] = array(
			'name'			=> "Last 10 News Items",
			'function'		=> "last_ten",
			'description' 	=> ""
		);	
		
		return $links;
	}
	
	

	function last_ten() 
	{
		$sql = e107::getDb();
		$sublinks = array();
		
		$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";
		$query = "SELECT * FROM #news WHERE news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.") ORDER BY news_datestamp DESC LIMIT 10";
		
		if($sql->db_Select_gen($query))
		{		
			while($row = $sql->db_Fetch())
			{
				$sublinks[] = array(
					'link_name'			=> $row['news_title'],
					'link_url'			=> 'news.php?extend.'.$row['news_id'],
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
					'link_name'			=> "More...",
					'link_url'			=> 'news.php?all',
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