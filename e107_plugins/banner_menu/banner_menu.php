<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/banner_menu/banner_menu.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:34:44 $
|     $Author: mcfly_e107 $
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
if (file_exists(THEME."banner_template.php")) {
	require_once(THEME."banner_template.php");
} else {
	require_once(e_BASE.$THEMES_DIRECTORY."templates/banner_template.php");
}

if(isset($campaign)){
	$parm = (isset($campaign) ? $campaign : "");
	$bannersccode = file_get_contents(e_FILE."shortcode/banner.sc");
	$BANNER = eval($bannersccode);
	$txt = $BANNER_MENU_START;
	$txt .= preg_replace("/\{(.*?)\}/e", '$\1', $BANNER_MENU);
	$txt .= $BANNER_MENU_END;
	
}else{
	if (isset($menu_pref['banner_campaign']) && $menu_pref['banner_campaign'])
	{
		if(strstr($menu_pref['banner_campaign'], "|"))
		{
			$campaignlist = explode("|", $menu_pref['banner_campaign']);
			$amount = ($menu_pref['banner_amount']<1 ? '1' : $menu_pref['banner_amount']);
			$amount = ($amount > count($campaignlist) ? count($campaignlist) : $amount);
			$keys = array_rand($campaignlist, $amount);
			$parms = array();
			foreach($keys as $k=>$v){
				$parms[] = $campaignlist[$v];
			}
		}
		else
		{
			$parms[] = $menu_pref['banner_campaign'];
		}
	}

	$txt = $BANNER_MENU_START;
	foreach($parms as $parm){
		$bannersccode = file_get_contents(e_FILE."shortcode/banner.sc");
		$BANNER = eval($bannersccode);
		$txt .= preg_replace("/\{(.*?)\}/e", '$\1', $BANNER_MENU);
	}
	$txt .= $BANNER_MENU_END;
}

$text = $txt;

if (isset($menu_pref['banner_rendertype']) && $menu_pref['banner_rendertype'] == 2)
{
	$ns->tablerender($menu_pref['banner_caption'], $text);
}
else
{
	echo $text;
}

?>