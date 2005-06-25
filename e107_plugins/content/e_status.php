<?php
$sql2 = new db;
$total = $sql -> db_Count("pcontent", "(*)", "WHERE LEFT(content_parent,1) != '0' AND content_refer != 'sa'");
if($total == 0){
$text .= "<div style='padding-bottom: 2px;'><img src='".e_PLUGIN."content/images/content_16.png' style='width: 16px; height: 16px; vertical-align: bottom' alt='' /> Content: ".$total."</div>";
}else{
$text .= "<div style='padding-bottom: 2px;'><a style='cursor: pointer; cursor: hand' onclick=\"expandit('content');\"><img src='".e_PLUGIN."content/images/content_16.png' style='width: 16px; height: 16px; vertical-align: bottom' alt='' /> Content: ".$total."</a></div>";
}
$maincat = $sql -> db_Select("pcontent", "content_id, content_heading", "LEFT(content_parent,1) = '0' ORDER BY content_heading");
$text .= "<div id='content' style='display: none;'>";
while($row = $sql -> db_Fetch()){
	$count = $sql2 -> db_Count("pcontent", "(*)", "WHERE content_parent = '".$row['content_id']."' AND content_refer != 'sa' ");
	$text .= "<div style='padding-bottom: 2px;'><img src='".e_PLUGIN."content/images/content_16.png' style='width: 16px; height: 16px; vertical-align: bottom' alt='' /> ".$row['content_heading'].": ".$count."</div>";
}
$text .= "</div>";
?>