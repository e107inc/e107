<?php
// search module for news.

$results = $sql -> db_Select("news", "*", "(news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().")  AND (news_title REGEXP('".$query."') OR news_body REGEXP('".$query."') OR news_extended REGEXP('".$query."')) ORDER BY news_id DESC ");
	while($row = $sql -> db_Fetch()){
		extract($row);
		if(check_class($news_class)){
			$link = ($news_allow_comments ? "news.php?item.$news_id" : "comment.php?comment.news.$news_id");
			$datestamp = $con -> convert_date($news_datestamp, "long");
			if(eregi($query, $news_title)){
				$resmain = parsesearch($news_title, $query);
				$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='$link'>".$resmain."</a></b><br /><span class='smalltext'>item posted on ".$datestamp." - Match found in news title</span><br /><br />";
			}else if(eregi($query, $news_body)){
				$resmain = parsesearch($news_body, $query);
				$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='$link'>".$news_title."</a></b><br /><span class='smalltext'>item posted on ".$datestamp." - Match found in news text</span><br />".$resmain."<br /><br />";
			}else if(eregi($query, $news_body)){
				$resmain = parsesearch($news_extended, $query);
				$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='$link'>".$news_title."</a></b><br /><span class='smalltext'>item posted on ".$datestamp." - Match found in extended news text</span><br />".$resmain."<br /><br />";
			}
		}else{
			$results = $results -1;
		}
	}
if(!$results){
	$text .= LAN_198;
}
?>