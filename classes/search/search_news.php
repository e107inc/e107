<?php
// search module for news.

if($results = $sql -> db_Select("news", "*", "news_title REGEXP('".$query."') OR news_body REGEXP('".$query."') OR news_extended REGEXP('".$query."') ORDER BY news_id DESC ")){
	while(list($news_id, $news_title, $news_body, $news_extended, $news_datestamp) = $sql -> db_Fetch()){
		$datestamp = $con -> convert_date($news_datestamp, "long");
		if(eregi($query, $news_title)){
			$resmain = parsesearch($news_title, $query);
			$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <b><a href=\"comment.php?".$news_id."\">".$resmain."</a></b><br /><span class=\"smalltext\">item posted on ".$datestamp." - Match found in news title</span><br /><br />";
		}else if(eregi($query, $news_body)){
			$resmain = parsesearch($news_body, $query);
			$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <b><a href=\"comment.php?".$news_id."\">".$news_title."</a></b><br /><span class=\"smalltext\">item posted on ".$datestamp." - Match found in news text</span><br />".$resmain."<br /><br />";
		}else if(eregi($query, $news_body)){
			$resmain = parsesearch($news_extended, $query);
			$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <b><a href=\"comment.php?".$news_id."\">".$news_title."</a></b><br /><span class=\"smalltext\">item posted on ".$datestamp." - Match found in extended news text</span><br />".$resmain."<br /><br />";
		}
	}
}else{
	$text .= "No matches.";
}
?>