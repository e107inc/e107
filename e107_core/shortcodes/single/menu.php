<?php
if(!defined('e107_INIT'))
{
	exit();
}

function menu_shortcode($parm, $mode='')
{
	list($path,$echo) = explode(':', $parm);
	
	if($mode) // New in v2.x. eg. {MENU|feature-1} Renders a menu called 'feature-1' as found in the e107_page table  See admin Pages/Menus . 
	{
		return e107::getMenu()->renderMenu($mode, false);		
	}	
	
	if(is_numeric($path)) // eg. {MENU=1} - renders area 1 as found in the e107_menu db table. 
	{
		return e107::getMenu()->renderArea($parm);		
	}
	else // eg. {MENU=contact} for e107_plugins/contact/contact_menu.php OR {MENU=contact/other} for e107_plugins/contact/other_menu.php 
	{
		list($plugin,$menu) = explode("/",$path,2); 
		
		if($menu == '')
		{
			$menu = $plugin;	
		}
		
		return e107::getMenu()->renderMenu($plugin,$menu."_menu");		
	}
}


?>