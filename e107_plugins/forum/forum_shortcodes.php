<?php
include_once(e_HANDLER.'shortcode_handler.php');
$forum_shortcodes = e_shortcode::parse_scbatch(__FILE__);
/*
SC_BEGIN TOP
return "<a href='".e_SELF."?".e_QUERY."#top'>".LAN_10."</a>";
SC_END
	
SC_BEGIN JOINED
global $post_info;
global $gen;
if ($post_info['user_id']) {
return LAN_06.': '.$gen->convert_date($post_info['user_join'], 'forum').'<br />';
}
SC_END
	
SC_BEGIN LOCATION
global $post_info;
return ($post_info["user_location"] ? LAN_07.": ".$post_info["user_location"]."<br />" : "");
SC_END
	
SC_BEGIN THREADDATESTAMP
global $post_info;
global $gen;
global $thread_id;
return "<a id='{$thread_id}'>".IMAGE_post."</a> ".$gen->convert_date($post_info['thread_datestamp'], "forum");
SC_END
	
SC_BEGIN POST
global $post_info;
global $tp;
global $iphost;
$ret = "";
$ret = $tp->toHTML($post_info["thread_thread"], TRUE, "", 'class:'.$post_info["user_class"]);
if (ADMIN && $iphost) {
$ret .= "<br />".$iphost;
}
return $ret;
SC_END
	
SC_BEGIN PRIVMESSAGE
global $post_info;
global $tp;
return $tp->parseTemplate("{SENDPM={$post_info['user_id']}}");
SC_END
	
SC_BEGIN AVATAR
global $post_info;
if ($post_info['user_id']) {
if ($post_info["user_image"]) {
require_once(e_HANDLER."avatar_handler.php");
return "<div class='spacer'><img src='".avatar($post_info['user_image'])."' alt='' /></div><br />";
} else {
return "";
}
} else {
return "<span class='smallblacktext'>".LAN_194."</span>";
}
SC_END
	
SC_BEGIN POSTER
global $post_info;
if (!$post_info["thread_user"]) {
$tmp = explode(chr(1), $post_info['thread_anon']);
return "<b>".$tmp[0]."</b>";
} else {
return "<a href='".e_BASE."user.php?id.".$post_info['user_id']."'><b>".$post_info['user_name']."</b></a>";
}
SC_END
	
SC_BEGIN EMAILING
global $post_info;
global $tp;
return (!$post_info['user_hideemail'] ? $tp->parseTemplate("{EMAILTO={$post_info['user_email']}}") : "");
SC_END
	
	
SC_BEGIN SIGNATURE
global $post_info;
global $tp;
return ($post_info['user_signature'] ? "<br /><hr style='width:15%; text-align:left'><span class='smalltext'>".$tp->toHTML($post_info['user_signature'],TRUE) : "");
SC_END
	
SC_BEGIN PROFILEIMG
global $post_info;
global $tp;
if (USER) {
return $tp->parseTemplate("{PROFILE={$post_info['user_id']}}");
} else {
return "";
}
SC_END
	
SC_BEGIN POSTS
global $post_info;
if ($post_info['user_id']) {
return LAN_67.": ".$post_info['user_forums']."<br />";
}
SC_END
	
SC_BEGIN VISITS
global $post_info;
if ($post_info['user_id']) {
return LAN_09.": ".$post_info['user_visits']."<br />";
}
SC_END
	
SC_BEGIN EDITIMG
global $post_info;
global $thread_info;
global $thread_id;
if ($post_info['user_id'] != '0' && $post_info['user_name'] === USERNAME && $thread_info['head']['thread_active']) {
return "<a href='forum_post.php?edit.".$post_info['thread_id']."'>".IMAGE_edit."</a> ";
} else {
return "";
}
SC_END
	
SC_BEGIN CUSTOMTITLE
global $post_info;
global $tp;
if ($post_info['user_customtitle']) {
return $tp->toHTML($post_info['user_customtitle'])."<br />";
}
SC_END
	
SC_BEGIN WEBSITE
global $post_info;
global $tp;
if ($post_info['user_homepage']) {
return LAN_08.": ".$post_info['user_homeapage']."<br />";
}
SC_END
	
SC_BEGIN WEBSITEIMG
global $post_info;
if ($post_info['user_homepage'] && $post_info['user_homepage'] != "http://") {
return "<a href='{$post_info['user_homepage']}'>".IMAGE_website."</a>";
}
SC_END
	
SC_BEGIN QUOTEIMG
global $thread_info;
global $post_info;
if ($thread_info["head"]["thread_active"]) {
return "<a href='".e_PLUGIN."forum/forum_post.php?quote.{$post_info['thread_id']}'>".IMAGE_quote."</a>";
}
SC_END
	
SC_BEGIN REPORTIMG
global $post_info;
global $from;
if (USER) {
return "<a href='".e_PLUGIN."forum/forum_viewtopic.php?{$post_info['thread_id']}.{$from}.report'>".IMAGE_report."</a> ";
}
SC_END
	
SC_BEGIN RPG
global $post_info;
return rpg($post_info['user_join'],$post_info['user_forums']);
SC_END
	
SC_BEGIN MEMBERID
global $post_info;
global $ldata;
global $pref;
if ($post_info['anon']) {
return "";
}
if (!array_key_exists($post_info['user_id'],$ldata)) {
$ldata[$post_info['user_id']] = get_level($post_info['user_id'], $post_info['user_forums'], $post_info['user_comments'], $post_info['user_chats'], $post_info['user_visits'], $post_info['user_join'], $post_info['user_admin'], $post_info['user_perms'], $pref);
}
return $ldata[$post_info['user_id']][0];
SC_END
	
SC_BEGIN LEVEL
global $post_info;
global $ldata;
global $pref;
if ($post_info['anon']) {
return "";
}
if (!array_key_exists($post_info['user_id'],$ldata)) {
$ldata[$post_info['user_id']] = get_level($post_info['user_id'], $post_info['user_forums'], $post_info['user_comments'], $post_info['user_chats'], $post_info['user_visits'], $post_info['user_join'], $post_info['user_admin'], $post_info['user_perms'], $pref);
}
return $ldata[$post_info['user_id']][1];
SC_END
	
SC_BEGIN MODOPTIONS
if (MODERATOR) {
return showmodoptions();
}
SC_END
	
SC_BEGIN LASTEDIT
global $post_info;
global $gen;
if ($post_info['thread_edit_datestamp']) {
return $gen->convert_date($post_info['thread_edit_datestamp'],'forum');
}
SC_END
	
*/
?>