<?php
/*
* e107 website system
*
* Copyright (C) 2008-2011 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* View specific forums
*
* $URL$
* $Id$
*
*/

require_once('../../class2.php');
$e107 = e107::getInstance();
if (!$e107->isInstalled('forum'))
{
	header('Location: '.e_BASE.'index.php');
	exit;
}
include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_viewforum.php');

if (isset($_POST['fjsubmit']))
{
	header('location:'.$e107->url->getUrl('forum', 'forum', array('func' => 'view', 'id'=>$_POST['forumjump'])));
	exit;
}

if (!e_QUERY)
{
	header('Location:'.$e107->url->getUrl('forum', 'forum', array('func' => 'main')));
	exit;
}

require_once(e_PLUGIN.'forum/forum_class.php');
$forum = new e107forum;

//$view = 25;
$view = $forum->prefs->get('threadspage', 25);
if(!$view) { $view = 25; }
$page = (varset($_GET['p']) ? $_GET['p'] : 1);
$threadFrom = ($page - 1) * $view;

global $forum_info, $FORUM_CRUMB;
$fVars = new e_vars;

$fVars->STARTERTITLE = LAN_54;
$fVars->THREADTITLE = LAN_53;
$fVars->REPLYTITLE = LAN_55;
$fVars->LASTPOSTITLE = LAN_57;
$fVars->VIEWTITLE = LAN_56;

$forumId = (int)$_REQUEST['id'];

if (!$forum->checkPerm($forumId, 'view'))
{
	header('Location:'.$e107->url->getUrl('forum', 'forum', array('func' => 'main')));
	exit;
}

$forumInfo = $forum->forumGet($forumId);
$threadsViewed = $forum->threadGetUserViewed();

if (!$FORUM_VIEW_START)
{
	if (file_exists(THEME.'forum_viewforum_template.php'))
	{
		require_once(THEME.'forum_viewforum_template.php');
	}
	elseif (file_exists(THEME.'forum_template.php'))
	{
		require_once(THEME.'forum_template.php');
	}
	else
	{
		require_once(e_PLUGIN.'forum/templates/forum_viewforum_template.php');
	}
}

$forumInfo['forum_name'] = $e107->tp->toHTML($forumInfo['forum_name'], true, 'no_hook, emotes_off');
$forumInfo['forum_description'] = $e107->tp->toHTML($forumInfo['forum_description'], true, 'no_hook');

$_forum_name = (substr($forumInfo['forum_name'], 0, 1) == '*' ? substr($forumInfo['forum_name'], 1) : $forumInfo['forum_name']);
define('e_PAGETITLE', $_forum_name.' / '.LAN_01);

// SEO - meta description (auto)
if(!empty($forumInfo['forum_description']))
{
	define("META_DESCRIPTION", $tp->text_truncate(
		str_replace(
			array('"', "'"), '', strip_tags($tp->toHTML($forumInfo['forum_description']))
	), 250, '...'));
}

//define('MODERATOR', $forum_info['forum_moderators'] != '' && check_class($forum_info['forum_moderators']));
//$modArray = $forum->forum_getmods($forum_info['forum_moderators']);

$modArray = $forum->forumGetMods($thread->forum_info['forum_moderators']);
define('MODERATOR', (USER && is_array($modArray) && in_array(USERID, array_keys($modArray))));

$message = '';
if (MODERATOR)
{
	if ($_POST)
	{
		require_once(e_PLUGIN.'forum/forum_mod.php');
		$message = forum_thread_moderate($_POST);
	}
}

if(varset($pref['track_online']))
{
	$member_users = $sql->db_Count('online', '(*)', "WHERE online_location REGEXP('viewforum.php.id=$forumId\$') AND online_user_id != 0");
	$guest_users = $sql->db_Count('online', '(*)', "WHERE online_location REGEXP('viewforum.php.id=$forumId\$') AND online_user_id = 0");
	$users = $member_users+$guest_users;
}

require_once(HEADERF);
$text='';
// TODO - message batch shortcode
if ($message)
{
	//$ns->tablerender('', $message, array('forum_viewforum', 'msg'));
	//e107::getMessage()->add($thread->message);
	$fVars->MESSAGE = $message;
}

$threadCount = $forumInfo['forum_threads'];

