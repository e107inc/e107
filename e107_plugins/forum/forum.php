<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum main page
 *
 * $URL$
 * $Id$
 *
*/
if(!defined('e107_INIT'))
{
	require_once('../../class2.php');
}

include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum.php');

require_once(e_PLUGIN.'forum/forum_class.php');
$forum = new e107forum;

if ($untrackId = varset($_REQUEST['untrack']))
{
	$forum->track('del', USERID, $untrackId);
	header('location:'.$e107->url->getUrl('forum', 'thread', array('func' => 'track')));
	exit;
}

if(isset($_GET['f']))
{
	if(isset($_GET['id']))
	{
		$id = (int)$_GET['id'];
	}

	switch($_GET['f'])
	{
		case 'mfar':
			$forum->forumMarkAsRead($id);
			header('location:'.e_SELF);
			exit;
			break;

		case 'rules':
			include_once(HEADERF);
			forum_rules('show');
			include_once(FOOTERF);
			exit;
			break;
	}
}
$fVars = new e_vars;
$gen = new convert;

$fVars->FORUMTITLE = LAN_46;
$fVars->THREADTITLE = LAN_47;
$fVars->REPLYTITLE = LAN_48;
$fVars->LASTPOSTITLE = LAN_49;
$fVars->INFOTITLE = LAN_191;
$fVars->LOGO = IMAGE_e;
$fVars->NEWTHREADTITLE = LAN_424;
$fVars->POSTEDTITLE = LAN_423;
$fVars->NEWIMAGE = IMAGE_new_small;
$fVars->TRACKTITLE = LAN_397;

$rules_text = forum_rules('check');
$fVars->USERINFO = "<a href='".e_BASE."top.php?0.top.forum.10'>".LAN_429."</a> | <a href='".e_BASE."top.php?0.active'>".LAN_430."</a>";
if(USER)
{
	$fVars->USERINFO .= " | <a href='".e_BASE.'userposts.php?0.forums.'.USERID."'>".LAN_431."</a> | <a href='".e_BASE."usersettings.php'>".LAN_432."</a> | <a href='".e_BASE."user.php?id.".USERID."'>".LAN_435."</a>";
	if($forum->prefs->get('attach') && (check_class($pref['upload_class']) || getperms('0')))
	{
		$fVars->USERINFO .= " | <a href='".e_PLUGIN."forum/forum_uploads.php'>".FORLAN_442."</a>";
	}
}
if($rules_text != '')
{
	$fVars->USERINFO .= " | <a href='".$e107->url->getUrl('forum', 'forum', "func=rules")."'>".LAN_433.'</a>';
}
$total_topics = $sql->db_Count("forum_t", "(*)", " WHERE thread_parent='0' ");
$total_replies = $sql->db_Count("forum_t", "(*)", " WHERE thread_parent!='0' ");
$total_members = $sql->db_Count("user");
$newest_member = $sql->db_Select("user", "*", "user_ban='0' ORDER BY user_join DESC LIMIT 0,1");
list($nuser_id, $nuser_name) = $sql->db_Fetch();
if(!defined('e_TRACKING_DISABLED'))
{
	$member_users = $sql->db_Select("online", "*", "online_location REGEXP('forum.php') AND online_user_id!='0' ");
	$guest_users = $sql->db_Select("online", "*", "online_location REGEXP('forum.php') AND online_user_id='0' ");
	$users = $member_users+$guest_users;
	$fVars->USERLIST = LAN_426;
	global $listuserson;
	$c = 0;
	if(is_array($listuserson))
	{
	foreach($listuserson as $uinfo => $pinfo)
	{
		list($oid, $oname) = explode(".", $uinfo, 2);
		$c ++;
		$fVars->USERLIST .= "<a href='".e_BASE."user.php?id.$oid'>$oname</a>".($c == MEMBERS_ONLINE ? "." :", ");
	}
	}
	$fVars->USERLIST .= "<br /><a rel='external' href='".e_BASE."online.php'>".LAN_427."</a> ".LAN_436;
}
$fVars->STATLINK = "<a href='".e_PLUGIN."forum/forum_stats.php'>".LAN_441."</a>\n";
$fVars->ICONKEY = "
<table style='width:100%'>\n<tr>
<td style='width:2%'>".IMAGE_new_small."</td>
<td style='width:10%'><span class='smallblacktext'>".LAN_79."</span></td>
<td style='width:2%'>".IMAGE_nonew_small."</td>
<td style='width:10%'><span class='smallblacktext'>".LAN_80."</span></td>
<td style='width:2%'>".IMAGE_closed_small."</td>
<td style='width:10%'><span class='smallblacktext'>".LAN_394."</span></td>
</tr>\n</table>\n";

