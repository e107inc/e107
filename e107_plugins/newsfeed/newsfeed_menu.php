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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/newsfeed/newsfeed_menu.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-28 18:23:56 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
if(!function_exists("checkUpdate"))
{
	require(e_PLUGIN."newsfeed/newsfeed_functions.php");
}
global $tp;

/* get template */
if (file_exists(THEME."newsfeed_menu_template.php"))
{
	require_once(THEME."newsfeed_menu_template.php");
}
else if(!$NEWSFEED_MENU_START)
{
	require_once(e_PLUGIN."newsfeed/templates/newsfeed_menu_template.php");
}



checkUpdate("newsfeed_active=1 OR newsfeed_active=3");
$text = "";

if ($feeds = $sql -> db_Select("newsfeed", "*", "newsfeed_active=1 OR newsfeed_active=3"))
{
	while($row = $sql->db_Fetch())
	{
		extract ($row);
		$dataArray = parseData($newsfeed_data);
		$FEEDNAME = "<a href='".e_SELF."?show.$newsfeed_id'>$newsfeed_name</a>";
		$FEEDDESCRIPTION = $newsfeed_description;
		if($newsfeed_image == "default")
		{
			$FEEDIMAGE = ($dataArray['image'] ? "<a href='".$dataArray['link']."' rel='external'><img src='".$dataArray['image']."' alt='' style='border: 0; vertical-align: middle;' /></a>" : "");
		}else if ($newsfeed_image)
		{
			$FEEDIMAGE = $newsfeed_image;
		}
		else
		{
			$FEEDIMAGE = "";
		}
		$FEEDLANGUAGE = $dataArray['language'];
		$FEEDLASTBUILDDATE = NFLAN_33.($dataArray['lastbuilddate'] ? $dataArray['lastbuilddate'] : NFLAN_34);
		$FEEDCOPYRIGHT = $tp -> toHTML($dataArray['copyright'], TRUE);
		$FEEDTITLE = "<a href='".$dataArray['link']."' rel='external'>".$dataArray['title']."</a>";
		$FEEDLINK = $dataArray['link'];
		$LINKTOMAIN = "<a href='".e_PLUGIN."newsfeed/newsfeed.php?show.$newsfeed_id'>".NFLAN_39."</a>";

		$data = "";
		for($loop=0; $loop<=($items-1); $loop++)
		{
			$FEEDITEMLINK = $dataArray['itemlink'][$loop];
			$feeditemtext = $tp -> toHTML($dataArray['itemtext'][$loop], TRUE);
			$FEEDITEMTEXT = (strlen($feeditemtext) > $truncate ? substr($feeditemtext, 0, $truncate).$truncate_string : $feeditemtext);
			$FEEDITEMCREATOR = $tp -> toHTML($dataArray['itemcreator'][$loop], TRUE);
			$data .= preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MENU);
		}
		$BACKLINK = "<a href='".e_SELF."'>".NFLAN_31."</a>";
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MENU_START) . $data . preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MENU_END);
	}
}

$ns->tablerender(NFLAN_38, $text);






?>