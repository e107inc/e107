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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/faqs/e_sitelink.php,v $
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


class faqs_sitelinks // include plugin-folder in the name.
{
	function config()
	{
		global $pref;
		
		$links = array();
			
		$links[] = array(
			'name'			=> "FAQ Categories",
			'function'		=> "faqCategories",
			'description' 	=> "FAQ Category links"
		);	
		
		
		return $links;
	}
	
	

	function faqCategories() 
	{
		$sql = e107::getDb();
		$sublinks = array();
		
		$sql->db_Select("faqs_info","*","faq_info_id != '' ORDER BY faq_info_order");
		
		while($row = $sql->db_Fetch())
		{
			$sublinks[] = array(
				'link_name'			=> $row['faq_info_title'],
				'link_url'			=> '{e_PLUGIN}faqs/faqs.php?cat.'.$row['faq_info_id'],
				'link_description'	=> $row['faq_info_about'],
				'link_button'		=> '',
				'link_category'		=> '',
				'link_order'		=> '',
				'link_parent'		=> '',
				'link_open'			=> '',
				'link_class'		=> intval($row['faq_info_class'])
			);
		}
		
		return $sublinks;
	    
	}
	
}



?>