$fVars->SEARCH = "
<form method='get' action='".e_BASE."search.php'>
<p>
<input class='tbox' type='text' name='q' size='20' value='' maxlength='50' />
<input type='hidden' name='r' value='0' />
<input type='hidden' name='ref' value='forum' />
<input class='button' type='submit' name='s' value='".LAN_180."' />
</p>
</form>\n";

$fVars->PERMS = (USER == TRUE || ANON == TRUE ? LAN_204." - ".LAN_206." - ".LAN_208 : LAN_205." - ".LAN_207." - ".LAN_209);

$fVars->INFO = "";
if (USER == TRUE)
{
	$total_new_threads = $sql->db_Count('forum_t', '(*)', "WHERE thread_datestamp>'".USERLV."' ");
	if (USERVIEWED != "")
	{
		$tmp = explode(".", USERVIEWED);		// List of numbers, separated by single period
		$total_read_threads = count($tmp);
	}
	else
	{
		$total_read_threads = 0;
	}

	$fVars->INFO = LAN_30." ".USERNAME."<br />";
	$lastvisit_datestamp = $gen->convert_date(USERLV, 'long');
	$datestamp = $gen->convert_date(time(), "long");
	if (!$total_new_threads)
	{
		$fVars->INFO .= LAN_31;
	}
	elseif($total_new_threads == 1)
	{
		$fVars->INFO .= LAN_32;
	}
	else
	{
		$fVars->INFO .= LAN_33." ".$total_new_threads." ".LAN_34." ";
	}
	$fVars->INFO .= LAN_35;
	if ($total_new_threads == $total_read_threads && $total_new_threads != 0 && $total_read_threads >= $total_new_threads)
	{
		$fVars->INFO .= LAN_198;
		$allread = TRUE;
	}
	elseif($total_read_threads != 0)
	{
		$fVars->INFO .= " (".LAN_196.$total_read_threads.LAN_197.")";
	}

	$fVars->INFO .= "<br />
	".LAN_36." ".$lastvisit_datestamp."<br />
	".LAN_37." ".$datestamp;
}
else
{
	$fVars->INFO .= '';
	if (ANON == TRUE)
	{
		$fVars->INFO .= LAN_410.'<br />'.LAN_44." <a href='".e_SIGNUP."'>".LAN_437."</a> ".LAN_438;
	}
	elseif(USER == FALSE)
	{
		$fVars->INFO .= LAN_410.'<br />'.LAN_45." <a href='".e_SIGNUP."'>".LAN_439."</a> ".LAN_440;
	}
}

if (USER && $allread != TRUE && $total_new_threads && $total_new_threads >= $total_read_threads)
{
	$fVars->INFO .= "<br /><a href='".e_SELF."?mark.all.as.read'>".LAN_199.'</a>'.(e_QUERY != 'new' ? ", <a href='".e_SELF."?new'>".LAN_421."</a>" : '');
}

if (USER && varsettrue($forum->prefs->get('track')) && e_QUERY != 'track')
{
	$fVars->INFO .= "<br /><a href='".e_SELF."?track'>".LAN_393.'</a>';
}

$fVars->FORUMINFO = LAN_192.($total_topics+$total_replies).' '.LAN_404." ($total_topics ".($total_topics == 1 ? LAN_411 : LAN_413).", $total_replies ".($total_replies == 1 ? LAN_412 : LAN_414).").".(!defined("e_TRACKING_DISABLED") ? "" : "<br />".$users." ".($users == 1 ? LAN_415 : LAN_416)." (".$member_users." ".($member_users == 1 ? LAN_417 : LAN_419).", ".$guest_users." ".($guest_users == 1 ? LAN_418 : LAN_420).")<br />".LAN_42.$total_members."<br />".LAN_41."<a href='".e_BASE."user.php?id.".$nuser_id."'>".$nuser_name."</a>.\n");

