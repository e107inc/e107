<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/top.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
require_once(e_HANDLER."level_handler.php");
require_once(HEADERF);
define("IMAGE_rank_main_admin_image", ($pref['rank_main_admin_image'] && file_exists(e_IMAGE."forum/".$pref['rank_main_admin_image']) ? "<img src='".e_IMAGE."forum/".$pref['rank_main_admin_image']."' alt='' />" : "<img src='".e_IMAGE."forum/main_admin.png' alt='' />"));
define("IMAGE_rank_admin_image", ($pref['rank_admin_image'] && file_exists(e_IMAGE."forum/".$pref['rank_admin_image']) ? "<img src='".e_IMAGE."forum/".$pref['rank_admin_image']."' alt='' />" : "<img src='".e_IMAGE."forum/admin.png' alt='' />"));
define("IMAGE_rank_moderator_image", ($pref['rank_moderator_image'] && file_exists(e_IMAGE."forum/".$pref['rank_moderator_image']) ? "<img src='".e_IMAGE."forum/".$pref['rank_moderator_image']."' alt='' />" : "<img src='".e_IMAGE."forum/moderator.png' alt='' />"));

$top_forum_posters = $sql -> db_Select("user", "*", "ORDER BY user_forums DESC LIMIT 0, 10", "no_where");
$text = "
<div style='text-align:center'>
<table style='width:95%' class='fborder'>
<tr>
<td style='width:10%; text-align:center' class='forumheader3'>&nbsp;</td>
<td style='width:50%' class='forumheader3'>".TOP_LAN_1."</td>
<td style='width:10%; text-align:center' class='forumheader3'>".TOP_LAN_2."</td>
<td style='width:30%; text-align:center' class='forumheader3'>".TOP_LAN_6."</td>
</tr>\n";
$counter=1;
while($row = $sql -> db_Fetch()){
	extract($row);
	$ldata = get_level($user_id, $user_forums, $user_comments, $user_chats, $user_visits, $user_join, $user_admin, $user_perms, $pref);
	$text .= "<tr>
	<td style='width:10%; text-align:center' class='forumheader3'>$counter</td>
	<td style='width:50%' class='forumheader3'><a href='".e_BASE."user.php?id.$user_id'>$user_name</a></td>
	<td style='width:10%; text-align:center' class='forumheader3'>$user_forums</td>
	<td style='width:30%; text-align:center' class='forumheader3'>".(strstr($ldata[0], "LAN") ? $ldata[1] : $ldata[0])."</td>
	</tr>";
	$counter++;
}
$text .= "</table>\n</div>";
$ns -> tablerender(TOP_LAN_0, $text);


$top_forum_posters = $sql -> db_Select("user", "*", "ORDER BY user_comments DESC LIMIT 0, 10", "no_where");
$text = "
<div style='text-align:center'>
<table style='width:95%' class='fborder'>
<tr>
<td style='width:10%; text-align:center' class='forumheader3'>&nbsp;</td>
<td style='width:50%' class='forumheader3'>".TOP_LAN_1."</td>
<td style='width:10%; text-align:center' class='forumheader3'>".TOP_LAN_4."</td>
<td style='width:30%; text-align:center' class='forumheader3'>".TOP_LAN_6."</td>
</tr>\n";
$counter=1;
while($row = $sql -> db_Fetch()){
	extract($row);
	$ldata = get_level($user_id, $user_forums, $user_comments, $user_chats, $user_visits, $user_join, $user_admin, $user_perms, $pref);
	$text .= "<tr>
	<td style='width:10%; text-align:center' class='forumheader3'>$counter</td>
	<td style='width:50%' class='forumheader3'><a href='".e_BASE."user.php?id.$user_id'>$user_name</a></td>
	<td style='width:10%; text-align:center' class='forumheader3'>$user_comments</td>
	<td style='width:30%; text-align:center' class='forumheader3'>".(strstr($ldata[0], "LAN") ? $ldata[1] : $ldata[0])."</td>
	</tr>";
	$counter++;
}
$text .= "</table>\n</div>";
$ns -> tablerender(TOP_LAN_3, $text);

$top_forum_posters = $sql -> db_Select("user", "*", "ORDER BY user_chats DESC LIMIT 0, 10", "no_where");
$text = "
<div style='text-align:center'>
<table style='width:95%' class='fborder'>
<tr>
<td style='width:10%; text-align:center' class='forumheader3'>&nbsp;</td>
<td style='width:20%' class='forumheader3'>".TOP_LAN_1."</td>
<td style='width:10%; text-align:center' class='forumheader3'>".TOP_LAN_2."</td>
<td style='width:30%; text-align:center' class='forumheader3'>".TOP_LAN_6."</td>
</tr>\n";
$counter=1;
while($row = $sql -> db_Fetch()){
	extract($row);
	$ldata = get_level($user_id, $user_forums, $user_comments, $user_chats, $user_visits, $user_join, $user_admin, $user_perms, $pref);
	$text .= "<tr>
	<td style='width:10%; text-align:center' class='forumheader3'>$counter</td>
	<td style='width:50%' class='forumheader3'><a href='".e_BASE."user.php?id.$user_id'>$user_name</a></td>
	<td style='width:10%; text-align:center' class='forumheader3'>$user_chats</td>
	<td style='width:30%; text-align:center' class='forumheader3'>".(strstr($ldata[0], "LAN") ? $ldata[1] : $ldata[0])."</td>
	</tr>";
	$counter++;
}
$text .= "</table>\n</div>";
$ns -> tablerender(TOP_LAN_5, $text);

require_once(FOOTERF);
?>