<?php
// search module for articles.

$c = 0;
if($results = $sql -> db_Select("content", "*", "content_type=1 AND content_class!=255 AND (content_heading REGEXP('".$query."') OR content_subheading REGEXP('".$query."') OR content_content REGEXP('".$query."')) ")){
	while(list($content_id, $content_heading, $content_subheading, $content_content, $content_datestamp, $content_author, $content_comment) = $sql -> db_Fetch()){
		$c ++ ;
		$content_heading_ = parsesearch($content_heading, $query);
		if(!$content_heading_){
			$content_heading_ = $content_heading;
		}
		$content_subheading_ = parsesearch($content_subheading, $query);
		if(!$content_subheading_){
			$content_subheading_ = $content_subheading_;
		}
		$content_content_ = parsesearch($content_content, $query);
		$text .= "<br /><form method='post' action='content.php?content.".$content_id."' name='content_".$c."'>
				<input type='hidden' name='highlight_search' value='1'><input type='hidden' name='search_query' value='$query'><img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <b><a href='javascript:this.form.content_".$c.".submit()'>".$content_heading_."</a></b> <br />".$content_subheading_.$content_content_;
	}
}else{
	$text .= LAN_198;
}
?>