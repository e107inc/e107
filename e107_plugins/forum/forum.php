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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/forum/forum.php,v $
|     $Revision: 1.21 $
|     $Date: 2005-05-16 01:33:05 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");

@include_once e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum.php';
@include_once e_PLUGIN.'forum/languages/English/lan_forum.php';

require_once(e_PLUGIN.'forum/forum_class.php');
$forum = new e107forum;

if (strstr(e_QUERY, "untrack"))
{
	$tmp1 = explode(".", e_QUERY);
	$tmp = str_replace("-".$tmp1[1]."-", "", USERREALM);
	$sql->db_Update("user", "user_realm='$tmp' WHERE user_id='".USERID."' ");
	header("location:".e_SELF."?track");
	exit;
}

//Mark all threads as read
if (e_QUERY == "mark.all.as.read")
{
	$forum->forum_markasread('all');
	header("location:".e_SELF);
	exit;
}

//Mark all threads in specific forum as read
if (strstr(e_QUERY, 'mfar'))
{
	$tmp = explode(".", e_QUERY);
	$forum_id = intval($tmp[1]);
	$forum->forum_markasread($forum_id);
	header("location:".e_SELF);
	exit;
}

if (e_QUERY == 'rules')
{
	include_once(HEADERF);
	forum_rules('show');
	include_once(FOOTERF);
	exit;
}

$gen = new convert;

$FORUMTITLE = LAN_46;
$THREADTITLE = LAN_47;
$REPLYTITLE = LAN_48;
$LASTPOSTITLE = LAN_49;
$INFOTITLE = LAN_191;
$LOGO = IMAGE_e;
$NEWTHREADTITLE = LAN_424;
$POSTEDTITLE = LAN_423;
$NEWIMAGE = IMAGE_new_small;
$TRACKTITLE = LAN_397;

$rules_text = forum_rules('check');
$USERINFO = "<a href='".e_BASE."top.php?0.top.forum.10'>".LAN_429."</a> | <a href='".e_BASE."top.php?0.active'>".LAN_430."</a>";
if(USER)
{
	$USERINFO .= " | <a href='".e_BASE."userposts.php?0.forums.".USERID."'>".LAN_431."</a> | <a href='".e_BASE."usersettings.php'>".LAN_432."</a> | <a href='".e_BASE."user.php?id.".USERID."'>".LAN_435."</a>";
	if($pref['forum_attach'] && (check_class($pref['upload_class']) || getperms('0')))
	{
		$USERINFO .= " | <a href='".e_PLUGIN."forum/forum_uploads.php'>".FORLAN_442."</a>";
	}
}
if($rules_text != '')
{
	$USERINFO .= " | <a href='".e_PLUGIN."forum/forum.php?rules'>".LAN_433."</a>";
}
$total_topics = $sql->db_Count("forum_t", "(*)", " WHERE thread_parent='0' ");
$total_replies = $sql->db_Count("forum_t", "(*)", " WHERE thread_parent!='0' ");
$total_members = $sql->db_Count("user");
$newest_member = $sql->db_Select("user", "*", "ORDER BY user_join DESC LIMIT 0,1", $mode = "no_where");
list($nuser_id, $nuser_name) = $sql->db_Fetch();
if(!defined("e_TRACKING_DISABLED"))
{
	$member_users = $sql->db_Select("online", "*", "online_location REGEXP('forum.php') AND online_user_id!='0' ");
	$guest_users = $sql->db_Select("online", "*", "online_location REGEXP('forum.php') AND online_user_id='0' ");
	$users = $member_users+$guest_users;
	$USERLIST = LAN_426;
	global $listuserson;
	$c = 0;
	if(is_array($listuserson))
	{
	foreach($listuserson as $uinfo => $pinfo)
	{
		list($oid, $oname) = explode(".", $uinfo, 2);
		$c ++;
		$USERLIST .= "<a href='".e_BASE."user.php?id.$oid'>$oname</a>".($c == MEMBERS_ONLINE ? "." :", ");
	}
	}
	$USERLIST .= "<br /><a rel='external' href='".e_BASE."online.php'>".LAN_427."</a> ".LAN_436;
}
$STATLINK = "<a href='".e_PLUGIN."forum/forum_stats.php'>".LAN_441."</a>\n";
$ICONKEY = "
<table style='width:100%'>\n<tr>
<td style='width:2%'>".IMAGE_new_small."</td>
<td style='width:10%'><span class='smallblacktext'>".LAN_79."</span></td>
<td style='width:2%'>".IMAGE_nonew_small."</td>
<td style='width:10%'><span class='smallblacktext'>".LAN_80."</span></td>
<td style='width:2%'>".IMAGE_closed_small."</td>
<td style='width:10%'><span class='smallblacktext'>".LAN_394."</span></td>
</tr>\n</table>\n";