if ($threadCount > $view)
{
	$pages = ceil($threadCount/$view);
}
else
{
	$pages = false;
}

if ($pages)
{
	if(strpos($FORUM_VIEW_START, 'THREADPAGES') !== false || strpos($FORUM_VIEW_END, 'THREADPAGES') !== false)
	{
		//if(!$page) $page = 1;
		$url = rawurlencode(e107::getUrl()->getUrl('forum', 'forum', array('func' => 'view', 'id' => $forumId, 'page' => '[FROM]')));
		$parms = "total={$pages}&type=page&current={$page}&url=".$url."&caption=off";
		$fVars->THREADPAGES = $e107->tp->parseTemplate("{NEXTPREV={$parms}}");
	}
}

if($forum->checkPerm($forumId, 'post'))
{
	$fVars->NEWTHREADBUTTON = "<a href='".$e107->url->getUrl('forum', 'thread', array('func' => 'nt', 'id' => $forumId))."'>".IMAGE_newthread.'</a>';
}

if(substr($forumInfo['forum_name'], 0, 1) == '*')
{
	$forum_info['forum_name'] = substr($forum_info['forum_name'], 1);
	$container_only = true;
}
else
{
	$container_only = false;
}

if(substr($forum_info['sub_parent'], 0, 1) == '*')
{
	$forum_info['sub_parent'] = substr($forum_info['sub_parent'], 1);
}

$forum->set_crumb(true, '', $fVars); // set $BREADCRUMB (and $BACKLINK)

$fVars->FORUMTITLE = $forumInfo['forum_name'];
$fVars->MODERATORS = LAN_404.': '.implode(', ', $modArray);
$fVars->BROWSERS = '';
if(varset($pref['track_online']))
{
	$fVars->BROWSERS = $users.' '.($users == 1 ? LAN_405 : LAN_406).' ('.$member_users.' '.($member_users == 1 ? LAN_407 : LAN_409).", ".$guest_users." ".($guest_users == 1 ? LAN_408 : LAN_410).')';
}


$fVars->ICONKEY = "
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

$fVars->SEARCH = "
	<form method='get' action='".e_BASE."search.php'>
	<p>
	<input class='tbox' type='text' name='q' size='20' value='' maxlength='50' />
	<input type='hidden' name='r' value='0' />
	<input type='hidden' name='ref' value='forum' />
	<input class='button' type='submit' name='s' value='".LAN_180."' />
	</p>
	</form>";

if($forum->checkPerm($forumId, 'post'))
{
	$fVars->PERMS = LAN_204.' - '.LAN_206.' - '.LAN_208;
}
else
{
	$fVars->PERMS = LAN_205.' - '.LAN_207.' - '.LAN_209;
}

$sticky_threads = 0;
$stuck = false;
$reg_threads = 0;
$unstuck = false;

$threadList = $forum->forumGetThreads($forumId, $threadFrom, $view);
$subList = $forum->forumGetSubs($forum_id);
$gen = new convert;

$fVars->SUBFORUMS = '';
if(is_array($subList) && isset($subList[$forumInfo['forum_parent']][$forumId]))
{
	$newflag_list = $forum->forumGetUnreadForums();
	$sub_info = '';
	foreach($subList[$forumInfo['forum_parent']][$forumId] as $sub)
	{
		$sub_info .= parse_sub($sub);
	}
	$fVars->SUBFORUMS = $FORUM_VIEW_SUB_START.$sub_info.$FORUM_VIEW_SUB_END;
}

if (count($threadList) )
{
	foreach($threadList as $thread_info)
	{
		if($thread_info['thread_options'])
		{
			$thread_info['thread_options'] = unserialize($thread_info['thread_options']);
		}
		else
		{
			$thread_info['thread_options'] = array();
		}
		if ($thread_info['thread_sticky'])
		{
			$sticky_threads ++;
		}
		if ($sticky_threads > 0 && !$stuck && $forum->prefs->get('hilightsticky'))
		{
			if($FORUM_IMPORTANT_ROW)
			{
				$forum_view_forum .= $FORUM_IMPORTANT_ROW;
			}
			else
			{
				$forum_view_forum .= "<tr><td class='forumheader'>&nbsp;</td><td colspan='5'  class='forumheader'><span class='mediumtext'><b>".LAN_411."</b></span></td></tr>";
			}
			$stuck = true;
		}
		if (!$thread_info['thread_sticky'])
		{
			$reg_threads ++;
		}
		if ($reg_threads == '1' && !$unstuck && $stuck)
		{
			if($FORUM_NORMAL_ROW)
			{
				$forum_view_forum .= $FORUM_NORMAL_ROW;
			}
			else
			{
				$forum_view_forum .= "<tr><td class='forumheader'>&nbsp;</td><td colspan='5'  class='forumheader'><span class='mediumtext'><b>".LAN_412."</b></span></td></tr>";
			}
			$unstuck = true;
		}
		$forum_view_forum .= parse_thread($thread_info);
	}
}
else
{
	$forum_view_forum .= "<tr><td class='forumheader' colspan='6'>".LAN_58."</td></tr>";
}

