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



class _blank_sitelink // include plugin-folder in the name.
{
	function config()
	{
		global $pref;
		
		$links = array();
			
		$links[] = array(
			'name'			=> "Drop-Down Links",
			'function'		=> "myCategories"
		);

		$links[]  = array(

			'name'          => 'Drop-Down MegaMenu',
			'function'      => 'megaMenu'
		);
		
		
		return $links;
	}
	

	function megaMenu() // http://bootsnipp.com/snippets/33gmp
	{
		$text = '<div class="dropdown-menu mega-dropdown-menu">
                    <div class="container-fluid2">
                            <ul class="nav-list list-inline">
                                <li><a data-filter=".89" href="#"><img src="http://content.nike.com/content/dam/one-nike/globalAssets/menu_header_images/OneNike_Global_Nav_Icons_Running.png"><span>BRICS</span></a></li>
                                <li><a data-filter=".97" href="#"><img src="http://content.nike.com/content/dam/one-nike/globalAssets/menu_header_images/OneNike_Global_Nav_Icons_Basketball.png"><span>Latin America</span></a></li>
                                <li><a data-filter=".96" href="#"><img src="http://content.nike.com/content/dam/one-nike/globalAssets/menu_header_images/OneNike_Global_Nav_Icons_Football.png"><span>USA</span></a></li>
                                <li><a data-filter=".87" href="#"><img src="http://content.nike.com/content/dam/one-nike/globalAssets/menu_header_images/OneNike_Global_Nav_Icons_Soccer.png"><span>Middle East</span></a></li>
                                <li><a data-filter=".85" href="#"><img src="http://content.nike.com/content/dam/one-nike/globalAssets/menu_header_images/OneNike_Global_Nav_Icons_MensTraining.png"><span>Asia</span></a></li>
                               <li><a data-filter=".90" href="#"><img src="http://content.nike.com/content/dam/one-nike/globalAssets/menu_header_images/OneNike_Global_Nav_Icons_MensTraining.png"><span>Africa</span></a></li>
                            </ul>
                    </div>
				</div>			';

		return $text;

	}







	function myCategories()
	{
		$sql = e107::getDb();
		$tp = e107::getParser();
		$sublinks = array();
		
		$sql->select("blank","*","blank_id != '' ");
		
		while($row = $sql->fetch())
		{
			$sublinks[] = array(
				'link_name'			=> $tp->toHTML($row['blank_name'],'','TITLE'),
				'link_url'			=> e107::url('_blank', 'other', $row),
				'link_description'	=> '',
				'link_button'		=> $row['blank_icon'],
				'link_category'		=> '',
				'link_order'		=> '',
				'link_parent'		=> '',
				'link_open'			=> '',
				'link_class'		=> e_UC_PUBLIC
			);
		}
		
		return $sublinks;
	    
	}
	
}