$SEARCH = "
<form method='get' action='".e_BASE."search.php'>
<p>
<input class='tbox' type='text' name='q' size='20' value='' maxlength='50' />
<input type='hidden' name='r' value='0' />
<input class='button' type='submit' name='s' value='".LAN_180."' />
</p>
</form>\n";

$PERMS = (USER == TRUE || ANON == TRUE ? LAN_204." - ".LAN_206." - ".LAN_208 : LAN_205." - ".LAN_207." - ".LAN_209);

$INFO = "";
if (USER == TRUE)
{
	$total_new_threads = $sql->db_Count('forum_t', '(*)', "WHERE thread_datestamp>'".USERLV."' ");
	if (USERVIEWED != "")
	{
		$tmp = explode("..", USERVIEWED);
		$total_read_threads = count($tmp);
	}
	else
	{
		$total_read_threads = 0;
	}

	$INFO = LAN_30." ".USERNAME."<br />";
	$lastvisit_datestamp = $gen->convert_date(USERLV, 'long');
	$datestamp = $gen->convert_date(time(), "long");
	if (!$total_new_threads)
	{
		$INFO .= LAN_31;
	}
	elseif($total_new_threads == 1)
	{
		$INFO .= LAN_32;
	}
	else
	{
		$INFO .= LAN_33." ".$total_new_threads." ".LAN_34." ";
	}
	$INFO .= LAN_35;
	if ($total_new_threads == $total_read_threads && $total_new_threads != 0 && $total_read_threads >= $total_new_threads)
	{
		$INFO .= LAN_198;
		$allread = TRUE;
	}
	elseif($total_read_threads != 0)
	{
		$INFO .= " (".LAN_196.$total_read_threads.LAN_197.")";
	}

	$INFO .= "<br />
	".LAN_36." ".$lastvisit_datestamp."<br />
	".LAN_37." ".$datestamp.LAN_38.$pref['timezone'];
}
else
{
	$INFO .= "";
	if (ANON == TRUE)
	{
		$INFO .= LAN_410."<br />".LAN_44." <a href='".e_SIGNUP."'>".LAN_437."</a> ".LAN_438;
	}
	elseif(USER == FALSE)
	{
		$INFO .= LAN_410."<br />".LAN_45." <a href='".e_SIGNUP."'>".LAN_439."</a> ".LAN_440;
	}
}

if (USER && $allread != TRUE && $total_new_threads && $total_new_threads >= $total_read_threads)
{
	$INFO .= "<br /><a href='".e_SELF."?mark.all.as.read'>".LAN_199."</a>".(e_QUERY != "new" ? ", <a href='".e_SELF."?new'>".LAN_421."</a>" : "");
}

if (USER && USERREALM && e_QUERY != "track")
{
	$INFO .= "<br /><a href='".e_SELF."?track'>".LAN_393."</a>";
}

$FORUMINFO = LAN_192.($total_topics+$total_replies)." ".LAN_404." ($total_topics ".($total_topics == 1 ? LAN_411 : LAN_413).", $total_replies ".($total_replies == 1 ? LAN_412 : LAN_414).").".(!defined("e_TRACKING_DISABLED") ? "" : "<br />".$users." ".($users == 1 ? LAN_415 : LAN_416)." (".$member_users." ".($member_users == 1 ? LAN_417 : LAN_419).", ".$guest_users." ".($guest_users == 1 ? LAN_418 : LAN_420).")<br />".LAN_42.$total_members."<br />".LAN_41."<a href='".e_BASE."user.php?id.".$nuser_id."'>".$nuser_name."</a>.\n");