$fVars->FORUMJUMP = forumjump();
$fVars->TOPLINK = "<a href='".e_SELF.'?'.e_QUERY."#top' onclick=\"window.scrollTo(0,0);\">".LAN_02.'</a>';

if($container_only)
{
	$FORUM_VIEW_START = ($FORUM_VIEW_START_CONTAINER ? $FORUM_VIEW_START_CONTAINER : $FORUM_VIEW_START);
	$FORUM_VIEW_END = ($FORUM_VIEW_END_CONTAINER ? $FORUM_VIEW_END_CONTAINER : $FORUM_VIEW_END);
	$forum_view_forum = '';
}

$forum_view_start = $tp->simpleParse($FORUM_VIEW_START, $fVars);
$forum_view_end = $tp->simpleParse($FORUM_VIEW_END, $fVars);


if ($forum->prefs->get('forum_enclose'))
{
	$ns->tablerender($forum->prefs->get('forum_title'), $forum_view_start.$forum_view_subs.$forum_view_forum.$forum_view_end, array('forum_viewforum', 'main1'));
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
	global $forum, $FORUM_VIEW_FORUM, $FORUM_VIEW_FORUM_STICKY, $FORUM_VIEW_FORUM_ANNOUNCE, $gen, $menu_pref, $threadsViewed;
	global $tp;
	$tVars = new e_vars;
	$e107 = e107::getInstance();
	$text = '';

	$threadId = $thread_info['thread_id'];
	$forumId = $thread_info['thread_forum_id'];

	$tVars->VIEWS = $thread_info['thread_views'];
	$tVars->REPLIES = $thread_info['thread_total_replies'];

	if ($tVars->REPLIES)
	{
		$lastpost_datestamp = $gen->convert_date($thread_info['thread_lastpost'], 'forum');
		if($thread_info['lastpost_username'])
		{
			$url = $e107->url->getUrl('core:user', 'main', "func=profile&id={$thread_info['thread_lastuser']}");
			$tVars->LASTPOST = "<a href='{$url}'>".$thread_info['lastpost_username']."</a>";
		}
		else
		{
			if(!$thread_info['thread_lastuser'])
			{
				$tVars->LASTPOST = $e107->tp->toHTML($thread_info['thread_lastuser_anon']);
			}
			else
			{
				$tVars->LASTPOST = FORLAN_19;
			}
		}
		$tVars->LASTPOST .= '<br />'.$lastpost_datestamp;
	}

	$newflag = (USER && $thread_info['thread_lastpost'] > USERLV && !in_array($thread_info['thread_id'], $threadsViewed));

	$tVars->THREADDATE = $gen->convert_date($thread_info['thread_datestamp'], 'forum');
	$tVars->ICON = ($newflag ? IMAGE_new : IMAGE_nonew);
	if ($tVars->REPLIES >= $forum->prefs->get('popular', 10))
	{
	  $tVars->ICON = ($newflag ? IMAGE_new_popular : IMAGE_nonew_popular);
	}

	$tVars->THREADTYPE = '';
	if ($thread_info['thread_sticky'] == 1)
	{
		$tVars->ICON = ($thread_info['thread_active'] ? IMAGE_sticky : IMAGE_stickyclosed);
		$tVars->THREADTYPE = '['.LAN_202.']<br />';
	}
	elseif($thread_info['thread_sticky'] == 2)
	{
		$tVars->ICON = IMAGE_announce;
		$tVars->THREADTYPE = '['.LAN_396.']<br />';
	}
	elseif(!$thread_info['thread_active'])
	{
		$tVars->ICON = IMAGE_closed;
	}

	$thread_name = strip_tags($e107->tp->toHTML($thread_info['thread_name'], false, 'no_hook, emotes_off'));
	if(isset($thread_info['thread_options']['poll']))
	{
		$thread_name = '['.FORLAN_23.'] ' . $thread_name;
	}
//	if (strtoupper($THREADTYPE) == strtoupper(substr($thread_name, 0, strlen($THREADTYPE))))
//	{
//		$thread_name = substr($thread_name, strlen($THREADTYPE));
//	}
	if ($forum->prefs->get('tooltip'))
	{
		$thread_thread = strip_tags($tp->toHTML($thread_info['thread_thread'], true, 'no_hook'));
		$tip_length = $forum->prefs->get('tiplength', 400);
		if (strlen($thread_thread) > $tip_length)
		{
			//$thread_thread = substr($thread_thread, 0, $tip_length).' '.$menu_pref['newforumposts_postfix'];
			$thread_thread = $tp->text_truncate($thread_thread, $tip_length, $menu_pref['newforumposts_postfix']);	// Doesn't split entities
		}
		$thread_thread = str_replace("'", '&#39;', $thread_thread);
		$title = "title='".$thread_thread."'";
	}
	else
	{
		$title = '';
	}
	$tVars->THREADNAME = "<a {$title} href='".$e107->url->getUrl('forum', 'thread', "func=view&id={$threadId}")."'>{$thread_name}</a>";

	$pages = ceil(($tVars->REPLIES)/$forum->prefs->get('postspage'));
	if ($pages > 1)
	{
		if($pages > 6)
		{
			for($a = 0; $a <= 2; $a++)
			{
				$aa = $a + 1;
				$tVars->PAGES .= $tVars->PAGES ? ' ' : '';
				$url = $e107->url->getUrl('forum', 'thread', "func=view&id={$thread_info['thread_id']}&page={$aa}");
				$tVars->PAGES .= "<a href='{$url}'>{$aa}</a>";
			}
			$tVars->PAGES .= ' ... ';
			for($a = $pages-3; $a <= $pages-1; $a++)
			{
				$aa = $a + 1;
				$tVars->PAGES .= $tVars->PAGES ? ' ' : '';
				$url = $e107->url->getUrl('forum', 'thread', "func=view&id={$thread_info['thread_id']}&page={$aa}");
				$tVars->PAGES .= "<a href='{$url}'>{$aa}</a>";
			}
		}
		else
		{
			for($a = 0; $a <= ($pages-1); $a++)
			{
				$aa = $a + 1;
				$tVars->PAGES .= $tVars->PAGES ? ' ' : '';
				$url = $e107->url->getUrl('forum', 'thread', "func=view&id={$thread_info['thread_id']}&page={$aa}");
				$tVars->PAGES .= "<a href='{$url}'>{$aa}</a>";
			}
		}
		$tVars->PAGES = LAN_316.' [&nbsp;'.$tVars->PAGES.'&nbsp;]';
	}
	else
	{
		$tVars->PAGES = '';
	}

	if (MODERATOR)
	{
		$tVars->ADMIN_ICONS = "
		<form method='post' action='".$e107->url->getUrl('forum', 'forum', "func=view&id={$thread_info['thread_forum_id']}")."' id='frmMod_{$forumId}_{$threadId}' style='margin:0;'><div>
		<input type='image' ".IMAGE_admin_delete." name='deleteThread_{$threadId}' value='thread_action' onclick=\"return confirm_({$threadId})\" />
		".($thread_info['thread_sticky'] == 1 ? "<input type='image' ".IMAGE_admin_unstick." name='unstick_{$threadId}' value='thread_action' /> " : "<input type='image' ".IMAGE_admin_stick." name='stick_{$threadId}' value='thread_action' /> ")."
		".($thread_info['thread_active'] ? "<input type='image' ".IMAGE_admin_lock." name='lock_{$threadId}' value='thread_action' /> " : "<input type='image' ".IMAGE_admin_unlock." name='unlock_{$threadId}' value='thread_action' /> "). "
		<a href='".$e107->url->getUrl('forum', 'thread', "func=move&id={$threadId}")."'>".IMAGE_admin_move.'</a>
		</div></form>
		';
	}

	$text .= "</td>
		<td style='vertical-align:top; text-align:center; width:20%' class='forumheader3'>".$THREADDATE.'<br />';
//	$tmp = explode('.', $thread_info['thread_user'], 2);

	if($thread_info['user_name'])
	{
		$tVars->POSTER = "<a href='".$e107->url->getUrl('core:user', 'main', "func=profile&id={$thread_info['thread_user']}")."'>".$thread_info['user_name']."</a>";
	}
	else
	{
		if($thread_info['thread_user_anon'])
		{
			$tVars->POSTER = $e107->tp->toHTML($thread_info['thread_user_anon']);
		}
		else
		{
			$tVars->POSTER = FORLAN_19;
		}
	}

	if (!$tVars->REPLIES)
	{
		$tVars->REPLIES = LAN_317;		// 'None'
		$tVars->LASTPOST = ' - ';
	}

	switch($thread_info['thread_sticky'])
	{
		case 1:
			$_TEMPLATE = ($FORUM_VIEW_FORUM_STICKY ? $FORUM_VIEW_FORUM_STICKY : $FORUM_VIEW_FORUM);
			break;

		case 2:
			$_TEMPLATE = ($FORUM_VIEW_FORUM_ANNOUNCE ? $FORUM_VIEW_FORUM_ANNOUNCE : $FORUM_VIEW_FORUM);
			break;

		default:
			$_TEMPLATE = $FORUM_VIEW_FORUM;
			break;
	}

	return $tp->simpleParse($_TEMPLATE, $tVars);
}

