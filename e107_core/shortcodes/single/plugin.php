<?php
/* $Id$ */

function plugin_shortcode($parm = '')
{
	if(empty($parm))
	{
		return null;
	}

	$tp = e107::getParser();

	@list($menu,$parms) = explode('|',$parm.'|', 2);


	$path = $tp->toDB(dirname($menu));
	$name = $tp->toDB(basename($menu));
	
	//BC Fix for v2.x
	
	$changeMenuPaths = array(
		array('oldpath'	=> 'siteinfo_menu',		'newpath' => 'siteinfo',	'menu' => 'sitebutton_menu'),
		array('oldpath'	=> 'siteinfo_menu',		'newpath' => 'siteinfo',	'menu' => 'compliance_menu'),
		array('oldpath'	=> 'siteinfo_menu',		'newpath' => 'siteinfo',	'menu' => 'powered_by_menu'),
		array('oldpath'	=> 'siteinfo_menu',		'newpath' => 'siteinfo',	'menu' => 'sitebutton_menu'),
		array('oldpath'	=> 'siteinfo_menu',		'newpath' => 'siteinfo',	'menu' => 'counter_menu'),
		array('oldpath'	=> 'siteinfo_menu',		'newpath' => 'siteinfo',	'menu' => 'latestnews_menu'),
		array('oldpath'	=> 'compliance_menu',	'newpath' => 'siteinfo',	'menu' => 'compliance_menu'),
		array('oldpath'	=> 'powered_by_menu',	'newpath' => 'siteinfo',	'menu' => 'powered_by_menu'),
		array('oldpath'	=> 'sitebutton_menu',	'newpath' => 'siteinfo',	'menu' => 'sitebutton_menu'),
		array('oldpath'	=> 'counter_menu',		'newpath' => 'siteinfo',	'menu' => 'counter_menu'),
		array('oldpath'	=> 'usertheme_menu',	'newpath' => 'user_menu',	'menu' => 'usertheme_menu'),
		array('oldpath'	=> 'userlanguage_menu',	'newpath' => 'user_menu',	'menu' => 'userlanguage_menu'),
		array('oldpath'	=> 'lastseen_menu',		'newpath' => 'online',		'menu' => 'lastseen_menu'),
		array('oldpath'	=> 'other_news_menu',	'newpath' => 'news',		'menu' => 'other_news_menu'),
		array('oldpath'	=> 'other_news_menu',	'newpath' => 'news',		'menu' => 'other_news2_menu')
	);
	
	foreach($changeMenuPaths as $k=>$v)
	{
		if($v['oldpath'] == $path && $v['menu'] == $name)
		{
			$path = $v['newpath'];
			continue;	
		}	
	}


	if($path == '.')
	{
	  $path = $menu;
	}
	/**
	 * @todo check if plugin is installed when installation required
	 */
	

	
	
	
	
	
	
	
	
	
	
	
	/**
	 *	fixed todo: $mode is provided by the menu itself, return is always true, added optional menu parameters
	 */
    return e107::getMenu()->renderMenu($path,$name, trim($parms, '|'),true);
}
