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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/other_news_menu/other_news2_menu.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-02-11 06:59:45 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("othernews_parser.php");
unset($text);
global $OTHERNEWS2_STYLE;



if(!$OTHERNEWS2_STYLE){
   	$OTHERNEWS2_STYLE = "
	<table class='forumheader3' cellpadding='0' cellspacing='0' style='width:100%'>
	<tr><td class='caption2' colspan='2' style='padding:3px;text-decoration:none'>
	{OTHERNEWS_CATEGORY}
	</td></tr>
	<tr><td  style='padding:3px'>
	{OTHERNEWS_LINK}
	<br />
	{OTHERNEWS_SUMMARY}
	</td>
	<td style='padding:3px'>
	{OTHERNEWS_THUMBNAIL}
	</td>
	</tr>
	</table>
	";
}

if(!defined("OTHERNEWS2_LIMIT")){
	define("OTHERNEWS2_LIMIT",5);
}

if(!defined("OTHERNEWS2_ITEMLINKSTYLE")){
	define("OTHERNEWS2_ITEMLINKSTYLE","");
}

if(!defined("OTHERNEWS2_CATLINKSTYLE")){
	define("OTHERNEWS2_CATLINKSTYLE","");
}


	$categories = array();
	$sql->db_Select("news_category");
	while ($row = $sql->db_Fetch()) {
		$categories['name'][$row['category_id']] = $row['category_name'];
		$categories['icon'][$row['category_id']] = $row['category_icon'];
	}
	$numb = OTHERNEWS2_LIMIT;

if ($sql->db_Select("news", "*", "news_render_type=3 ORDER BY news_datestamp DESC LIMIT 0,$numb")) {
	while ($row = $sql->db_Fetch()) {
		$text .= othernews_parser($row,$OTHERNEWS2_STYLE,$categories,OTHERNEWS2_ITEMLINKSTYLE,OTHERNEWS2_CATLINKSTYLE);
	}
	$ns->tablerender(TD_MENU_L1, $text, 'other_news2');
}

?>