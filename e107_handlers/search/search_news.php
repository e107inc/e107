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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/search/search_news.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:40 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
$c = 0;
$results = $sql -> db_Select("news", "*", "(news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().")  AND (news_title REGEXP('".$query."') OR news_body REGEXP('".$query."') OR news_extended REGEXP('".$query."')) ORDER BY news_id DESC ");
        while($row = $sql -> db_Fetch()){
                extract($row);$c ++;
                if(check_class($news_class)){
                        $link = ($news_allow_comments ? "news.php?item.$news_id" : "comment.php?comment.news.$news_id");
                        $datestamp = $con -> convert_date($news_datestamp, "long");
                        if(eregi($query, $news_title)){
                                $resmain = parsesearch($news_title, $query);
                                $text .= "<form method='post' action='$link' id='news_title_".$c."'>
                                <input type='hidden' name='highlight_search' value='1' /><input type='hidden' name='search_query' value='$query' /><img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='javascript: document.getElementById(\"news_title_".$c."\").submit();'>".$resmain."</a></b></form><br /><span class='smalltext'>".LAN_SEARCH_3.$datestamp." - ".LAN_SEARCH_4."</span><br /><br />";
                        }else if(eregi($query, $news_body)){
                                $resmain = parsesearch($news_body, $query);
                                $text .= "<form method='post' action='$link' id='news_news_".$c."'>
                                <input type='hidden' name='highlight_search' value='1' /><input type='hidden' name='search_query' value='$query' /><img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a  href='javascript:document.getElementById(\"news_news_".$c."\").submit()'>".$news_title."</a></b></form><br /><span class='smalltext'>".LAN_SEARCH_3.$datestamp." - ".LAN_SEARCH_5."</span><br />".$resmain."<br /><br />";
                        }else if(eregi($query, $news_extended)){
                                $resmain = parsesearch($news_extended, $query);
                                $text .= "<form method='post' action='$link' id='news_extended_".$c."'>
                                <input type='hidden' name='highlight_search' value='1' /><input type='hidden' name='search_query' value='$query' /><img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='javascript:document.getElementById(\"news_extended_".$c."\").submit()'>".$news_title."</a></b></form><br /><span class='smalltext'>".LAN_SEARCH_3.$datestamp." - ".LAN_SEARCH_6."</span><br />".$resmain."<br /><br />";
                        }
                }else{
                        $results = $results -1;
                }
        }
if(!$results){
        $text .= LAN_198;
}
?>