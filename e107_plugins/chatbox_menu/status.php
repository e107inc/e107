<?php
$chatbox_posts = $sql -> db_Count("chatbox");
$text .= "<div style='padding-bottom: 2px;'><img src='".e_PLUGIN."chatbox_menu/images/chatbox_16.png' style='width: 16px; height: 16px; vertical-align: bottom' /> ".ADLAN_115.": ".$chatbox_posts."</div>";
?>