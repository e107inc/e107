<?php
$sql2 = new db;
$count = $sql2 -> db_Count("links_page", "(*)");
$text .= "<div style='padding-bottom: 2px;'><img src='".e_PLUGIN."content/images/content_16.png' style='width: 16px; height: 16px; vertical-align: bottom' alt='' /> Links: ".$count."</div>";
?>