<?php
// search module for Links.

if($results = $sql -> db_Select("links", "*", "link_name LIKE('%".$query."%') OR link_description LIKE('%".$query."%') ")){
	while(list($link_id, $link_name, $link_url, $link_desciption, $link_button, $link_category, $link_refer) = $sql -> db_Fetch()){
		$link_name_ = parsesearch($link_name, $query);
		if(!$link_name_){
			$link_name_ = $link_name;
		}
		$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"".$link_url."\">".$link_name."</a><br />";
	}
}else{
	$text .= LAN_198;
}
?>