<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/newforumposts_main/newforumposts_main.php,v $
|     $Revision: 1.10 $
|     $Date: 2005-02-28 20:03:51 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
if(!defined("e_HANDLER")){ exit; }
require_once(e_HANDLER."userclass_class.php");
$query = ($pref['nfp_posts'] ? "thread_lastpost" : "thread_datestamp");
$lan_file = e_PLUGIN."newforumposts_main/languages/".e_LANGUAGE.".php";
$path = e_PLUGIN."forum/";
require_once((file_exists($lan_file) ? $lan_file : e_PLUGIN."newforumposts_main/languages/English.php"));
// get template ...
	
if (file_exists(THEME."newforumpost.php")) {
	require_once(THEME."newforumpost.php");
}
else if(!$NEWFORUMPOSTSTYLE_HEADER) {
	// no template found - use default ...
	$NEWFORUMPOSTSTYLE_HEADER = "
		<!-- newforumposts -->
		<div style='text-align:center'>\n<table style='width:auto' class='fborder'>
		<tr>
		<td style='width:5%' class='forumheader'>&nbsp;</td>
		<td style='width:45%' class='forumheader'>".LAN_1."</td>
		<td style='width:15%; text-align:center' class='forumheader'>".LAN_2."</td>
		<td style='width:5%; text-align:center' class='forumheader'>".LAN_3."</td>
		<td style='width:5%; text-align:center' class='forumheader'>".LAN_4."</td>
		<td style='width:25%; text-align:center' class='forumheader'>".LAN_5."</td>
		</tr>\n";
	 
	$NEWFORUMPOSTSTYLE_MAIN = "
		<tr>
		<td style='width:5%; text-align:center' class='forumheader3'>{ICON}</td>
		<td style='width:45%' class='forumheader3'><b>{THREAD}</b> <span class='smalltext'>({FORUM})</span></td>
		<td style='width:15%; text-align:center' class='forumheader3'>{POSTER}</td>
		<td style='width:5%; text-align:center' class='forumheader3'>{VIEWS}</td>
		<td style='width:5%; text-align:center' class='forumheader3'>{REPLIES}</td>
		<td style='width:25%; text-align:center' class='forumheader3'>{LASTPOST}<br /><span class='smalltext'>{LASTPOSTDATE}</span></td>
		</tr>\n";
	 
	$NEWFORUMPOSTSTYLE_FOOTER = "<tr>\n<td colspan='6' style='text-align:center' class='forumheader2'>
		<span class='smalltext'>".LAN_6.": <b>{TOTAL_TOPICS}</b> | ".LAN_4.": <b>{TOTAL_REPLIES}</b> | ".LAN_3.": <b>{TOTAL_VIEWS}</b></span>\n</td>\n</tr>\n</table>\n</div>";
	 
}

$results = $sql->db_Select_gen("
SELECT t.thread_id, t.thread_name, t.thread_datestamp, t.thread_user, t.thread_views, t.thread_lastpost, t.thread_anon, t.thread_lastuser, t.thread_total_replies, f.forum_id, f.forum_name, f.forum_class, u.user_name
FROM #forum_t AS t, #forum AS f
LEFT JOIN #user AS u ON t.thread_user = u.user_id
WHERE f.forum_id = t.thread_forum_id AND t.thread_parent =0 AND f.forum_class IN (".USERCLASS_LIST.") 
ORDER BY t.$query DESC LIMIT 0, ".$pref['nfp_amount']);

$forumArray = $sql->db_getList();
	
if (!is_object($gen)) {
	$gen = new convert;
}
	
$ICON = "<img src='".e_PLUGIN."forum/images/new_small.png' alt='' />";
$TOTAL_TOPICS = $sql->db_Count("forum_t", "(*)", " WHERE thread_parent='0' ");
$TOTAL_REPLIES = $sql->db_Count("forum_t", "(*)", " WHERE thread_parent!='0' ");
$TOTAL_VIEWS = $sql->db_Count("SELECT sum(thread_views) FROM ".MPREFIX."forum_t", "generic");
	
$text = preg_replace("/\{(.*?)\}/e", '$\1', $NEWFORUMPOSTSTYLE_HEADER);
foreach($forumArray as $forumInfo) {
	 
	 
	//echo "<pre>"; print_r($forumInfo); echo "</pre>";
	 
	extract($forumInfo);
	 
	$r_id = substr($thread_lastuser, 0, strpos($thread_lastuser, "."));
	$r_name = substr($thread_lastuser, (strpos($thread_lastuser, ".")+1));
	if (strstr($thread_lastuser, chr(1))) {
		$tmp = explode(chr(1), $thread_lastuser);
		$r_name = $tmp[0];
	}
	$r_datestamp = $gen->convert_date($thread_lastpost, "forum");
	 
	if ($thread_anon) {
		$tmp = explode(chr(1), $thread_anon);
		$thread_user = $tmp[0];
		$thread_user_ip = $tmp[1];
	}

	$THREAD = "<a href='".$path."forum_viewtopic.php?$thread_id'>$thread_name</a>";
	$FORUM = "<a href='".$path."forum_viewforum.php?$forum_id'>$forum_name</a>";
	$POSTER = ($thread_anon ? $thread_user : "<a href='".e_BASE."user.php?id.$thread_user'>$user_name</a>");
	 
	$VIEWS = $thread_views;
	$REPLIES = $thread_total_replies;
	$LASTPOST = ($thread_total_replies ? ($r_id ? "<a href='".e_BASE."user.php?id.$r_id'>$r_name</a>" : $r_name) : " - ");
	$LASTPOSTDATE = ($thread_total_replies ? "<span class='smalltext'>$r_datestamp</span>" : "");
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $NEWFORUMPOSTSTYLE_MAIN);
	 
}
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $NEWFORUMPOSTSTYLE_FOOTER);
	
$text = ($pref['nfp_layer'] ? "<div style='border : 0; padding : 4px; width : auto; height : ".$pref['nfp_layer_height']."px; overflow : auto; '>".$text."</div>" : $text);
if ($results) {
	$ns->tablerender($pref['nfp_caption'], $text, "nfp");
}

?>