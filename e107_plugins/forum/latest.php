<?php
$reportd_posts = $sql->db_Count('generic', '(*)', "WHERE gen_type='reported_post'");
$text .= "
	<div style='padding-bottom: 2px;'>
	<img src='".e_PLUGIN."forum/images/forum_16.png' style='width: 16px; height: 16px; vertical-align: bottom' />
	";
if ($$reported_posts) {
	$text .= " <a href='".e_PLUGIN."forum/forum_admin.php'>Reported forum posts: $submitted_links</a>";
} else {
	$text .= 'Reported forum posts: '.$reported_posts;
}
$text .= '</div>';
?>