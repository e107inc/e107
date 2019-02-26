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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/_blank/e_sitelink.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

if (!defined('e107_INIT')) { exit; }
/*if(!e107::isInstalled('_blank'))
{ 
	return;
}*/



class download_sitelink // include plugin-folder in the name.
{
	function config()
	{
		global $pref;
		
		$links = array();
			
		$links[] = array(
			'name'			=> "Download Categories",
			'function'		=> "categories"
		);


		
		return $links;
	}
	


	function categories()
	{
		$sql = e107::getDb();
		$tp = e107::getParser();
		$sublinks = array();
		
		// $sql->select("download_category","*","download_category_id != '' ");

		$where = "download_category_class IN (".USERCLASS_LIST.")";

		$sql->selectTree('download_category','download_category_parent', 'download_category_id', 'download_category_order', $where );
		
		while($row = $sql->fetch())
		{


			if(empty($row['download_category_name']))
			{
				continue;
			}

			$sublinks[] = array(
				'link_name'			=> $tp->toHTML($row['download_category_name'],'','TITLE'),
				'link_url'			=> e107::url('download', 'category', $row),
				'link_description'	=> '',
				'link_button'		=> $row['download_category_icon'],
				'link_category'		=> '',
				'link_order'		=> '',
				'link_parent'		=> '',
				'link_open'			=> '',
				'link_class'		=> e_UC_PUBLIC,
				'link_depth'        => $row['_depth']
			);
		}


		return $sublinks;
	    
	}
	
}
