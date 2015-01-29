<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 *	e107 Banner management plugin
 *
 *	Handles the display and sequencing of banners on web pages, including counting impressions
 *
 *	@package	e107_plugins
 *	@subpackage	banner
 */

if (!defined('e107_INIT')) { exit; }

/*
To define your own banner to use here ...

1. Go to admin -> banners and create a campaign, then add your banner to it
2. Add this line to this file ...

	$campaign = NAME_OF_YOUR_CAMPAIGN

3. Save file
*/


if(file_exists(THEME.'templates/banner/banner_template.php')) // v2.x location. 
{
	require_once (THEME.'templates/banner/banner_template.php');
}
elseif(file_exists(THEME.'banner_template.php')) // v1.x location. 
{
	require_once (THEME.'banner_template.php');
}
else
{
	require_once (e_PLUGIN.'banner/banner_template.php');
}

$menu_pref = e107::getConfig('menu')->getPref('');

if(isset($campaign))
{
	$parm = $campaign;
	$txt = $BANNER_MENU_START;
	$txt .= e107::getParser()->parseTemplate("{BANNER=".$parm."}",true); 
	$txt .= $BANNER_MENU_END;

}
else
{
	if(isset($menu_pref['banner_campaign']) && $menu_pref['banner_campaign'])
	{
		$parms = array();
		if(strstr($menu_pref['banner_campaign'], "|"))
		{
			$campaignlist = explode('|', $menu_pref['banner_campaign']);
			$amount = ($menu_pref['banner_amount'] < 1 ? '1' : $menu_pref['banner_amount']);
			$amount = ($amount > count($campaignlist) ? count($campaignlist) : $amount);
			$keys = array_rand($campaignlist, $amount);		// If one entry, returns a single value
			if (!is_array($keys))
			{
				$keys = array($keys);
			}
			foreach ($keys as $k=>$v)
			{
				$parms[] = $campaignlist[$v];
			}
		}
		else
		{
			$parms[] = $menu_pref['banner_campaign'];
		}
		
		$txt = $BANNER_MENU_START;

		foreach ($parms as $parm)
		{
			$txt .= e107::getParser()->parseTemplate("{BANNER=".$parm."}",true); 
		}
		
		$txt .= $BANNER_MENU_END;
	}


}

if(isset($menu_pref['banner_rendertype']) && $menu_pref['banner_rendertype'] == 2)
{
	$ns->tablerender($menu_pref['banner_caption'], $txt);
}
else
{
	echo $txt;
}


?>