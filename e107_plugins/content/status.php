<?php

if(!is_object($sql2)){ $sql2 = new db; }
$maincat = $sql -> db_Select("pcontent", "content_id, content_heading", "content_parent = '0' ORDER BY content_heading");
while($row = $sql -> db_Fetch()){
	extract($row);
		$count = $sql2 -> db_Count("pcontent", "(*)", "WHERE LEFT(content_parent,1) = '".$content_id."' AND content_refer != 'sa' ");
		$text .= "<div style='padding-bottom: 2px;'>".E_16_ARTICLE." ".$content_heading.": ".$count."</div>";
}

?>