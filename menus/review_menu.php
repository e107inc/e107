<?php
if($sql -> db_Select("content", "*", "content_type='3' ORDER BY content_datestamp DESC")){
	$text = "";
	while(list($content_id, $content_heading, $content_subheading, $content_content, $content_page, $content_datestamp, $content_author, $content_comment) = $sql-> db_Fetch()){
		$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> 
<a href=\"article.php?".$content_id.".0"."\">".$content_heading."</a>
<br />";
	}
	$ns -> tablerender(LAN_190, $text);
}
?>