<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

/*
To define your own banner to use here ...

1. Go to admin -> banners and create a campaign, then add your banner to it
2. Add this line to this file ...

	$campaign = NAME_OF_YOUR_CAMPAIGN

3. Save file
*/


global $THEMES_DIRECTORY;
if (file_exists(THEME."banner_template.php")) 
{
  require_once(THEME."banner_template.php");
} 
else 
{
  require_once(e_BASE.$THEMES_DIRECTORY."templates/banner_template.php");
}

if(isset($campaign))
{
  $parm = $campaign;
  $bannersccode = file_get_contents(e_FILE."shortcode/banner.sc");
  $BANNER = eval($bannersccode);
  $txt = $BANNER_MENU_START;
  $txt .= preg_replace("/\{(.*?)\}/e", '$\1', $BANNER_MENU);
  $txt .= $BANNER_MENU_END;
	
}
else
{
	if (isset($menu_pref['banner_campaign']) && $menu_pref['banner_campaign'])
	{
		$parms = array();
		if(strstr($menu_pref['banner_campaign'], "|"))
		{
			$campaignlist = explode("|", $menu_pref['banner_campaign']);
			$amount = ($menu_pref['banner_amount']<1 ? '1' : $menu_pref['banner_amount']);
			$amount = ($amount > count($campaignlist) ? count($campaignlist) : $amount);
			$keys = array_rand($campaignlist, $amount);		// If one entry, returns a single value
			if (!is_array($keys))
			{
				$keys = array($keys);
			}
			foreach($keys as $k=>$v)
			{
				$parms[] = $campaignlist[$v];
			}
		}
		else
		{
			$parms[] = $menu_pref['banner_campaign'];
		}
	}

	$txt = $BANNER_MENU_START;
	if (isset($parms) && is_array($parms) && count($parms))
	{
		foreach($parms as $parm)
		{
			$bannersccode = file_get_contents(e_FILE."shortcode/banner.sc");
			$BANNER = eval($bannersccode);
			$txt .= preg_replace("/\{(.*?)\}/e", '$\1', $BANNER_MENU);
		}
	}
	$txt .= $BANNER_MENU_END;
}

if (isset($menu_pref['banner_rendertype']) && $menu_pref['banner_rendertype'] == 2)
{
	$ns->tablerender($menu_pref['banner_caption'], $txt);
}
else
{
	echo $txt;
}

?>