<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/alt_news.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
unset($text);


if($sql -> db_Select("news", "*", "news_render_type=2 ORDER BY news_datestamp DESC LIMIT 0,10")){
	unset($text);
	while($row = $sql -> db_Fetch()){
		extract($row);
		if(check_class($news_class)){
			$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."news.php?item.$news_id'>$news_title</a><br />\n";
		}
	}
	$ns -> tablerender(TD_MENU_L1, $text);
}

?>