<?php
$maincat = $sql -> db_Select("pcontent", "content_id", "content_parent = '0' ORDER BY content_heading");
while($row = $sql -> db_Fetch()){
	extract($row);
		$count = $sql -> db_Count("pcontent", "(*)", "WHERE LEFT(content_parent,1) = '".$content_id."' AND content_refer != 'sa' ");
		$text .= "<div style='padding-bottom: 2px;'>".$content_heading.": ".$count."</div>";
}

?>