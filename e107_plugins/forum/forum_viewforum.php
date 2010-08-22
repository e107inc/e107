<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

require_once("../../class2.php");
if (isset($_POST['fjsubmit'])) {
	header("location:".e_PLUGIN."forum/forum_viewforum.php?".$_POST['forumjump']);
	exit();
}

include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_viewforum.php');

if (!e_QUERY)
{
	js_location(e_PLUGIN."forum/forum.php");
}
else
{
	$tmp = explode(".", e_QUERY);
	$forum_id = intval($tmp[0]);
	$thread_from = (isset($tmp[1]) ? intval($tmp[1]) : 0);
}
$view = 25;

if(is_numeric(e_MENU))
{
	$thread_from = (intval(e_MENU)-1)*$view;
}
require_once(e_PLUGIN.'forum/forum_class.php');
$forum = new e107forum;

$STARTERTITLE = LAN_54;
$THREADTITLE = LAN_53;
$REPLYTITLE = LAN_55;
$LASTPOSTITLE = LAN_57;
$VIEWTITLE = LAN_56;

global $forum_info, $FORUM_CRUMB;
$forum_info = $forum->forum_get($forum_id);

if (!check_class($forum_info['forum_class']) || !check_class($forum_info['parent_class']) || !$forum_info['forum_parent'])
{
	header("Location:".e_PLUGIN."forum/forum.php");
	exit;
}

if (!$FORUM_VIEW_START) {
	if (file_exists(THEME."forum_viewforum_template.php"))
	{
		require_once(THEME."forum_viewforum_template.php");
	}
	else if (file_exists(THEME."forum_template.php"))
	{
		require_once(THEME."forum_template.php");
	}
	else
	{
		require_once(e_PLUGIN."forum/templates/forum_viewforum_template.php");
	}
}


$forum_info['forum_name'] = $tp->toHTML($forum_info['forum_name'], TRUE, 'no_hook, emotes_off');
$forum_info['forum_description'] = $tp->toHTML($forum_info['forum_description'], TRUE, 'no_hook');

$_forum_name = (substr($forum_info['forum_name'], 0, 1) == "*" ? substr($forum_info['forum_name'], 1) : $forum_info['forum_name']);
define("e_PAGETITLE", LAN_01." / ".$_forum_name);
define("MODERATOR", $forum_info['forum_moderators'] != "" && check_class($forum_info['forum_moderators']));
$modArray = $forum->forum_getmods($forum_info['forum_moderators']);
$message = "";
if (MODERATOR)
{
	if ($_POST)
	{
		require_once(e_PLUGIN."forum/forum_mod.php");
		$message = forum_thread_moderate($_POST);
	}
}

$member_users = $sql->db_Select("online", "*", "online_location REGEXP('forum_viewforum.php.$forum_id') AND online_user_id!='0' ");
$guest_users = $sql->db_Select("online", "*", "online_location REGEXP('forum_viewforum.php.$forum_id') AND online_user_id='0' ");
$users = $member_users+$guest_users;

require_once(HEADERF);
$text='';
if ($message)
{
	$ns->tablerender("", $message, array('forum_viewforum', 'msg'));
}

$topics = $forum->forum_get_topic_count($forum_id);

if ($topics > $view)
{
	$pages = ceil($topics/$view);
}
else
{
	$pages = FALSE;
}

if ($pages)
{
	if(strpos($FORUM_VIEW_START, 'THREADPAGES') !== FALSE || strpos($FORUM_VIEW_END, 'THREADPAGES') !== FALSE)
	{
		$parms = "{$topics},{$view},{$thread_from},".e_SELF.'?'.$forum_id.'.[FROM],off';
		$THREADPAGES = $tp->parseTemplate("{NEXTPREV={$parms}}");
	}
}

if (check_class($forum_info['forum_postclass']) && check_class($forum_info['parent_postclass']))
{
	$NEWTHREADBUTTON = "<a href='".e_PLUGIN."forum/forum_post.php?nt.".$forum_id."'>".IMAGE_newthread."</a>";
}

