<?php
$forum_posts = $sql -> db_Count("forum_t");
$text .= "<div style='padding-bottom: 2px;'>".E_16_FORUM." ".ADLAN_113.": ".$forum_posts."</div>";
?>