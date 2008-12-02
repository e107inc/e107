<?php
if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$forum_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
/*
SC_BEGIN TOP
return "<a href='".e_SELF."?".e_QUERY."#top' onclick=\"window.scrollTo(0,0);\">".LAN_10."</a>";
SC_END

SC_BEGIN JOINED
global $postInfo, $gen;
if ($postInfo['post_user'])
{
	return LAN_06.': '.$gen->convert_date($postInfo['user_join'], 'forum').'<br />';
}
SC_END

SC_BEGIN THREADDATESTAMP
global $postInfo, $gen;
$e107 = e107::getInstance();
return "<a id='post_{$post_info['post_id']}' href='".$e107->url->getUrl('forum', 'thread', array('func' => 'post', 'id' => $postInfo['post_id']))."'>".IMAGE_post."</a> ".$gen->convert_date($postInfo['post_datestamp'], 'forum');
SC_END

SC_BEGIN POST
global $postInfo;
$e107 = e107::getInstance();
return $e107->tp->toHTML($postInfo['post_entry'], true, 'USER_BODY', 'class:'.$post_info['user_class']);
SC_END

SC_BEGIN PRIVMESSAGE
global $pref, $post_info, $tp;
if(isset($pref['plug_installed']['pm']) && ($post_info['user_id'] > 0))
{
	return $tp->parseTemplate("{SENDPM={$post_info['user_id']}}");
}
SC_END

SC_BEGIN AVATAR
global $postInfo;
if ($postInfo['post_user'])
{
	if(!$avatar = getcachedvars('forum_avatar_'.$postInfo['post_user']))
	{
		if ($postInfo['user_image'])
		{
			require_once(e_HANDLER.'avatar_handler.php');
			$avatar = "<div class='spacer'><img src='".avatar($postInfo['user_image'])."' alt='' /></div><br />";
		}
		else
		{
			$avatar = '';
		}
		cachevars('forum_avatar_'.$postInfo['post_user'], $avatar);
	}
	return $avatar;
}
return '';
SC_END

SC_BEGIN ANON_IP
global $postInfo;
$e107 = e107::getInstance();
if(ADMIN || MODERATOR)
{
	return $e107->ipDecode($postInfo['post_ip']);
}
SC_END

SC_BEGIN IP
global $postInfo;
$e107 = e107::getInstance();
if((ADMIN || MODERATOR) && !$postInfo['user_admin'])
{
	return $e107->ipDecode($postInfo['post_ip']);
}
SC_END

SC_BEGIN POSTER
global $postInfo;
$e107 = e107::getInstance();
if($postInfo['user_name'])
{
	return "<a href='".$e107->url->getUrl('core:user', 'main', array('func' => 'profile', 'id' => $postInfo['post_user']))."'>{$postInfo['user_name']}</a>";
}
else
{
	return '<b>'.$e107->tp->toHTML($postInfo['post_anon_name']).'</b>';
}
SC_END

SC_BEGIN EMAILIMG
global $postInfo;
$e107 = e107::getInstance();
if($postInfo['user_name'])
{
	return (!$post_info['user_hideemail'] ? $e107->tp->parseTemplate("{EMAILTO={$postInfo['user_email']}}") : '');
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
if(!USER) { return ''; }
global $postInfo, $pref;
static $forum_sig_shown;
$e107 = e107::getInstance();
//$pref['forum_sig_once'] = true;
if(varsettrue($pref['forum_sig_once']))
{
	$_tmp = 'forum_sig_shown_'.$postInfo['post_user'];
	if(getcachedvars($_tmp)) { return ''; }
	cachevars($_tmp, 1);
}
return ($postInfo['user_signature'] ? "<br /><hr style='width:15%; text-align:left' /><span class='smalltext'>".$e107->tp->toHTML($postInfo['user_signature'], true).'</span>' : '');
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
global $postInfo;
if ($postInfo['post_user'])
{
	return LAN_67.': '.(int)$postInfo['user_plugin_forum_posts'].'<br />';
}
SC_END

SC_BEGIN VISITS
global $post_info;
if ($post_info['user_id']) {
return LAN_09.": ".$post_info['user_visits']."<br />";
}
SC_END

SC_BEGIN CUSTOMTITLE
global $postInfo;
$e107 = e107::getInstance();
if ($postInfo['user_customtitle'])
{
	return $e107->tp->toHTML($postInfo['user_customtitle']).'<br />';
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

SC_BEGIN EDITIMG
global $postInfo, $threadInfo;
$e107 = e107::getInstance();
if (USER && $postInfo['post_user'] == USERID && $threadInfo['thread_active'])
{
	return "<a href='".$e107->url->getUrl('forum', 'thread', array('func' => 'edit', 'id' => $postInfo['post_id']))."'>".IMAGE_edit.'</a> ';
}
return '';
SC_END

SC_BEGIN QUOTEIMG
global $postInfo, $forum;
$e107 = e107::getInstance();
if($forum->checkperm($postInfo['post_forum'], 'post'))
{
	return "<a href='".$e107->url->getUrl('forum', 'thread', array('func' => 'quote', 'id' => $postInfo['post_id']))."'>".IMAGE_quote.'</a> ';
}
SC_END

SC_BEGIN REPORTIMG
global $postInfo, $page;
if (USER) {
	$e107 = e107::getInstance();
	$tmp = array (
	'func' => 'report',
	'id' => $postInfo['post_thread'],
	'report' => $postInfo['post_id']
	);
	return "<a href='".$e107->url->getUrl('forum', 'thread', $tmp)."'>".IMAGE_report.'</a> ';
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
global $postInfo, $gen;
if ($postInfo['post_edit_datestamp'])
{
	return $gen->convert_date($postInfo['thread_edit_datestamp'],'forum');
}
return '';
SC_END

SC_BEGIN LASTEDITBY
global $postInfo;
$e107 = e107::getInstance();
if ($postInfo['post_edit_datestamp'])
{
	return $postInfo['edit_name'];
}
return '';
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