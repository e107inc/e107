<?php
if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$forum_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
/*
SC_BEGIN TOP
return "<a href='".e_SELF."?".e_QUERY."#top' onclick=\"window.scrollTo(0,0);\">".LAN_10."</a>";
SC_END
	
SC_BEGIN JOINED
global $post_info, $gen;
if ($post_info['user_id']) {
return LAN_06.': '.$gen->convert_date($post_info['user_join'], 'forum').'<br />';
}
SC_END
	
SC_BEGIN THREADDATESTAMP
global $post_info, $gen, $thread_id;
return "<a id='post_{$post_info['thread_id']}' href='".e_SELF."?{$post_info['thread_id']}.post'>".IMAGE_post."</a> ".$gen->convert_date($post_info['thread_datestamp'], "forum");
SC_END
	
SC_BEGIN POST
global $post_info, $tp, $iphost;
$ret = "";
$ret = $tp->toHTML($post_info["thread_thread"], TRUE, "USER_BODY", 'class:'.$post_info["user_class"]);
if (ADMIN && $iphost) {
$ret .= "<br />".$iphost;
}
return $ret;
SC_END
	
SC_BEGIN PRIVMESSAGE
global $pref, $post_info, $tp;
if(isset($pref['plug_installed']['pm']) && ($post_info['user_id'] > 0))
{
	return $tp->parseTemplate("{SENDPM={$post_info['user_id']}}");
}
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
	
SC_BEGIN ANON_IP
global $post_info;
//die($post_info['thread_user']);
$x = explode(chr(1), $post_info['thread_user']);
if($x[1] && ADMIN)
{
	return $x[1];
}
SC_END

SC_BEGIN POSTER
global $post_info, $tp;
if($post_info['user_name'])
{
	return "<a href='".e_BASE."user.php?id.".$post_info['user_id']."'><b>".$post_info['user_name']."</b></a>";
}
else
{
	$x = explode(chr(1), $post_info['thread_user']);
	$tmp = explode(".", $x[0], 2);
	if(!$tmp[1])
	{
		return FORLAN_103;
	}
	else
	{
		return "<b>".$tp->toHTML($tmp[1])."</b>";
	}
}
SC_END
	
SC_BEGIN EMAILIMG
global $post_info;
if(USER && $post_info['user_id'] && !$post_info['user_hideemail'])
{
	return "<a href='mailto:{$post_info['user_email']}'>".IMAGE_email."</a>";
}
return '';
SC_END
	
SC_BEGIN EMAILITEM
global $post_info, $tp;
if($post_info['thread_parent'] == 0)
{
	return $tp->parseTemplate("{EMAIL_ITEM=".FORLAN_101."^plugin:forum.{$post_info['thread_id']}}");
}
SC_END

SC_BEGIN PRINTITEM
global $post_info, $tp;
if($post_info['thread_parent'] == 0)
{
	return $tp->parseTemplate("{PRINT_ITEM=".FORLAN_102."^plugin:forum.{$post_info['thread_id']}}");
}
SC_END
	
SC_BEGIN SIGNATURE
global $post_info, $tp;
if ($post_info['user_forums'] >= $pref['forum_posts_sig'] && check_class($pref['forum_class_sig'], $post_info['user_class'])) {
return ($post_info['user_signature'] ? "<br /><hr style='width:15%; text-align:left' /><span class='smalltext'>".$tp->toHTML($post_info['user_signature'],TRUE)."</span>" : "");
}
SC_END
	
SC_BEGIN PROFILEIMG
global $post_info, $tp;
if (USER && $post_info['user_id']) {
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
global $post_info, $thread_info, $thread_id;
if ($post_info['user_id'] != '0' && $post_info['user_name'] === USERNAME && $thread_info['head']['thread_active']) {
return "<a href='forum_post.php?edit.".$post_info['thread_id']."'>".IMAGE_edit."</a> ";
} else {
return "";
}
SC_END
	
SC_BEGIN CUSTOMTITLE
global $post_info, $tp;
if ($post_info['user_customtitle']) {
return $tp->toHTML($post_info['user_customtitle'])."<br />";
}
SC_END
	
SC_BEGIN WEBSITE
global $post_info, $tp;
if ($post_info['user_homepage']) {
return LAN_08.": ".$post_info['user_homepage']."<br />";
}
SC_END
	
SC_BEGIN WEBSITEIMG
global $post_info;
if ($post_info['user_homepage'] && $post_info['user_homepage'] != "http://") {
return "<a href='{$post_info['user_homepage']}'>".IMAGE_website."</a>";
}
SC_END
	
SC_BEGIN QUOTEIMG
global $thread_info, $post_info, $forum_info;
if (check_class($forum_info['forum_postclass']) && check_class($forum_info['parent_postclass']) && $thread_info["head"]["thread_active"]) {
return "<a href='".e_PLUGIN."forum/forum_post.php?quote.{$post_info['thread_id']}'>".IMAGE_quote."</a>";
}
SC_END
	
SC_BEGIN REPORTIMG
global $post_info, $from;
if (USER) {
return "<a href='".e_PLUGIN."forum/forum_viewtopic.php?{$post_info['thread_id']}.{$from}.report'>".IMAGE_report."</a> ";
}
SC_END
	
SC_BEGIN RPG
global $post_info;
return rpg($post_info['user_join'],$post_info['user_forums']);
SC_END
	
SC_BEGIN MEMBERID
global $post_info, $ldata, $pref, $forum_info;
if ($post_info['anon']) {
return "";
}

$fmod = ($post_info['user_class'] != "" && check_class($forum_info['forum_moderators'], $post_info['user_class'], TRUE));
if(!$fmod && $forum_info['forum_moderators'] == e_UC_ADMIN)
{
	$fmod = $post_info['user_admin'];
}
if (!array_key_exists($post_info['user_id'],$ldata)) {
	$ldata[$post_info['user_id']] = get_level($post_info['user_id'], $post_info['user_forums'], $post_info['user_comments'], $post_info['user_chats'], $post_info['user_visits'], $post_info['user_join'], $post_info['user_admin'], $post_info['user_perms'], $pref, $fmod);
}
return $ldata[$post_info['user_id']][0];
SC_END
	
SC_BEGIN LEVEL
global $post_info, $ldata, $pref, $forum_info;
if ($post_info['anon']) {
return "";
}
$fmod = ($post_info['user_class'] != "" && check_class($forum_info['forum_moderators'], $post_info['user_class'], TRUE));
if(!$fmod && $forum_info['forum_moderators'] == e_UC_ADMIN)
{
	$fmod = $post_info['user_admin'];
}
if (!array_key_exists($post_info['user_id'],$ldata)) {
$ldata[$post_info['user_id']] = get_level($post_info['user_id'], $post_info['user_forums'], $post_info['user_comments'], $post_info['user_chats'], $post_info['user_visits'], $post_info['user_join'], $post_info['user_admin'], $post_info['user_perms'], $pref, $fmod);
}
if($parm == 'pic')
{
return $ldata[$post_info['user_id']]['pic'];
}
if($parm == 'name')
{
return $ldata[$post_info['user_id']]['name'];
}
if($parm == 'special')
{
return $ldata[$post_info['user_id']]['special'];
}
if($parm == 'userid')
{
return $ldata[$post_info['user_id']]['userid'];
}
return $ldata[$post_info['user_id']][1];
SC_END
	
SC_BEGIN MODOPTIONS
if (MODERATOR) {
return showmodoptions();
}
SC_END
	
SC_BEGIN LASTEDIT
global $post_info, $gen;
if ($post_info['thread_edit_datestamp']) {
return $gen->convert_date($post_info['thread_edit_datestamp'],'forum');
}
return "";
SC_END

SC_BEGIN POLL
global $pollstr;
return $pollstr;
SC_END

SC_BEGIN NEWFLAG
// Defined in case an indicator is required
return '';
SC_END

*/
?>