if (!isset($FORUM_MAIN_START))
{
	if (file_exists(THEME.'forum_template.php'))
	{
		include_once(THEME.'forum_template.php');
	}
}
include(e_PLUGIN.'forum/templates/forum_template.php');
require_once(HEADERF);

$forumList = $forum->forumGetForumList();
$newflag_list = $forum->forumGetUnreadForums();

if (!$forumList)
{
	$ns->tablerender(PAGE_NAME, "<div style='text-align:center'>".LAN_51.'</div>', array('forum', '51'));
	require_once(FOOTERF);
	exit;
}

$forum_string = '';
$pVars = new e_vars;
foreach ($forumList['parents'] as $parent)
{
	$status = parse_parent($parent);
	$pVars->PARENTSTATUS = $status;

	$pVars->PARENTNAME = $parent['forum_name'];
	$forum_string .= $tp->simpleParse($FORUM_MAIN_PARENT, $pVars);
	if (!count($forumList['forums'][$parent['forum_id']]))
	{
		$text .= "<td colspan='5' style='text-align:center' class='forumheader3'>".LAN_52."</td>";
	}
	else
	{
//TODO: Rework the restricted string
		foreach($forumList['forums'][$parent['forum_id']] as $f)
		{
			if ($f['forum_class'] == e_UC_ADMIN && ADMIN)
			{
				$forum_string .= parse_forum($f, LAN_406);
			}
			elseif($f['forum_class'] == e_UC_MEMBER && USER)
			{
				$forum_string .= parse_forum($f, LAN_407);
			}
			elseif($f['forum_class'] == e_UC_READONLY)
			{
				$forum_string .= parse_forum($f, LAN_408);
			}
			elseif($f['forum_class'] && check_class($f['forum_class']))
			{
				$forum_string .= parse_forum($f, LAN_409);
			}
			elseif(!$f['forum_class'])
			{
				$forum_string .= parse_forum($f);
			}
		}
		if (isset($FORUM_MAIN_PARENT_END))
		{
			$forum_string .= $tp->simpleParse($FORUM_MAIN_PARENT_END, $pVars);
		}
	}
}

function parse_parent($parent)
{
	if(!check_class($parent['forum_postclass']))
	{
		$status = '( '.LAN_405.' )';
	}
	return $status;
}

