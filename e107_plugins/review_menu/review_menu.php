<?php
$text = ($menu_pref['reviews_mainlink'] ? "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."article.php?0.list.3'> ".$menu_pref['reviews_mainlink']."</a><br/>" : "");

if($sql -> db_Select("content", "*", "content_type='3' ORDER BY content_datestamp DESC limit 0, ".$menu_pref['reviews_display'])){
	while(list($content_id, $content_heading, $content_subheading, $content_content, $content_page, $content_datestamp, $content_author, $content_comment) = $sql-> db_Fetch()){
		$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> 
<a href=\"".e_BASE."article.php?".$content_id.".0"."\">".$content_heading."</a>
<br />";
	}
	$ns -> tablerender(LAN_190, $text);
}
?>