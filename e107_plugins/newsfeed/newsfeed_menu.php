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
|     $Revision: 1.3 $
|     $Date: 2005-03-02 09:06:47 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
if(!defined("e_PLUGIN")){ exit; }
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
		$rss = unserialize($newsfeed_data);
		$FEEDNAME = "<a href='".e_SELF."?show.$newsfeed_id'>$newsfeed_name</a>";
		$FEEDDESCRIPTION = $newsfeed_description;
		if($newsfeed_image == "default")
		{
			if($file = fopen ($dataArray['image'], "r"))
			{
				/* remote image exists - use it! */
				$FEEDIMAGE = "<a href='".$rss -> image['link']."' rel='external'><img src='".$rss -> image['url']."' alt='".$rss -> image['title']."' style='border: 0; vertical-align: middle;' /></a>";
			}
			else
			{
				/* remote image doesn't exist - ghah! */
				$FEEDIMAGE = "";
			}


		}else if ($newsfeed_image)
		{
			$FEEDIMAGE = $newsfeed_image;
		}
		else
		{
			$FEEDIMAGE = "";
		}
		$FEEDLANGUAGE = $rss -> channel['language'];
		$FEEDLASTBUILDDATE = NFLAN_33.($rss -> channel['lastbuilddate'] ? $rss -> channel['lastbuilddate'] : NFLAN_34);
		$FEEDCOPYRIGHT = $tp -> toHTML($rss -> channel['copyright'], TRUE);
		$FEEDTITLE = "<a href='".$rss -> channel['link']."' rel='external'>".$rss -> channel['title']."</a>";
		$FEEDLINK = $rss -> channel['link'];
		$LINKTOMAIN = "<a href='".e_PLUGIN."newsfeed/newsfeed.php?show.$newsfeed_id'>".NFLAN_39."</a>";

		$data = "";

		$items = array_slice($rss->items, 0, 10);

		foreach ($items as $item)
		{
			$FEEDITEMLINK = "<a href='".$item['link']."' rel='external'>".$tp -> toHTML($item['title'], TRUE)."</a>";
			$feeditemtext = strip_tags(ereg_replace("&#091;.*]", "", $tp -> toHTML($item['description'], TRUE)));
			$FEEDITEMTEXT = (strlen($feeditemtext) > $truncate ? substr($feeditemtext, 0, $truncate).$truncate_string : $feeditemtext);
			$FEEDITEMCREATOR = $tp -> toHTML($item['author'], TRUE);
			$data .= preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MENU);
		}

		$BACKLINK = "<a href='".e_SELF."'>".NFLAN_31."</a>";
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MENU_START) . $data . preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MENU_END);
	}
}

$ns->tablerender(NFLAN_38, $text);

?>