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
|     $Revision: 1.5 $
|     $Date: 2005-02-10 06:14:51 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
unset($text);
global $OTHERNEWS_STYLE;

$numb = ($OTHERNEWS_LIMIT) ? $OTHERNEWS_LIMIT : 10;

if(!$OTHERNEWS_STYLE){
   	$OTHERNEWS_STYLE = "<img src='".THEME."images/bullet2.gif' alt='bullet' />&nbsp;{OTHERNEWS_LINK}<br />";
}

if ($sql->db_Select("news", "*", "news_render_type=2 ORDER BY news_datestamp DESC LIMIT 0,$numb")) {
	unset($text);
	while ($row = $sql->db_Fetch()) {
		extract($row);
		if (check_class($news_class)) {

            $search[0] = "/\{OTHERNEWS_LINK\}(.*?)/si";
			$replace[0] = ($news_id) ? "<a href='".e_BASE."news.php?item.$news_id'>$news_title</a>" : "";

            $search[1] = "/\{OTHERNEWS_SUMMARY\}(.*?)/si";
			$replace[1] =  ($news_summary) ? $tp->toHTML($news_summary) : "" ;

			$search[2] = "/\{OTHERNEWS_THUMBNAIL\}(.*?)/si";
			$replace[2] = ($news_thumb) ? "<a href='news.php?extend.".$news_id."'><img src='".e_IMAGE."newspost_images/".$news_thumb."' alt='' style='border:0px' /></a>" : "";



            $text .= preg_replace($search, $replace,$OTHERNEWS_STYLE);
		  //	$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."news.php?item.$news_id'>$news_title</a><br />\n";
		}
	}
	$ns->tablerender(TD_MENU_L1, $text, 'other_news2');
}

?>