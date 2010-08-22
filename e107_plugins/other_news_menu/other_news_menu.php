<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

global $e107cache;

// Load Data
if($cacheData = $e107cache->retrieve("nq_othernews"))
{
	echo $cacheData;
	return;
}


require_once(e_HANDLER."news_class.php");
unset($text);
global $OTHERNEWS_STYLE,$news_cssmode;
$ix = new news;

if(!$OTHERNEWS_STYLE)
{
	$OTHERNEWS_STYLE = "
	<div style='padding:3px;width:100%'>
	<table style='border-bottom:1px solid black;width:100%' cellpadding='0' cellspacing='0'>
	<tr>
	<td style='vertical-align:top;padding:3px;width:20px'>
	{NEWSCATICON}
	</td><td style='text-align:left;padding:3px;vertical-align:top'>
	{NEWSTITLELINK}
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
$GLOBALS['NEWS_CSSMODE'] = "othernews";

$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
LEFT JOIN #user AS u ON n.news_author = u.user_id
LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
WHERE n.news_class IN (".USERCLASS_LIST.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") AND n.news_render_type=2  ORDER BY n.news_datestamp DESC LIMIT 0,".OTHERNEWS_LIMIT;

if ($sql->db_Select_gen($query))
{
	$text = "<table style='width:100%' cellpadding='0' cellspacing='".OTHERNEWS_SPACING."'>";
	$t = 0;
	$wid = floor(100/$nbr_cols);

	while ($row = $sql->db_Fetch())
	{
		$text .= ($t % $nbr_cols == 0) ? "<tr>" : "";
		$text .= "\n<td class='othernews_cell' style='$style ; width:$wid%;'>\n";
		$text .= $ix->render_newsitem($row, 'return', '', $OTHERNEWS_STYLE, $param);

		$text .= "\n</td>\n";
		if (($t+1) % $nbr_cols == 0) {
			$text .= "</tr>";
			$t++;
		}
		else {
			$t++;
		}
	}


	while ($t % $nbr_cols != 0)
	{
		$text .= "<td style='width:$wid'>&nbsp;</td>\n";
		$text .= (($t+1) % $nbr_cols == 0) ? "</tr>" : "";
		$t++;
	}
	$text .= "</table>";


	// Save Data
	ob_start();

	$ns->tablerender(TD_MENU_L1, $text, 'other_news');

	$cache_data = ob_get_flush();
	$e107cache->set("nq_othernews", $cache_data);
}

?>