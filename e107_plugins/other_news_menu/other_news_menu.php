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
|     $Revision: 1.11 $
|     $Date: 2005-02-28 20:04:07 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
if(!defined("e_HANDLER")){ exit; }
// require_once("othernews_parser.php");
require_once(e_HANDLER."news_class.php");
unset($text);
global $OTHERNEWS_STYLE;
$ix = new news;

 if(!$OTHERNEWS_STYLE){
$OTHERNEWS_STYLE = "
			<div style='padding:3px;width:100%'>
			<table style='border-bottom:1px solid black;width:100%' cellpadding='0' cellspacing='0'>
			<tr>
			<td style='vertical-align:top;padding:3px;width:20px'>
			{NEWSCATICON}
			</td><td style='text-align:left;padding:3px'>
			{NEWSTITLELINK}
			<br />
			{NEWSSUMMARY}
			<span class='smalltext'>
			{NEWSDATE}
			{NEWSCOMMENTS}
			</span>
			</td><td style='width:55px'>
			{NEWSTHUMBNAIL}
			</td></tr></table>
			</div>\n";
 }
if(!defined("OTHERNEWS_LIMIT")){
	define("OTHERNEWS_LIMIT",10);
}

if(!defined("OTHERNEWS_ITEMLINK")){
	define("OTHERNEWS_ITEMLINK","");
}

if(!defined("OTHERNEWS_CATLINK")){
	define("OTHERNEWS_CATLINK","");
}
if(!defined("OTHERNEWS_THUMB")){
	define("OTHERNEWS_THUMB","border:0px");
}
if(!defined("OTHERNEWS_CATICON")){
	define("OTHERNEWS_CATICON","border:0px");
}

if(!defined("OTHERNEWS_COLS")){
	define("OTHERNEWS_COLS","1");
}

if(!defined("OTHERNEWS_CELL")){
	define("OTHERNEWS_CELL","padding:0px;vertical-align:top");
}

if(!defined("OTHERNEWS_SPACING")){
	define("OTHERNEWS_SPACING","0");
}

$param['itemlink'] = OTHERNEWS_ITEMLINK;
$param['thumbnail'] = OTHERNEWS_THUMB;
$param['catlink'] = OTHERNEWS_CATLINK;
$param['caticon'] = OTHERNEWS_CATICON;

$style = OTHERNEWS_CELL;
$nbr_cols = OTHERNEWS_COLS;


	$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class IN (".USERCLASS_LIST.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") AND n.news_render_type=2  ORDER BY n.news_datestamp DESC LIMIT 0,".OTHERNEWS_LIMIT;

	if ($sql->db_Select_gen($query)){
    $text = "<table style='width:100%' cellpadding='0' cellspacing='".OTHERNEWS2_SPACING."'>";
    $t = 0;
	$wid = floor(100/$nbr_cols);
		while ($row = $sql->db_Fetch()) {
		$text .= ($t % $nbr_cols == 0) ? "<tr>" : "";
		$text .= "\n<td style='$style ; width:$wid%;'>\n";
		$text .= $ix->parse_newstemplate($row,$OTHERNEWS_STYLE,$param);

    	$text .= "\n</td>\n";
		if (($t+1) % $nbr_cols == 0) {
			$text .= "</tr>";
			$t++;
		} else {
			$t++;
		}
	}


    while ($t % $nbr_cols != 0){
		$text .= "<td style='width:$wid'>&nbsp;</td>\n";
		$text .= (($t+1) % nbr_cols == 0) ? "</tr>" : "";
		$t++;
			
		}
	$text .= "</table>";

		$ns->tablerender(TD_MENU_L1, $text, 'other_news');
	}

?>