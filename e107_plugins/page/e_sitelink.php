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
 * $Revision: 1.1 $
 * $Date: 2009-11-20 05:01:51 $
 * $Author: e107coders $
 *
*/

if (!defined('e107_INIT')) { exit; }
/*if(!plugInstalled('gsitemap'))
{ 
	return;
}*/


class page_sitelinks // include plugin-folder in the name.
{
	function config()
	{	
		$links = array();
			
		$links[] = array(
			'name'			=> "All Pages",
			'function'		=> "myfunction",
			'description' 	=> ""
		);	
		
		return $links;
	}
	
	

	function myfunction() 
	{
		$sql = e107::getDb();
		$sublinks = array();
		
		$sql->db_Select("page","*","page_theme = '' ORDER BY page_title");
		
		while($row = $sql->db_Fetch())
		{
			$sublinks[] = array(
				'link_name'			=> $row['page_title'],
				'link_url'			=> 'page.php?'.$row['page_id'],
				'link_description'	=> '',
				'link_button'		=> '',
				'link_category'		=> '',
				'link_order'		=> '',
				'link_parent'		=> '',
				'link_open'			=> '',
				'link_class'		=> intval($row['page_class'])
			);
		}
		
		return $sublinks;
	    
	}
	
}



?>