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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/news/other_news2_menu.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

global $e107cache;

// Load Data
if($cacheData = $e107cache->retrieve("nq_othernews2"))
{
	echo $cacheData;
	return;
}


require_once(e_HANDLER."news_class.php");
unset($text);
global $OTHERNEWS2_STYLE;
$ix = new news;

if(!$OTHERNEWS2_STYLE) {
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
//	define("OTHERNEWS2_LIMIT",5);
}

if(!defined("OTHERNEWS2_ITEMLINK")){
	// define("OTHERNEWS2_ITEMLINK","");
}
if(!defined("OTHERNEWS2_CATLINK")){
//	define("OTHERNEWS2_CATLINK","");
}
if(!defined("OTHERNEWS2_CATICON")){
	define("OTHERNEWS2_CATICON","border:0px");
}
if(!defined("OTHERNEWS2_THUMB")){
	define("OTHERNEWS2_THUMB","border:0px");
}

if(!defined("OTHERNEWS2_COLS")){
	// define("OTHERNEWS2_COLS","1");
}

if(!defined("OTHERNEWS2_CELL")){
	// define("OTHERNEWS2_CELL","");
}

if(!defined("OTHERNEWS2_SPACING")){
	// define("OTHERNEWS2_SPACING","0");
}

$param['itemlink'] 		= defset('OTHERNEWS2_ITEMLINK','');
$param['thumbnail'] 	= OTHERNEWS2_THUMB;
$param['catlink'] 		= defset('OTHERNEWS2_CATLINK','');
$param['caticon'] 		= OTHERNEWS2_CATICON;

$style 					= defset('OTHERNEWS2_CELL','padding:0px;vertical-align:top');
$nbr_cols 				= defset('OTHERNEWS2_COLS', 1);

$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
LEFT JOIN #user AS u ON n.news_author = u.user_id
LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
WHERE n.news_class IN (".USERCLASS_LIST.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") 
AND FIND_IN_SET(3, n.news_render_type)  ORDER BY n.news_datestamp DESC LIMIT 0,". defset('OTHERNEWS2_LIMIT',5);

if ($sql->db_Select_gen($query)) 
{
	$text = "";	
	
	if(OTHERNEWS2_COLS !== false)
	{			
		$text = "<table style='width:100%' cellpadding='0' cellspacing='".defset('OTHERNEWS2_SPACING',0)."'>";
		$t = 0;
		$wid = floor(100/$nbr_cols);
		while ($row = $sql->db_Fetch()) 
		{
			$text .= ($t % $nbr_cols == 0) ? "<tr>" : "";
			$text .= "\n<td style='$style ; width:$wid%;'>\n";
	
			$text .= $ix->render_newsitem($row, 'return', '', $OTHERNEWS2_STYLE, $param);
	
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
	}
	else // perfect for divs. 
	{
		while ($row = $sql->db_Fetch()) 
		{
			$text .= $ix->render_newsitem($row, 'return', '', $OTHERNEWS2_STYLE, $param);
		}
	}
		
	

	// Save Data
	ob_start();

	$ns->tablerender(TD_MENU_L2, $text, 'other_news2');

	$cache_data = ob_get_flush();
	$e107cache->set("nq_othernews2", $cache_data);

}

?>