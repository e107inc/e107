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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/other_news_menu/other_news_menu.php,v $
|     $Revision: 1.7 $
|     $Date: 2005-02-11 06:59:45 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("othernews_parser.php");
unset($text);
global $OTHERNEWS_STYLE;

if(!$OTHERNEWS_STYLE){
	$OTHERNEWS_STYLE = "<img src='".THEME."images/bullet2.gif' alt='bullet' />&nbsp;{OTHERNEWS_LINK}<br />";
}

if(!defined("OTHERNEWS_LIMIT")){
	define("OTHERNEWS_LIMIT",10);
}

if(!defined("OTHERNEWS_ITEMLINKSTYLE")){
	define("OTHERNEWS_ITEMLINKSTYLE","");
}

if(!defined("OTHERNEWS_CATLINKSTYLE")){
	define("OTHERNEWS_CATLINKSTYLE","");
}

	$categories = array();
	$sql->db_Select("news_category");
	while ($row = $sql->db_Fetch()) {
		$categories['name'][$row['category_id']] = $row['category_name'];
		$categories['icon'][$row['category_id']] = $row['category_icon'];
	}

	$numb = OTHERNEWS_LIMIT;

if ($sql->db_Select("news", "*", "news_render_type=2 ORDER BY news_datestamp DESC LIMIT 0,$numb")) {
	while ($row = $sql->db_Fetch()) {
		$text .= othernews_parser($row,$OTHERNEWS_STYLE,$categories,OTHERNEWS_ITEMLINKSTYLE,OTHERNEWS_CATLINKSTYLE);
	}
	$ns->tablerender(TD_MENU_L1, $text, 'other_news2');
}

?>