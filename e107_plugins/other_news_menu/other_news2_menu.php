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
|     $Revision: 1.4 $
|     $Date: 2005-02-15 00:41:08 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
// require_once("othernews_parser.php");
require_once(e_HANDLER."news_class.php");
unset($text);
global $OTHERNEWS2_STYLE;
$ix = new news;

if(!$OTHERNEWS2_STYLE){
	$OTHERNEWS2_STYLE = "
	<table class='forumheader3' cellpadding='0' cellspacing='0' style='width:100%'>
	<tr><td class='caption2' colspan='2' style='padding:3px;text-decoration:none;'>
	{NEWSCATICON}
	{NEWSCATEGORY}
	</td></tr>
	<tr><td  style='padding:3px;vertical-align:top'>
	{NEWSTITLELINK}
	<br />
	{NEWSSUMMARY}
	</td>
	<td style='padding:3px'>
	{NEWSTHUMBNAIL}
	</td>
	</tr>
	</table>
	";
}

if(!defined("OTHERNEWS2_LIMIT")){
	define("OTHERNEWS2_LIMIT",5);
}

if(!defined("OTHERNEWS2_ITEMLINK")){
	define("OTHERNEWS2_ITEMLINK","");
}
if(!defined("OTHERNEWS2_CATLINK")){
	define("OTHERNEWS2_CATLINK","");
}
if(!defined("OTHERNEWS2_CATICON")){
	define("OTHERNEWS2_CATICON","border:0px");
}
if(!defined("OTHERNEWS2_THUMB")){
	define("OTHERNEWS2_THUMB","border:0px");
}


$param['itemlink'] = OTHERNEWS2_ITEMLINK;
$param['thumbnail'] = OTHERNEWS2_THUMB;
$param['catlink'] = OTHERNEWS2_CATLINK;
$param['caticon'] = OTHERNEWS2_CATICON;



$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class IN (".USERCLASS_LIST.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") AND n.news_render_type=3  ORDER BY n.news_datestamp DESC LIMIT 0,".OTHERNEWS2_LIMIT;

	if ($sql->db_Select_gen($query)){

	while ($row = $sql->db_Fetch()) {
		$text .= $ix->parse_newstemplate($row,$OTHERNEWS2_STYLE,$param);
	}
	$ns->tablerender(TD_MENU_L1, $text, 'other_news2');
}

?>