if (!isset($FORUM_MAIN_START))
{
	if (file_exists(THEME."forum_template.php"))
	{
		include_once(THEME."forum_template.php");
	}
}
include(e_PLUGIN."forum/templates/forum_template.php");
require_once(HEADERF);

$parent_list = $forum->forum_getparents();
$forum_list = $forum->forum_getforums();
$newflag_list = $forum->forum_newflag_list();

if (!$parent_list)
{
	$ns->tablerender(PAGE_NAME, "<div style='text-align:center'>".LAN_51."</div>");
	require_once(FOOTERF);
	exit;
}

$forum_string = "";
foreach ($parent_list as $parent) {
	$status = parse_parent($parent);
	$PARENTSTATUS = $status[0];
	if ($status[1]) {
		$PARENTNAME = $parent['forum_name'];
		$forum_string .= preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_MAIN_PARENT);
		//		$forums = $forum->forum_getforums($parent['forum_id']);
		if (!count($forum_list[$parent['forum_id']]))
		{
			$text .= "<td colspan='5' style='text-align:center' class='forumheader3'>".LAN_52."</td>";
		}
		else
		{
			foreach($forum_list[$parent['forum_id']] as $f)
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
				$forum_string .= preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_MAIN_PARENT_END);
			}
		}
	}
}

function parse_parent($parent)
{

	if(check_class($parent['forum_class']))
	{
		$status[0]="";
		$status[1] = TRUE;
		if(!check_class($parent['forum_postclass']))
		{
			$status[0] = "( ".LAN_405." )";
		}
	}
	else
	{
		$status[1] = FALSE;
	}
	return $status;
}

function parse_forum($f, $restricted_string = "")
{
	global $FORUM_MAIN_FORUM, $gen, $forum, $tp, $newflag_list;

	if(USER && is_array($newflag_list) && in_array($f['forum_id'], $newflag_list))
	{
		$NEWFLAG = "<a href='".e_SELF."?mfar.{$f['forum_id']}'>".IMAGE_new."</a></td>";
	}
	else
	{
		$NEWFLAG = IMAGE_nonew;
	}

	$f['forum_name'] = $tp -> toHTML($f['forum_name'], TRUE);
	$f['forum_description'] = $tp -> toHTML($f['forum_description'], TRUE);

	$FORUMNAME = "<a href='".e_PLUGIN."forum/forum_viewforum.php?{$f['forum_id']}'>{$f['forum_name']}</a>";
	$FORUMDESCRIPTION = $f['forum_description'].($restricted_string ? "<br /><span class='smalltext'><i>$restricted_string</i></span>" : "");
	$THREADS = $f['forum_threads'];
	$REPLIES = $f['forum_replies'];


	if ($f['forum_lastpost_user'])
	{
		list($lastpost_datestamp, $lastpost_thread) = explode(chr(1), $f['forum_lastpost_info']);
		if ($f['user_name'])
		{
			$lastpost_name = "<a href='".e_BASE."user.php?id.{$f['forum_lastpost_user']}'>{$f['user_name']}</a>";
		}
		else
		{
			if(substr($f['forum_lastpost_user'], 0, 2) != "0.")
			{
				$lastpost_name = FORLAN_443;
			}
			else
			{
				$lastpost_name = $tp->toHTML(substr($f['forum_lastpost_user'], 2));
			}
		}
		$lastpost_datestamp = $gen->convert_date($lastpost_datestamp, 'forum');
		$LASTPOST = $lastpost_datestamp.'<br />'.$lastpost_name."<a href='".e_PLUGIN."forum/forum_viewtopic.php?{$lastpost_thread}.last'>".IMAGE_post2.'</a>';
	}
	else
	{
		$LASTPOST = "-";
	}
	return(preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_MAIN_FORUM));
}

