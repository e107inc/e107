<?php
// search module for news.
$c = 0;
$results = $sql -> db_Select("news", "*", "(news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().")  AND (news_title REGEXP('".$query."') OR news_body REGEXP('".$query."') OR news_extended REGEXP('".$query."')) ORDER BY news_id DESC ");
	while($row = $sql -> db_Fetch()){
		extract($row);$c ++;
		if(check_class($news_class)){
			$link = ($news_allow_comments ? "news.php?item.$news_id" : "comment.php?comment.news.$news_id");
			$datestamp = $con -> convert_date($news_datestamp, "long");
			if(eregi($query, $news_title)){
				$resmain = parsesearch($news_title, $query);
				$text .= "<form method='post' action='$link' name='news_title_".$c."'>
				<input type='hidden' name='highlight_search' value='1'><input type='hidden' name='search_query' value='$query'><img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='javascript:this.news_title_".$c.".submit()'>".$resmain."</a></b></form><br /><span class='smalltext'>item posted on ".$datestamp." - Match found in news title</span><br /><br />";
			}else if(eregi($query, $news_body)){
				$resmain = parsesearch($news_body, $query);
				$text .= "<form method='post' action='$link' name='news_news_".$c."'>
				<input type='hidden' name='highlight_search' value='1'><input type='hidden' name='search_query' value='$query'><img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a  href='javascript:this.news_news_".$c.".submit()'>".$news_title."</a></b></form><br /><span class='smalltext'>item posted on ".$datestamp." - Match found in news text</span><br />".$resmain."<br /><br />";
			}else if(eregi($query, $news_extended)){
				$resmain = parsesearch($news_extended, $query);
				$text .= "<form method='post' action='$link' name='news_extended_".$c."'>
				<input type='hidden' name='highlight_search' value='1'><input type='hidden' name='search_query' value='$query'><img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='javascript:this.news_extended_".$c.".submit()'>".$news_title."</a></b></form><br /><span class='smalltext'>item posted on ".$datestamp." - Match found in extended news text</span><br />".$resmain."<br /><br />";
			}
		}else{
			$results = $results -1;
		}
	}
if(!$results){
	$text .= LAN_198;
}
?>