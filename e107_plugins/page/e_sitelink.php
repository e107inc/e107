<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Sitelinks configuration module - gsitemap
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/page/e_sitelink.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/
//require_once("../../class2.php");

if (!defined('e107_INIT')) { exit; }
/*if(!plugInstalled('gsitemap'))
{ 
	return;
}*/
//$pg = new page_sitelinks;
//$pg->myfunction();


class page_sitelinks // include plugin-folder in the name.
{
	function config()
	{	
		$links = array();
			
		$links[] = array(
			'name'			=> "All Pages",
			'function'		=> "pageNav",
			'description' 	=> ""
		);	
		
		return $links;
	}
	
	

	function pageNav() 
	{
		$sql = e107::getDb();
		$sublinks = array();
		
		$query = "SELECT p.*, c.* FROM #page AS p LEFT JOIN #page_chapters AS c ON p.page_chapter = c.chapter_id ORDER BY c.chapter_order,p.page_order"; 
		
		// $sql->db_Select("page","*","page_theme = '' ORDER BY page_title");
		$data = $sql->retrieve($query, true);
		$sublinks = array();
		
		foreach($data as $row)
		{
			$sublinks[] = array(
				'link_id'			=> $row['page_id'],
				'link_name'			=> $row['page_title'],
				'link_url'			=> 'page.php?'.$row['page_id'],
				'link_description'	=> '',
				'link_button'		=> '',
				'link_category'		=> '',
				'link_order'		=> $row['page_order'],
				'link_parent'		=> 0,
				'link_open'			=> '',
				'link_class'		=> intval($row['page_class'])
			);
		}
		
		
		$outArray 	= array();
		$ret =  e107::getNav()->compile($sublinks, $outArray);		
		// print_a($ret);
		return $ret;
	    //	compile();
	}
	
}



?>