function parse_forum($f, $restricted_string = '')
{
	global $FORUM_MAIN_FORUM, $gen, $forum, $newflag_list, $forumList;
	$fVars = new e_vars;
	$e107 = e107::getInstance();

	if(USER && is_array($newflag_list) && in_array($f['forum_id'], $newflag_list))
	{

		$fVars->NEWFLAG = "<a href='".$e107->url->getUrl('forum','forum', 'func=mfar&id='.$f['forum_id'])."'>".IMAGE_new.'</a>';
	}
	else
	{
		$fVars->NEWFLAG = IMAGE_nonew;
	}

	if(substr($f['forum_name'], 0, 1) == '*')
	{
		$f['forum_name'] = substr($f['forum_name'], 1);
	}
	$f['forum_name'] = $e107->tp->toHTML($f['forum_name'], true, 'no_hook');
	$f['forum_description'] = $e107->tp->toHTML($f['forum_description'], true, 'no_hook');

	$fVars->FORUMNAME = "<a href='".$e107->url->getUrl('forum', 'forum', "func=view&id={$f['forum_id']}")."'>{$f['forum_name']}</a>";
	$fVars->FORUMDESCRIPTION = $f['forum_description'].($restricted_string ? "<br /><span class='smalltext'><i>$restricted_string</i></span>" : "");
	$fVars->THREADS = $f['forum_threads'];
	$fVars->REPLIES = $f['forum_replies'];
	$fVars->FORUMSUBFORUMS = '';

	if(is_array($forumList['subs'][$f['forum_id']]))
	{
		list($lastpost_datestamp, $lastpost_thread) = explode('.', $f['forum_lastpost_info']);
		$ret = parse_subs($forumList['subs'][$f['forum_id']], $lastpost_datestamp);
		$fVars->FORUMSUBFORUMS = "<br /><div class='smalltext'>".FORLAN_444.": {$ret['text']}</div>";
		$fVars->THREADS += $ret['threads'];
		$fVars->REPLIES += $ret['replies'];
		if(isset($ret['lastpost_info']))
		{
			$f['forum_lastpost_info'] = $ret['lastpost_info'];
			$f['forum_lastpost_user'] = $ret['lastpost_user'];
			$f['forum_lastpost_user_anon'] = $ret['lastpost_user_anon'];
			$f['user_name'] = $ret['user_name'];
		}
	}

	if ($f['forum_lastpost_info'])
	{
		list($lastpost_datestamp, $lastpost_thread) = explode('.', $f['forum_lastpost_info']);
		if ($f['user_name'])
		{

			$lastpost_name = "<a href='".$e107->url->getUrl('core:user','main','func=profile&id='.$f['forum_lastpost_user'])."'>{$f['user_name']}</a>";
		}
		else
		{
			$lastpost_name = $e107->tp->toHTML($f['forum_lastpost_user_anon']);
		}
		$lastpost_datestamp = $gen->convert_date($lastpost_datestamp, 'forum');
		$fVars->LASTPOST = $lastpost_datestamp.'<br />'.$lastpost_name." <a href='".$e107->url->getUrl('forum', 'thread', array('func' => 'last', 'id' => $lastpost_thread))."'>".IMAGE_post2.'</a>';
	}
	else
	{
		$fVars->LASTPOST = '-';
	}
	return $e107->tp->simpleParse($FORUM_MAIN_FORUM, $fVars);
}

function parse_subs($subList, $lastpost_datestamp)
{
	$e107 = e107::getInstance();
	$ret = array();
	$ret['text'] = '';
	foreach($subList as $sub)
	{
		$ret['text'] .= ($ret['text'] ? ', ' : '');
		$suburl = $e107->url->getUrl('forum', 'forum', array('func' => 'view', 'id' => $sub['forum_id']));
		$ret['text'] .= "<a href='{$suburl}'>".$e107->tp->toHTML($sub['forum_name']).'</a>';
		$ret['threads'] += $sub['forum_threads'];
		$ret['replies'] += $sub['forum_replies'];
		$tmp = explode('.', $sub['forum_lastpost_info']);
		if($tmp[0] > $lastpost_datestamp)
		{
			$ret['lastpost_info'] = $sub['forum_lastpost_info'];
			$ret['lastpost_user'] = $sub['forum_lastpost_user'];
			$ret['lastpost_user_anon'] = $sub['lastpost_user_anon'];
			$ret['user_name'] = $sub['user_name'];
			$lastpost_datestamp = $tmp[0];
		}
	}
	return $ret;
}