if(substr($forum_info['forum_name'], 0, 1) == "*")
{
	$forum_info['forum_name'] = substr($forum_info['forum_name'], 1);
	$container_only = true;
}
else
{
	$container_only = false;
}

if(substr($forum_info['sub_parent'], 0, 1) == "*")
{
	$forum_info['sub_parent'] = substr($forum_info['sub_parent'], 1);
}

$forum->set_crumb(); // set $BREADCRUMB (and $BACKLINK)

$FORUMTITLE = $forum_info['forum_name'];
//$MODERATORS = LAN_404.": ".$forum_info['forum_moderators'];
$MODERATORS = LAN_404.": ".implode(", ", $modArray);
$BROWSERS = $users." ".($users == 1 ? LAN_405 : LAN_406)." (".$member_users." ".($member_users == 1 ? LAN_407 : LAN_409).", ".$guest_users." ".($guest_users == 1 ? LAN_408 : LAN_410).")";

$ICONKEY = "
	<table style='width:100%'>
	<tr>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_new_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_79."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_nonew_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_80."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_sticky_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_202."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_announce_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_396."</td>
	</tr>
	<tr>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_new_popular_small."</td>
	<td style='width:2%' class='smallblacktext'>".LAN_79." ".LAN_395."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_nonew_popular_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_80." ".LAN_395."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_stickyclosed_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_203."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_closed_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_81."</td>
	</tr>
	</table>";

$SEARCH = "
	<form method='get' action='".e_BASE."search.php'>
	<p>
	<input class='tbox' type='text' name='q' size='20' value='' maxlength='50' />
	<input type='hidden' name='r' value='0' />
	<input type='hidden' name='ref' value='forum' />
	<input class='button' type='submit' name='s' value='".LAN_180."' />
	</p>
	</form>";

if(check_class($forum_info['forum_postclass']))
{
	$PERMS = LAN_204." - ".LAN_206." - ".LAN_208;
}
else
{
	$PERMS = LAN_205." - ".LAN_207." - ".LAN_209;
}

$sticky_threads = 0;
$stuck = FALSE;
$reg_threads = 0;
$unstuck = FALSE;

$thread_list = $forum->forum_get_topics($forum_id, $thread_from, $view);
$sub_list = $forum->forum_getsubs($forum_id);
//print_a($sub_list);
$gen = new convert;

$SUBFORUMS = "";
if(is_array($sub_list))
{
	$newflag_list = $forum->forum_newflag_list();
	$sub_info = "";
	foreach($sub_list as $sub)
	{
		$sub_info .= parse_sub($sub);
	}
	$SUBFORUMS = $FORUM_VIEW_SUB_START.$sub_info.$FORUM_VIEW_SUB_END;
}


if (count($thread_list) )
{
	foreach($thread_list as $thread_info) {
		$idArray[] = $thread_info['thread_id'];
	}
	$inList = '('.implode(',', $idArray).')';
	foreach($thread_list as $thread_info) {
		if ($thread_info['thread_s']) {
			$sticky_threads ++;
		}
		if ($sticky_threads > 0 && !$stuck && $pref['forum_hilightsticky'])
		{
			if($FORUM_IMPORTANT_ROW)
			{
				$forum_view_forum .= $FORUM_IMPORTANT_ROW;
			}
			else
			{
				$forum_view_forum .= "<tr><td class='forumheader'>&nbsp;</td><td colspan='5'  class='forumheader'><span class='mediumtext'><b>".LAN_411."</b></span></td></tr>";
			}
			$stuck = TRUE;
		}
		if (!$thread_info['thread_s'])
		{
			$reg_threads ++;
		}
		if ($reg_threads == "1" && !$unstuck && $stuck)
		{
			if($FORUM_NORMAL_ROW)
			{
				$forum_view_forum .= $FORUM_NORMAL_ROW;
			}
			else
			{
				$forum_view_forum .= "<tr><td class='forumheader'>&nbsp;</td><td colspan='5'  class='forumheader'><span class='mediumtext'><b>".LAN_412."</b></span></td></tr>";
			}
			$unstuck = TRUE;
		}
		$forum_view_forum .= parse_thread($thread_info);
	}
}
else
{
	$forum_view_forum .= "<tr><td class='forumheader' colspan='6'>".LAN_58."</td></tr>";
}

