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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/newsfeed/newsfeed.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-03-02 09:06:47 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");

@include_once(e_PLUGIN."newsfeed/languages/".e_LANGUAGE.".php");
@include_once(e_PLUGIN."newsfeed/languages/English.php");
if(!function_exists("checkUpdate"))
{
	require(e_PLUGIN."newsfeed/newsfeed_functions.php");
}
require_once(HEADERF);

/* get template */
if (file_exists(THEME."newsfeed_template.php"))
{
	require_once(THEME."newsfeed_template.php");
}
else if(!$NEWSFEED_LIST_START)
{
	require_once(e_PLUGIN."newsfeed/templates/newsfeed_template.php");
}

$action = FALSE;
if(e_QUERY)
{
	list($action, $id) = explode(".", e_QUERY);
	$id = intval($id);
}

if($action == "show")
{
	/* 'show' action - show feed */
	checkUpdate();

	if ($feeds = $sql -> db_Select("newsfeed", "*", "(newsfeed_active=2 OR newsfeed_active=3) AND newsfeed_id=$id"))
	{
		$row = $sql->db_Fetch();
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
		$FEEDDOCS = $rss -> channel['docs'];
		$FEEDTITLE = "<a href='".$rss -> channel['link']."' rel='external'>".$rss -> channel['title']."</a>";
		$FEEDLINK = $rss -> channel['link'];

		$data = "";
		foreach ($rss -> items as $item)
		{
			$FEEDITEMLINK = "<a href='".$item['link']."' rel='external'>".$tp -> toHTML($item['title'], TRUE)."</a>";
			$FEEDITEMTEXT = ereg_replace("&#091;.*]", "", $tp -> toHTML($item['description'], TRUE));
			$FEEDITEMCREATOR = $tp -> toHTML($item['author'], TRUE);
			$data .= preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MAIN);
		}
		$BACKLINK = "<a href='".e_SELF."'>".NFLAN_31."</a>";
		$text = preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MAIN_START) . $data . preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MAIN_END);
		$ns->tablerender(NFLAN_01, $text);
		require_once(FOOTERF);
		exit;
	}
}
	
/* no action - display feed list ... */
if ($feeds = $sql -> db_Select("newsfeed", "*", "newsfeed_active=2 OR newsfeed_active=3"))
{
	$data = "";
	while ($row = $sql->db_Fetch())
	{
		extract($row);
		$FEEDNAME = "<a href='".e_SELF."?show.$newsfeed_id'>$newsfeed_name</a>";
		$FEEDDESCRIPTION = ((!$newsfeed_description || $newsfeed_description == "default") ? "&nbsp;" : $newsfeed_description);
		$FEEDIMAGE = $newsfeed_image;
		$data .= preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_LIST);
	}
}

$text = $NEWSFEED_LIST_START . $data . $NEWSFEED_LIST_END;
$ns->tablerender(NFLAN_29, $text);
require_once(FOOTERF);

?>