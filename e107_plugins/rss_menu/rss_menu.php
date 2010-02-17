<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/rss_menu/rss_menu.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

global $FILES_DIRECTORY,$pref,$sql;
$path = e_PLUGIN_ABS."rss_menu/";

$des = "";
$topic = "";

if(strstr(e_SELF, "comment.php") && $sql -> db_Select("rss", "rss_path", " rss_path = 'comments' LIMIT 1")) {
	$type = 5;
	$des = RSS_MENU_L4;
}
if(strstr(e_SELF, "/forum")&& $sql -> db_Select("rss", "rss_path", " rss_path = 'forum|name' LIMIT 1") ) {
	$type = 6;
	$des = RSS_MENU_L5;
}
if(strstr(e_SELF, "forum_viewtopic") && $sql -> db_Select("rss", "rss_path", " rss_path = 'forum|topic' LIMIT 1")) {
	$type = 7;
	$des = RSS_MENU_L6;
}
if(strstr(e_SELF, "chat.php")&& $sql -> db_Select("rss", "rss_path", " rss_path = 'chatbox_menu' LIMIT 1")) {
	$type = 9;
	$des = RSS_MENU_L7;
}

if(strstr(e_SELF, "/bugtracker")) {
	$type = 10;
	$des = RSS_MENU_L8;
}

if(strstr(e_SELF, "download.php") && $sql -> db_Select("rss", "rss_path", " rss_path = 'download' LIMIT 1")) {
	$type = 12;
	$des = RSS_MENU_L9;
}

if(!$des) {
	$type = 1;
	$des = RSS_MENU_L3;
}

if(e_PAGE == "news.php" && $pref['rss_newscats'])
{

	$qry = explode(".",e_QUERY);
	if($qry[0] == "cat" || $qry[0] == "list")
	{
		$topic = intval($qry[1]);
	}
}

$text = "
<div style='text-align:center' class='smalltext'>
".$des.RSS_MENU_L1."<br />
<div class='spacer'><a href='".$path."rss.php?$type.1".($topic ? ".".$topic : "")."'><img src='".$path."images/rss1.png' alt='rss1.0' style='border:0' /></a></div>
<div class='spacer'><a href='".$path."rss.php?$type.2".($topic ? ".".$topic : "")."'><img src='".$path."images/rss2.png' alt='rss2.0' style='border:0' /></a></div>
<div class='spacer'><a href='".$path."rss.php?$type.3".($topic ? ".".$topic : "")."'><img src='".$path."images/rss3.png' alt='rdf' style='border:0' /></a><br /></div>
</div>";

$caption = (file_exists(THEME."images/RSS_menu.png") ? "<img src='".THEME_ABS."images/RSS_menu.png' alt='' style='vertical-align:middle' /> ".RSS_MENU_L2 : RSS_MENU_L2);

$ns->tablerender($caption, $text, 'backend');

?>