if (e_QUERY == "track")
{
	$sql2 = new db;
	$tmp = explode("-", USERREALM);
	foreach($tmp as $key => $value)
	{
		if ($value)
		{
			if($sql->db_Select("forum_t", "thread_id, thread_datestamp, thread_name", "thread_id='".$value."' "))
			{
				$row = $sql->db_Fetch();
				//extract($row);
				$NEWIMAGE = IMAGE_nonew_small;
				if ($row['thread_datestamp'] > USERLV && (!ereg("\.".$row['thread_id']."\.", USERVIEWED)))
				{
					$NEWIMAGE = IMAGE_new_small;
				}
				elseif($sql2->db_SELECT("forum_t", "thread_id", "thread_parent='{$row['thread_id']}' AND thread_datestamp > '".USERLV."' "))
				{
					while ($xrow = $sql2->db_Fetch())
					{
						if (!ereg("\.".$xrow['thread_id']."\.", USERVIEWED))
						{
							$NEWIMAGE = IMAGE_new_small;
						}
					}
				}
				$result = preg_split("/\]/", $row['thread_name']);
				$TRACKPOSTNAME = (($result[1] && $row['thread_name']{0} == "[") ? $result[0]."] <a href='".e_PLUGIN."forum/forum_viewtopic.php?{$row['thread_id']}'>".ereg_replace("\[.*\]", "", $row['thread_name'])."</a>" : "<a href='".e_PLUGIN."forum/forum_viewtopic.php?{$row['thread_id']}'>".$row['thread_name']."</a>");
				$UNTRACK = "<a href='".e_SELF."?untrack.".$row['thread_id']."'>".LAN_392."</a>";
				$forum_trackstring .= preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_TRACK_MAIN);
			}
		}
	}

	$forum_track_start = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_TRACK_START);
	$forum_track_end = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_TRACK_END);

	if ($pref['forum_enclose'])
	{
		$ns->tablerender($pref['forum_title'], $forum_track_start.$forum_trackstring.$forum_track_end);
	}
	else
	{
		echo $forum_track_start.$forum_trackstring.$forum_track_end;
	}
}

if (e_QUERY == "new")
{
	$newpostList = $forum->post_getnew(5);
	foreach($newpostList as $post)
	{
		if (!ereg("\.".$thread_id."\.", USERVIEWED))
		{
			$author_name = $forum->thread_user($post);
			$author_id = $post['thread_user'];
			$datestamp = $gen->convert_date($post['thread_datestamp'], "forum");
			if($author_id == 0)
			{
				$STARTERTITLE = $author_name."<br />".$datestamp;
			}
			else
			{
				$STARTERTITLE = "<a href='".e_BASE."user.php?id.$author_id'>$author_name</a><br />".$datestamp;
			}

			$NEWSPOSTNAME = "<a href='".e_PLUGIN."forum/forum_viewtopic.php?{$post['thread_id']}.post'>".LAN_425.$post['post_subject']."</a>";
			$forum_newstring .= preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_NEWPOSTS_MAIN);
		}
	}

	if (!$newpostList)
	{
		$NEWSPOSTNAME = LAN_198;
		$forum_newstring = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_NEWPOSTS_MAIN);
	}
	$forum_new_start = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_NEWPOSTS_START);
	$forum_new_end = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_NEWPOSTS_END);

	if ($pref['forum_enclose'])
	{
		$ns->tablerender($pref['forum_title'], $forum_new_start.$forum_newstring.$forum_new_end);
	}
	else
	{
		echo $forum_new_start.$forum_newstring.$forum_new_end;
	}
}

$forum_main_start = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_MAIN_START);
$forum_main_end = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_MAIN_END);
if ($pref['forum_enclose'])
{
	$ns->tablerender($pref['forum_title'], $forum_main_start.$forum_string.$forum_main_end);
}
else
{
	echo $forum_main_start.$forum_string.$forum_main_end;
}
require_once(FOOTERF);

function forum_rules($action = 'check')
{
	global $tp, $sql, $ns;
	if (ADMIN == TRUE)
	{
		$type = 'forum_rules_admin';
	}
	elseif(USER == TRUE)
	{
		$type = 'forum_rules_member';
	}
	else
	{
		$type = 'forum_rules_guest';
	}
	$result = $sql->db_Select('generic', 'gen_chardata', "gen_type = '$type' AND gen_intdata = 1");
	if ($action == 'check')
	{
		if ($result)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	if ($result)
	{
		$row = $sql->db_Fetch();
		$rules_text = $tp->toHTML($row['gen_chardata'], TRUE);
	}
	else
	{
		$rules_text = FORLAN_441;
	}
	$ns->tablerender(LAN_433, "<div style='text-align:center'>{$rules_text}</div>", 'forum_rules');
}
?>