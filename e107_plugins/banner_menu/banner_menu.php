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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/banner_menu/banner_menu.php,v $
|     $Revision: 1.8 $
|     $Date: 2005-03-05 09:44:08 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
if(!defined("e_HANDLER")){ exit; }

/*
To define your own banner to use here ...

1. Go to admin -> banners and create a campaign, then add your banner to it
2. Add this line to this file ...

	$campaign = NAME_OF_YOUR_CAMPAIGN

3. Save file
*/


if(!isset($campaign))
{
	if (isset($menu_pref['banner_campaign']) && $menu_pref['banner_campaign'])
	{
		$campaignlist = explode("|", $menu_pref['banner_campaign']);
		$campaignlist = array_slice($campaignlist, 0, -1);
	}
}

$bannersccode = file_get_contents(e_FILE."shortcode/banner.sc");
$parm = $campaignlist[0];
$text = eval($bannersccode);

if (isset($menu_pref['banner_rendertype']) && $menu_pref['banner_rendertype'] == 2)
{
	$ns->tablerender($menu_pref['banner_caption'], $text);
}
else
{
	echo $text;
}


?>