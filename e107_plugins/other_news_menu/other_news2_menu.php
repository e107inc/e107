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
|     $Revision: 1.1 $
|     $Date: 2005-02-10 06:14:51 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
unset($text);
global $tp,$OTHERNEWS_STYLE, $OTHERNEWS2_STYLE, $OTHERNEWS2_LIMIT;

$numb = ($OTHERNEWS2_LIMIT) ? $OTHERNEWS2_LIMIT : 5;

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
	$sql->db_Select("news_category");
	while ($row = $sql->db_Fetch()) {

    $category_name[$row['category_id']] = $row['category_name'];
    $category_icon[$row['category_id']] = $row['category_icon'];
	}

if ($sql->db_Select("news", "*", "news_render_type=3 ORDER BY news_datestamp DESC LIMIT 0,$numb")) {
	unset($text);
	while ($row = $sql->db_Fetch()) {
		extract($row);
		if (check_class($news_class)) {

            $search[0] = "/\{OTHERNEWS_LINK\}(.*?)/si";
			$replace[0] = ($news_id) ? "<a class='othernews_link' href='".e_BASE."news.php?item.$news_id'>$news_title</a>" : "";

            $search[1] = "/\{OTHERNEWS_SUMMARY\}(.*?)/si";
			$replace[1] =  ($news_summary) ? $tp->toHTML($news_summary) : "" ;

			$search[2] = "/\{OTHERNEWS_THUMBNAIL\}(.*?)/si";
			$replace[2] = ($news_thumb) ? "<a href='news.php?extend.".$news_id."'><img src='".e_IMAGE."newspost_images/".$news_thumb."' alt='' style='border:0px' /></a>" : "";

            $search[3] = "/\{OTHERNEWS_CATEGORY\}(.*?)/si";
			$replace[3] = "<a style='color:white;text-decoration:none' href='news.php?cat.".$news_category."'>".$category_name[$news_category]."</a>" ;


            $text .= preg_replace($search, $replace,$OTHERNEWS2_STYLE);
		  //	$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."news.php?item.$news_id'>$news_title</a><br />\n";
		}
	}
	$ns->tablerender(TD_MENU_L1, $text, 'other_news2');
}

?>