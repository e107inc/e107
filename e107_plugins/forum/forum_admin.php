<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $URL$
 * $Id$
 */

$eplug_admin = true;
require_once('../../class2.php');
include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_admin.php');

if (!getperms('P'))
{
	header('location:'.e_BASE.'index.php');
	exit;
}
$e_sub_cat = 'forum';

require_once(e_ADMIN.'auth.php');
require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER.'form_handler.php');
require_once(e_HANDLER.'ren_help.php');
require_once(e_PLUGIN.'forum/forum_class.php');
require_once(e_PLUGIN.'forum/forum_admin_class.php');
$emessage = eMessage::getInstance();
$rs = new form;
$for = new e107forum;
$forum = new forumAdmin;

$fPref = e107::getPlugConfig('forum', '', false);


define('IMAGE_new', "<img src='".img_path('new.png')."' alt='' />");
define('IMAGE_sub', "<img src='".e_PLUGIN."forum/images/forums_16.png' alt='".FORLAN_145."' title='".FORLAN_145."' />");
define('IMAGE_nosub', "<img src='".e_PLUGIN."forum/images/sub_forums_16.png' alt='".FORLAN_145."' title='".FORLAN_145."' />");

$deltest = array_flip($_POST);
if (e_QUERY)
{
	$tmp = explode('.', e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	$id = $tmp[2];
	unset($tmp);
}

if(isset($_POST['delete']))
{
	$tmp = array_pop(array_flip($_POST['delete']));
	list($delete, $del_id) = explode('_', $tmp);
}

if(isset($_POST['setMods']))
{
	foreach($_POST['mods'] as $fid => $modid)
	{
		$e107->sql->db_Update('forum',"forum_moderators = '{$modid}' WHERE forum_id = {$fid}");
	}
	$forum->show_message(FORLAN_144);
}

if(isset($_POST['tools']))
{
	$msg = '';
	if(isset($_POST['forum_all']))
	{
		$fList[]='all';
	}
	else
	{
		foreach(array_keys($_POST['forumlist']) as $k)
		{
			$fList[] = $k;
		}
	}
	foreach($fList as $fid)
	{
		if(isset($_POST['counts']))
		{
			$for->forumUpdateCounts($fid, $_POST['counts_threads']);
			$msg .= FORLAN_167.": $fid <br />";
		}
		if(isset($_POST['lastpost']))
		{
			$with_threads = (isset($_POST['lastpost_nothread'])) ? FALSE : TRUE;
			$for->forumUpdateLastpost('forum', $fid, $with_threads);
			$msg .= FORLAN_168.": $fid <br />";
		}
	}
	if(isset($_POST['userpostcounts']))
	{
		require_once(e_HANDLER.'user_extended_class.php');
		$ue = new e107_user_extended;

		$list = $for->getUserCounts();
		foreach($list as $uid => $cnt)
		{
			$ue->user_extended_setvalue($uid, 'user_plugin_forum_posts', $cnt, 'int');
		}
		$msg .= FORLAN_169.' <br />';
	}
	$forum->show_message($msg);
}

if(isset($_POST['create_sub']))
{
	$fid = (int)($sub_action);
	$tmp = array();
	$tmp['forum_name']  = $e107->tp->toDB($_POST['subname_new']);
	$tmp['forum_description']  = $e107->tp->toDB($_POST['subdesc_new']);
	$tmp['forum_order'] = (int)$_POST['suborder_new'];

	if($tmp['forum_name'] != '' && $e107->sql->db_Select('forum', '*', "forum_id = {$fid}"))
	{
		$row = $e107->sql->db_Fetch();
		$tmp['forum_parent'] = $row['forum_parent'];
		$tmp['forum_moderators'] = $row['forum_moderators'];
		$tmp['forum_class'] = $row['forum_class'];
		$tmp['forum_postclass'] = $row['forum_postclass'];
		$tmp['forum_sub'] = $fid;
		if($e107->sql->db_Insert('forum', $tmp))
		{
			$forum->show_message(FORLAN_150.' - '.LAN_CREATED);
		}
		else
		{
			$forum->show_message(FORLAN_150.' - '.LAN_CREATED_FAILED);
		}
	}
}

if(isset($_POST['update_subs']))
{
	$msg = "";
	foreach(array_keys($_POST['subname']) as $id)
	{
		if($_POST['subname'][$id] == "")
		{
			if ($e107->sql->db_Delete("forum", "forum_id='$id' "))
			{
				$msg .= FORLAN_150." ".$id." ".LAN_DELETED."<br />";
				$cnt = $e107->sql->db_Delete("forum_t", "thread_forum_id = {$id}");
				$msg .= $cnt." ".FORLAN_152." ".LAN_DELETED."<br />";
			}
		}
		else
		{
			$_name  = $e107->tp->toDB($_POST['subname'][$id]);
			$_desc  = $e107->tp->toDB($_POST['subdesc'][$id]);
			$_order = (int)$_POST['suborder'][$id];
			if($e107->sql->db_Update('forum', "forum_name='{$_name}', forum_description='{$_desc}', forum_order='{$_order}' WHERE forum_id = {$id}"))
			{
				$msg .= FORLAN_150.' '.$id.' '.LAN_UPDATED.'<br />';
			}
		}
	}
	if($msg)
	{
		$forum->show_message($msg);
	}
}

if(isset($_POST['submit_parent']))
{
	$tmp = array();
	$tmp['forum_name'] = $e107->tp->toDB($_POST['forum_name']);
	$tmp['forum_datestamp'] = time();
	$tmp['forum_class'] = (int)$_POST['forum_class'];
	$tmp['forum_postclass'] = (int)$_POST['forum_postclass'];
	$tmp['forum_threadclass'] = (int)$_POST['forum_threadclass'];
	if($e107->sql->db_Insert('forum',$tmp))
	{
		$forum->show_message(FORLAN_22.' - '.LAN_CREATED);
	}
	else
	{
		$forum->show_message(FORLAN_22.' - '.LAN_CREATED_FAILED);
	}
}

if(isset($_POST['update_parent']))
{
	$tmp = $_POST;
	unset($tmp['update_parent']);
	$tmp['_FIELD_TYPES']['forum_name'] = 'todb';
	$tmp['_FIELD_TYPES']['forum_class'] = 'int';
	$tmp['_FIELD_TYPES']['forum_postclass'] = 'int';
	$tmp['_FIELD_TYPES']['forum_threadclass'] = 'int';
	$tmp['WHERE'] = 'forum_id = '.(int)$id;
	$e107->sql->db_Update('forum', $tmp);
	$forum->show_message(FORLAN_14);
	$action = 'main';
}

if(isset($_POST['submit_forum']))
{
	$tmp = array();
	$tmp['forum_moderators'] = (int)$_POST['forum_moderators'];
	$tmp['forum_name'] = $e107->tp->toDB($_POST['forum_name']);
	$tmp['forum_description'] = $e107->tp->toDB($_POST['forum_description']);
	$tmp['forum_datestamp'] = time();
	$tmp['forum_class'] = (int)$_POST['forum_class'];
	$tmp['forum_postclass'] = (int)$_POST['forum_postclass'];
	$tmp['forum_threadclass'] = (int)$_POST['forum_threadclass'];
	$tmp['forum_parent'] = (int)$_POST['forum_parent'];
	if($e107->sql->db_Insert('forum',$tmp))
	{
		$forum->show_message(FORLAN_36.' - '.LAN_CREATED);
	}
	else
	{
		$forum->show_message(FORLAN_36.' - '.LAN_CREATED_FAILED);
	}
}

if(isset($_POST['update_forum']))
{
	unset($_POST['update_forum']);
	$tmp['data'] = $_POST;
	$tmp['WHERE'] = 'forum_id = '.(int)$id;

	$tmp2['forum_moderators'] = $tmp['forum_moderators'];
	$tmp2['forum_class'] = $tmp['forum_class'];
	$tmp2['forum_postclass'] = $tmp['forum_postclass'];
	$tmp2['forum_threadclass'] = $tmp['forum_threadclass'];
	$tmp2['WHERE'] = 'forum_sub = '.(int)$id;

	$e107->sql->db_Update('forum', $tmp);
	$e107->sql->db_Update('forum', $tmp2);

	$forum->show_message(FORLAN_12);
	$action = 'main';
}

if (isset($_POST['update_order']))
{
	while (list($key, $id) = each($_POST['forum_order']))
	{
		$tmp = explode('.', $id);
		$e107->sql->db_Update('forum', "forum_order=".$tmp[1]." WHERE forum_id=".$tmp[0]);
	}
	$forum->show_message(FORLAN_73);
}

if (isset($_POST['updateoptions']))
{
	$fPref->set('notify', $_POST['email_notify']);
	$fPref->set('notify_on', $_POST['email_notify_on']);
	$fPref->set('poll', $_POST['forum_poll']);
	$fPref->set('popular', $_POST['forum_popular']);
	$fPref->set('track', $_POST['forum_track']);
	$fPref->set('eprefix', $_POST['forum_eprefix']);
	$fPref->set('enclose', $_POST['forum_enclose']);
	$fPref->set('title', $_POST['forum_title']);
	$fPref->set('postspage', $_POST['forum_postspage']);
	$fPref->set('threadspage', $_POST['forum_threadspage']);
	$fPref->set('html_post', $_POST['html_post']);
	$fPref->set('attach', $_POST['forum_attach']);
	$fPref->set('redirect', $_POST['forum_redirect']);
	$fPref->set('reported_post_email', $_POST['reported_post_email']);
	$fPref->set('tooltip', $_POST['forum_tooltip']);
	$fPref->set('tiplength',  $_POST['forum_tiplength']);
	$fPref->set('hilightsticky', $_POST['forum_hilightsticky']);
	$fPref->set('maxwidth', $_POST['forum_maxwidth']);
	$fPref->set('linkimg', $_POST['forum_linkimg']);
	$fPref->save(true, true);
	$emessage->add(FORLAN_10, E_MESSAGE_SUCCESS);
//	$forum->show_message(FORLAN_10);
}

if (isset($_POST['do_prune']))
{
	$msg = $for->forumPrune($_POST['prune_type'], $_POST['prune_days'], $_POST['pruneForum']);
	$forum->show_message($msg);
	$action = 'main';
}

if (isset($_POST['frsubmit']))
{
	$guestrules = $e107->tp->toDB($_POST['guestrules']);
	$memberrules = $e107->tp->toDB($_POST['memberrules']);
	$adminrules = $e107->tp->toDB($_POST['adminrules']);
	if(!$e107->sql->db_Update("generic", "gen_chardata ='$guestrules', gen_intdata='".$_POST['guest_active']."' WHERE gen_type='forum_rules_guest' "))
	{
		$e107->sql->db_Insert("generic", "0, 'forum_rules_guest', '".time()."', 0, '', '".$_POST['guest_active']."', '$guestrules' ");
	}
	if(!$e107->sql->db_Update("generic", "gen_chardata ='$memberrules', gen_intdata='".$_POST['member_active']."' WHERE gen_type='forum_rules_member' "))
	{
		$e107->sql->db_Insert("generic", "0, 'forum_rules_member', '".time()."', 0, '', '".$_POST['member_active']."', '$memberrules' ");
	}
	if(!$e107->sql->db_Update("generic", "gen_chardata ='$adminrules', gen_intdata='".$_POST['admin_active']."' WHERE gen_type='forum_rules_admin' "))
	{
		$e107->sql->db_Insert("generic", "0, 'forum_rules_admin', '".time()."', 0, '', '".$_POST['admin_active']."', '$adminrules' ");
	}
}


if ($delete == 'main') {
	if ($e107->sql->db_Delete('forum', "forum_id='$del_id' ")) {
		$forum->show_message(FORLAN_96);
	}
}

if ($action == 'create')
{
	if ($e107->sql->db_Select('forum', '*', "forum_parent='0' "))
	{
		$forum->create_forums($sub_action, $id);
	}
	else
	{
		header('location:'.e_ADMIN.'forum.php');
		exit;
	}
}

if ($delete == 'cat')
{
	if ($e107->sql->db_Delete('forum', "forum_id='$del_id' "))
	{
		$e107->sql->db_Delete('forum', "forum_parent='$del_id' ");
		$forum->show_message(FORLAN_97);
		$action = 'main';
	}
}

switch($action)
{
	case 'delete':
		$forum->delete_item(intval($sub_action));
		break;

	case 'cat':
		$forum->create_parents($sub_action, $id);
		break;

	case 'order':
		$forum->show_existing_forums($sub_action, $id, true);
		break;

	case 'opt':
		$forum->show_prefs();
		break;

	case 'mods':
		$forum->show_mods();
		break;

	case 'tools':
		$forum->show_tools();
		break;

	case 'prune':
		$forum->show_prune();
		break;

	case 'rules':
		$forum->show_rules();
		break;

	case 'subs':
		$forum->show_subs($sub_action);
		break;

	case 'sr':
		$forum->show_reported($sub_action);
		break;
}


if ($delete == 'reported')
{
	$e107->sql->db_Delete("generic", "gen_id='$del_id' ");
	$forum->show_message(FORLAN_118);
}



if (!e_QUERY || $action == 'main')
{
	$forum->show_existing_forums($sub_action, $id);
}

//$forum->show_options($action);
require_once(e_ADMIN.'footer.php');
function headerjs()
{
	$e107 = e107::getInstance();
	// These functions need to be removed and replaced with the generic jsconfirm() function.
	$headerjs = "<script type=\"text/javascript\">
	function confirm_(mode, forum_id, forum_name) {
		if (mode == 'sr') {
			return confirm(\"".$e107->tp->toJS(FORLAN_117)."\");
		} else if(mode == 'parent') {
			return confirm(\"".$e107->tp->toJS(FORLAN_81)." [ID: \" + forum_name + \"]\");
		} else {
			return confirm(\"".$e107->tp->toJS(FORLAN_82)." [ID: \" + forum_name + \"]\");
		}
	}
	</script>";
	return $headerjs;
}

function forum_admin_adminmenu()
{
	global $forum;
	global $action;
	$forum->show_options($action);
}
?>