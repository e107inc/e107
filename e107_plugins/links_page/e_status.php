<?php
if (file_exists(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php")) {
	include_once(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php");
	} else {
	include_once(e_PLUGIN."links_page/languages/English.php");
}
$sql2 = new db;
$count = $sql2 -> db_Count("links_page", "(*)");
$text .= "<div style='padding-bottom: 2px;'><img src='".e_PLUGIN."links_page/images/linkspage_16.png' style='width: 16px; height: 16px; vertical-align: bottom' alt='' /> ".LCLAN_ADMIN_14.": ".$count."</div>";
?>