$sql->db_Select("forum", "*", "forum_parent !=0 AND forum_class!='255' ");
$FORUMJUMP = forumjump();
$TOPLINK = "<a href='".e_SELF."?".e_QUERY."#top' onclick=\"window.scrollTo(0,0);\">".LAN_02."</a>";

if($container_only)
{
	$FORUM_VIEW_START = ($FORUM_VIEW_START_CONTAINER ? $FORUM_VIEW_START_CONTAINER : $FORUM_VIEW_START);
	$FORUM_VIEW_END = ($FORUM_VIEW_END_CONTAINER ? $FORUM_VIEW_END_CONTAINER : $FORUM_VIEW_END);
	$forum_view_forum = "";
}

//$forum_view_start = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_VIEW_START);
$forum_view_start = $tp->simpleParse($FORUM_VIEW_START);

//$forum_view_end = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_VIEW_END);
$forum_view_end = $tp->simpleParse($FORUM_VIEW_END);


if ($pref['forum_enclose'])
{
	$ns->tablerender($pref['forum_title'], $forum_view_start.$forum_view_subs.$forum_view_forum.$forum_view_end, array('forum_viewforum', 'main1'));
}
else
{
	echo $forum_view_start.$forum_view_forum.$forum_view_end;
}

echo "<script type=\"text/javascript\">
	function confirm_(thread_id)
	{
		return confirm(\"".$tp->toJS(LAN_434)."\");
	}
	</script>";

require_once(FOOTERF);


function parse_thread($thread_info)
{
	global $forum, $tp, $FORUM_VIEW_FORUM, $FORUM_VIEW_FORUM_STICKY, $FORUM_VIEW_FORUM_ANNOUNCE, $gen, $pref, $forum_id, $menu_pref;
	$text = "";
	$VIEWS = $thread_info['thread_views'];
	$REPLIES = $thread_info['thread_total_replies'];


	if ($REPLIES)
	{
		$lastpost_datestamp = $gen->convert_date($thread_info['thread_lastpost'], 'forum');
		$tmp = explode(".", $thread_info['thread_lastuser'], 2);
		if($thread_info['lastpost_username'])
		{
			$LASTPOST = "<a href='".e_BASE."user.php?id.".$tmp[0]."'>".$thread_info['lastpost_username']."</a>";
		}
		else
		{
			if($tmp[1])
			{
				$LASTPOST = $tp->toHTML($tmp[1]);
			}
			else
			{
				$LASTPOST = FORLAN_19;
			}
		}
		$LASTPOST .= "<br />".$lastpost_datestamp;
	}

	$newflag = FALSE;
	if (USER)
	{
		if ($thread_info['thread_lastpost'] > USERLV && !preg_match("#\b".$thread_info['thread_id']."\b#", USERVIEWED))
		{
			$newflag = TRUE;
		}
	}

	$THREADDATE = $gen->convert_date($thread_info['thread_datestamp'], 'forum');
	$ICON = ($newflag ? IMAGE_new : IMAGE_nonew);
	if ($REPLIES >= $pref['forum_popular'])
	{
	  $ICON = ($newflag ? IMAGE_new_popular : IMAGE_nonew_popular);
	}

	$THREADTYPE = '';
	if ($thread_info['thread_s'] == 1)
	{
		$ICON = ($thread_info['thread_active'] ? IMAGE_sticky : IMAGE_stickyclosed);
		$THREADTYPE = '['.LAN_202.']<br />';
	}
	elseif($thread_info['thread_s'] == 2)
	{
		$ICON = IMAGE_announce;
		$THREADTYPE = '['.LAN_396.']<br />';
	}
	elseif(!$thread_info['thread_active'])
	{
		$ICON = IMAGE_closed;
	}

	$thread_name = strip_tags($tp->toHTML($thread_info['thread_name'], false, 'no_hook, emotes_off'));
	if (strtoupper($THREADTYPE) == strtoupper(substr($thread_name, 0, strlen($THREADTYPE)))) {
		$thread_name = substr($thread_name, strlen($THREADTYPE));
	}
	if ($pref['forum_tooltip']) {
		$thread_thread = strip_tags($tp->toHTML($thread_info['thread_thread'], true, 'no_hook'));
		$tip_length = ($pref['forum_tiplength'] ? $pref['forum_tiplength'] : 400);
		if (strlen($thread_thread) > $tip_length) 
		{
			//$thread_thread = substr($thread_thread, 0, $tip_length)." ".$menu_pref['newforumposts_postfix'];
			$thread_thread = $tp->text_truncate($thread_thread, $tip_length, $menu_pref['newforumposts_postfix']);	// Doesn't split entities
		}
		$thread_thread = str_replace("'", "&#39;", $thread_thread);
		$title = "title='".$thread_thread."'";
	} 
	else 
	{
		$title = "";
	}
	$THREADNAME = "<a {$title} href='".e_PLUGIN."forum/forum_viewtopic.php?{$thread_info['thread_id']}'>{$thread_name}</a>";

	$pages = ceil(($REPLIES+1)/$pref['forum_postspage']);
	if ($pages > 1)
	{
		if($pages > 6)
		{
			for($a = 0; $a <= 2; $a++)
			{
				$PAGES .= $PAGES ? " " : "";
				$PAGES .= "<a href='".e_PLUGIN."forum/forum_viewtopic.php?".$thread_info['thread_id'].".".($a * $pref['forum_postspage'])."'>".($a+1)."</a>";
			}
			$PAGES .= " ... ";
			for($a = $pages-3; $a <= $pages-1; $a++)
			{
				$PAGES .= $PAGES ? " " : "";
				$PAGES .= "<a href='".e_PLUGIN."forum/forum_viewtopic.php?".$thread_info['thread_id'].".".($a * $pref['forum_postspage'])."'>".($a+1)."</a>";
			}
		}
		else
		{
			for($a = 0; $a <= ($pages-1); $a++)
			{
				$PAGES .= $PAGES ? " " : "";
				$PAGES .= "<a href='".e_PLUGIN."forum/forum_viewtopic.php?".$thread_info['thread_id'].".".($a * $pref['forum_postspage'])."'>".($a+1)."</a>";
			}
		}
		$PAGES = LAN_316." [&nbsp;".$PAGES."&nbsp;]";
	}
	else
	{
		$PAGES = "";
	}

	if (MODERATOR)
	{
		$thread_id = $thread_info['thread_id'];
		$ADMIN_ICONS = "
			<form method='post' action='".e_SELF."?{$forum_id}' id='frmMod_{$forum_id}_{$thread_id}' style='margin:0;'><div>
			";

		$ADMIN_ICONS .= "<input type='image' ".IMAGE_admin_delete." name='delete_$thread_id' value='thread_action' onclick=\"return confirm_($thread_id)\" /> \n";

		$ADMIN_ICONS .= ($thread_info['thread_s'] == 1) ? "<input type='image' ".IMAGE_admin_unstick." name='unstick_{$thread_id}' value='thread_action' /> " :
		 "<input type='image' ".IMAGE_admin_stick." name='stick_{$thread_id}' value='thread_action' /> ";
		$ADMIN_ICONS .= ($thread_info['thread_active']) ? "<input type='image' ".IMAGE_admin_lock." name='lock_{$thread_id}' value='thread_action' /> " :
		 "<input type='image' ".IMAGE_admin_unlock." name='unlock_{$thread_id}' value='thread_action' /> ";
		$ADMIN_ICONS .= "<a href='".e_PLUGIN."forum/forum_conf.php?move.".$thread_id."'>".IMAGE_admin_move."</a>";
		$ADMIN_ICONS .= "
			</div></form>
			";
	}

	$text .= "</td>
		<td style='vertical-align:top; text-align:center; width:20%' class='forumheader3'>".$THREADDATE."<br />
		";
	$tmp = explode(".", $thread_info['thread_user'], 2);
	if($thread_info['user_name'])
	{
		$POSTER = "<a href='".e_BASE."user.php?id.".$tmp[0]."'>".$thread_info['user_name']."</a>";
	}
	else
	{
		if($tmp[1])
		{
			$x = explode(chr(1), $tmp[1]);
			$POSTER = $tp->toHTML($x[0]);
		}
		else
		{
			$POSTER = FORLAN_19;
		}
	}

	if ($thread_info['thread_s'] == 1 && $FORUM_VIEW_FORUM_STICKY)
	{
		return(preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_VIEW_FORUM_STICKY));
	}

	if ($thread_info['thread_s'] == 2 && $FORUM_VIEW_FORUM_ANNOUNCE)
	{
		return(preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_VIEW_FORUM_ANNOUNCE));
	}

	if (!$REPLIES)
	{
		$REPLIES = LAN_317;		// 'None'
		$LASTPOST = " - ";
	}

	return(preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_VIEW_FORUM));
}

