<?php
$sql2 = new db;
$maincat = $sql -> db_Select("pcontent", "content_id, content_heading", "content_parent = '0' ORDER BY content_heading");
while($row = $sql -> db_Fetch()){
	extract($row);
		$count = $sql2 -> db_Count("pcontent", "(*)", "WHERE LEFT(content_parent,".strlen($content_id).") = '".$content_id."' AND content_refer != 'sa' ");
		$text .= "<div style='padding-bottom: 2px;'><img src='".e_PLUGIN."content/images/articles_16.png' style='width: 16px; height: 16px; vertical-align: bottom' /> ".$content_heading.": ".$count."</div>";
}

?>