function parse_sub($subInfo)
{
	global $FORUM_VIEW_SUB, $gen, $newflag_list, $tp;
	$tVars = new e_vars;
	$e107 = e107::getInstance();
	$forumName = $e107->tp->toHTML($subInfo['forum_name'], true);
	$tVars->SUB_FORUMTITLE = "<a href='".$e107->url->getUrl('forum', 'forum', "func=view&id={$subInfo['forum_id']}")."'>{$forumName}</a>";
	$tVars->SUB_DESCRIPTION = $e107->tp->toHTML($subInfo['forum_description'], false, 'no_hook');
	$tVars->SUB_THREADS = $subInfo['forum_threads'];
	$tVars->SUB_REPLIES = $subInfo['forum_replies'];
	if(USER && is_array($newflag_list) && in_array($subInfo['forum_id'], $newflag_list))
	{

		$tVars->NEWFLAG = "<a href='".$e107->url->getUrl('forum','forum', 'func=mfar&id='.$subInfo['forum_id'])."'>".IMAGE_new.'</a>';
	}
	else
	{
		$tVars->NEWFLAG = IMAGE_nonew;
	}

	if($subInfo['forum_lastpost_info'])
	{
		$tmp = explode('.', $subInfo['forum_lastpost_info']);
		$lp_thread = "<a href='".$e107->url->getUrl('forum', 'thread', array('func' => 'last', 'id' => $tmp[1]))."'>".IMAGE_post2.'</a>';
		$lp_date = $gen->convert_date($tmp[0], 'forum');

		if($subInfo['user_name'])
		{
			$lp_name = "<a href='".$e107->url->getUrl('core:user', 'main', "func=profile&id={$subInfo['forum_lastpost_user']}")."'>{$subInfo['user_name']}</a>";
		}
		else
		{
			$lp_name = $subInfo['forum_lastpost_user_anon'];
		}
		$tVars->SUB_LASTPOST = $lp_date.'<br />'.$lp_name.' '.$lp_thread;
	}
	else
	{
		$tVars->SUB_LASTPOST = '-';
	}
	return $tp->simpleParse($FORUM_VIEW_SUB, $tVars);
}

function forumjump()
{
	global $forum;
	$jumpList = $forum->forumGetAllowed('view');
	$text = "<form method='post' action='".e_SELF."'><p>".LAN_403.": <select name='forumjump' class='tbox'>";
	foreach($jumpList as $key => $val)
	{
		$text .= "\n<option value='".$key."'>".$val."</option>";
	}
	$text .= "</select> <input class='button' type='submit' name='fjsubmit' value='".LAN_03."' />&nbsp;&nbsp;&nbsp;&nbsp;<a href='".e_SELF."?".$_SERVER['QUERY_STRING']."#top'>".LAN_02."</a></p></form>";
	return $text;
}

?>