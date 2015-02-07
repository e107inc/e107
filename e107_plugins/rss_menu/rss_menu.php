<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/rss_menu/rss_menu.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }
if (!e107::isInstalled('rss_menu')) 
{
	return '';
}

$FILES_DIRECTORY = e107::getInstance()->getFolder('files');
$sql = e107::getDb();
$path = e_PLUGIN_ABS."rss_menu/";
include_lan(e_PLUGIN."rss_menu/languages/".e_LANGUAGE."_admin_rss_menu.php");

$des = "";
$topic = "";

if(strstr(e_SELF, "comment.php") && $sql -> db_Select("rss", "rss_path", " rss_path = 'comments' LIMIT 1")) 
{
	$type = 5;
	$des = RSS_MENU_L4;
}
if(strstr(e_SELF, "/forum")&& $sql -> db_Select("rss", "rss_path", " rss_path = 'forum|name' LIMIT 1") ) 
{
	$type = 6;
	$des = RSS_MENU_L5;
}
if(strstr(e_SELF, "forum_viewtopic") && $sql -> db_Select("rss", "rss_path", " rss_path = 'forum|topic' LIMIT 1")) 
{
	$type = 7;
	$des = RSS_MENU_L6;
}
if(strstr(e_SELF, "chat.php")&& $sql -> db_Select("rss", "rss_path", " rss_path = 'chatbox_menu' LIMIT 1")) 
{
	$type = 9;
	$des = RSS_MENU_L7;
}
if(strstr(e_SELF, "/bugtracker")) 
{
	$type = 10;
	$des = RSS_MENU_L8;
}
if(strstr(e_SELF, "download.php") && $sql -> db_Select("rss", "rss_path", " rss_path = 'download' LIMIT 1")) 
{
	$type = 12;
	$des = RSS_MENU_L9;
}
if(!$des) 
{
	$type = 1;
	$des = RSS_MENU_L3;
}
if(e_PAGE == "news.php" && e107::getPref('rss_newscats'))
{

	$qry = explode(".",e_QUERY);
	if($qry[0] == "cat" || $qry[0] == "list")
	{
		$topic = intval($qry[1]);
	}
}

$text = "
<div>
	".$des.RSS_MENU_L1."<br />
	<div class='spacer'><a href='".$path."rss.php?$type.1".($topic ? ".".$topic : "")."'><img src='".$path."images/rss1.png' alt='rss1.0' /></a></div>
	<div class='spacer'><a href='".$path."rss.php?$type.2".($topic ? ".".$topic : "")."'><img src='".$path."images/rss2.png' alt='rss2.0' /></a></div>
	<div class='spacer'><a href='".$path."rss.php?$type.3".($topic ? ".".$topic : "")."'><img src='".$path."images/rss3.png' alt='rdf' /></a></div>
	<div class='spacer'><a href='".$path."rss.php?$type.4".($topic ? ".".$topic : "")."'><img src='".$path."images/rss4.png' alt='atom' /></a></div>
</div>";

// Deprecated - can be controlled within tablestyle (theme) - case mod == 'rss_menu'
//$caption = (file_exists(THEME."images/RSS_menu.png") ? "<img src='".THEME_ABS."images/RSS_menu.png' alt='' style='vertical-align:middle' /> ".RSS_MENU_L2 : RSS_MENU_L2);
e107::getRender()->tablerender(RSS_MENU_L2, $text, 'rss_menu');
?>