function parse_sub($subInfo)
{
	global $FORUM_VIEW_SUB, $gen, $tp, $newflag_list;
	$SUB_FORUMTITLE = "<a href='".e_PLUGIN."forum/forum_viewforum.php?{$subInfo['forum_id']}'>{$subInfo['forum_name']}</a>";
	$SUB_DESCRIPTION = $tp->toHTML($subInfo['forum_description'], false, 'no_hook');
	$SUB_THREADS = $subInfo['forum_threads'];
	$SUB_REPLIES = $subInfo['forum_replies'];
	if(USER && is_array($newflag_list) && in_array($subInfo['forum_id'], $newflag_list))
	{
		$NEWFLAG = "<a href='".e_SELF."?mfar.{$subInfo['forum_id']}'>".IMAGE_new."</a>";
	}
	else
	{
		$NEWFLAG = IMAGE_nonew;
	}

	if($subInfo['forum_lastpost_info'])
	{
		$tmp = explode(".", $subInfo['forum_lastpost_info']);
		$lp_thread = "<a href='".e_PLUGIN."forum/forum_viewtopic.php?{$tmp[1]}.last'>".IMAGE_post2."</a>";
		$lp_date = $gen->convert_date($tmp[0], 'forum');
		$tmp = explode(".", $subInfo['forum_lastpost_user'],2);
		if($subInfo['user_name'])
		{
			$lp_name = "<a href='".e_BASE."user.php?id.{$tmp[0]}'>{$subInfo['user_name']}</a>";
		}
		else
		{
			$lp_name = $tmp[1];
		}
		$SUB_LASTPOST = $lp_date."<br />".$lp_name." ".$lp_thread;
	}
	else
	{
		$SUB_LASTPOST = "-";
	}
	return  (preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_VIEW_SUB));
}

function forumjump()
{
	global $forum;
	$jumpList = $forum->forum_get_allowed();
	$text = "<form method='post' action='".e_SELF."'><p>".LAN_403.": <select name='forumjump' class='tbox'>";
	foreach($jumpList as $key => $val)
	{
		$text .= "\n<option value='".$key."'>".$val."</option>";
	}
	$text .= "</select> <input class='button' type='submit' name='fjsubmit' value='".LAN_03."' />&nbsp;&nbsp;&nbsp;&nbsp;<a href='".e_SELF."?".$_SERVER['QUERY_STRING']."#top'>".LAN_02."</a></p></form>";
	return $text;
}

?>