if (e_QUERY == 'track')
{
	if($trackedThreadList = $forum->getTrackedThreadList(USERID, 'list'))
	{
		$trackVars = new e_vars;
		$viewed = $forum->threadGetUserViewed();
		$qry = "
		SELECT t.*, p.* from `#forum_thread` AS t
		LEFT JOIN `#forum_post` AS p ON p.post_thread = t.thread_id AND p.post_datestamp = t.thread_datestamp
		WHERE thread_id IN({$trackedThreadList})
		ORDER BY thread_lastpost DESC
		";
		if($e107->sql->db_Select_gen($qry))
		{
			while($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
			{
				$trackVars->NEWIMAGE = IMAGE_nonew_small;
				if ($row['thread_datestamp'] > USERLV && !in_array($row['thread_id'], $viewed))
				{
					$trackVars->NEWIMAGE = IMAGE_new_small;
				}

				$url = $e107->url->getUrl('forum', 'thread', "func=view&id={$row['thread_id']}");
				$trackVars->TRACKPOSTNAME = "<a href='{$url}'>".$e107->tp->toHTML($row['thread_name']).'</a>';
				$trackVars->UNTRACK = "<a href='".e_SELF."?untrack.".$row['thread_id']."'>".LAN_392."</a>";
				$forum_trackstring .= $e107->tp->simpleParse($FORUM_TRACK_MAIN, $trackVars);
			}
		}
		$forum_track_start = $e107->tp->simpleParse($FORUM_TRACK_START, $trackVars);
		$forum_track_end = $e107->tp->simpleParse($FORUM_TRACK_END, $trackVars);
		if ($forum->prefs->get('enclose'))
		{
			$ns->tablerender($forum->prefs->get('title'), $forum_track_start.$forum_trackstring.$forum_track_end, array('forum', 'main1'));
		}
		else
		{
			echo $forum_track_start.$forum_trackstring.$forum_track_end;
		}
	}
}

if (e_QUERY == 'new')
{
	$nVars = new e_vars;
	$newThreadList = $forum->threadGetNew(10);
	foreach($newThreadList as $thread)
	{
		$author_name = ($thread['user_name'] ? $thread['user_name'] : $thread['lastuser_anon']);

		$datestamp = $gen->convert_date($thread['thread_lastpost'], 'forum');
		if(!$thread['user_name'])
		{
			$nVars->STARTERTITLE = $author_name.'<br />'.$datestamp;
		}
		else
		{
			$nVars->STARTERTITLE = "<a href='".$e107->url->getUrl('core:user', 'main', 'func=profile&id='.$thread['thread_lastuser'])."'>{$author_name}</a><br />".$datestamp;
		}
		$nVars->NEWSPOSTNAME = "<a href='".$e107->url->getUrl('forum', 'thread', 'func=last&id='.$thread['thread_id'])."'>".$e107->tp->toHTML($thread['thread_name'], TRUE, 'no_make_clickable, no_hook').'</a>';

		$forum_newstring .= $e107->tp->simpleParse($FORUM_NEWPOSTS_MAIN, $nVars);
	}

	if (!$newThreadList)
	{
		$nVars->NEWSPOSTNAME = LAN_198;
		$forum_newstring = $e107->tp->simpleParse($FORUM_NEWPOSTS_MAIN, $nVars);

	}
	$forum_new_start = $e107->tp->simpleParse($FORUM_NEWPOSTS_START, $nVars);
	$forum_new_end = $e107->tp->simpleParse($FORUM_NEWPOSTS_END, $nVars);

	if ($forum->prefs->get('enclose'))
	{
		$ns->tablerender($forum->prefs->get('title'), $forum_new_start.$forum_newstring.$forum_new_end, array('forum', 'main2'));
	}
	else
	{
		echo $forum_new_start.$forum_newstring.$forum_new_end;
	}
}

$forum_main_start = $e107->tp->simpleParse($FORUM_MAIN_START, $fVars);
$forum_main_end = $e107->tp->simpleParse($FORUM_MAIN_END, $fVars);

if ($forum->prefs->get('enclose'))
{
	$ns->tablerender($forum->prefs->get('title'), $forum_main_start.$forum_string.$forum_main_end, array('forum', 'main3'));
}
else
{
	echo $forum_main_start.$forum_string.$forum_main_end;
}
require_once(FOOTERF);

function forum_rules($action = 'check')
{
	$e107 = e107::getInstance();
	if (ADMIN == true)
	{
		$type = 'forum_rules_admin';
	}
	elseif(USER == true)
	{
		$type = 'forum_rules_member';
	}
	else
	{
		$type = 'forum_rules_guest';
	}
	$result = $e107->sql->db_Select('generic', 'gen_chardata', "gen_type = '$type' AND gen_intdata = 1");
	if ($action == 'check') { return $result; }

	if ($result)
	{
		$row = $e107->sql->db_Fetch();
		$rules_text = $e107->tp->toHTML($row['gen_chardata'], true);
	}
	else
	{
		$rules_text = FORLAN_441;
	}
	$e107->ns->tablerender(LAN_433, "<div style='text-align:center'>{$rules_text}</div>", array('forum', 'forum_rules'));
}
?>