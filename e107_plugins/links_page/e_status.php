<?php
if (!defined('e107_INIT')) { exit; }
include_lan(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php");

$count = $sql -> db_Count("links_page", "(*)");
$text .= "<div style='padding-bottom: 2px;'><img src='".e_PLUGIN."links_page/images/linkspage_16.png' style='width: 16px; height: 16px; vertical-align: bottom' alt='' /> ".LCLAN_ADMIN_14.": ".$count."</div>";
?>