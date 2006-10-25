<?php
if (!defined('e107_INIT')) { exit; }

$reported_posts = $sql->db_Count('generic', '(*)', "WHERE gen_type='reported_post' OR gen_type='Reported Forum Post'");
$text .= "<div style='padding-bottom: 2px;'>
<img src='".e_PLUGIN."forum/images/forums_16.png' style='width: 16px; height: 16px; vertical-align: bottom' alt='' /> ";

if ($reported_posts) {
	$text .= "<a href='".e_PLUGIN."forum/forum_admin.php?sr'>".ADLAN_LAT_6.": ".$reported_posts."</a>";
} else {
	$text .= ADLAN_LAT_6.": ".$reported_posts;
}

$text .= '</div>';
?>
