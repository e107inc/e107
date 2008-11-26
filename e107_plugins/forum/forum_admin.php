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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/forum/forum_admin.php,v $
|     $Revision: 1.4 $
|     $Date: 2008-11-26 03:24:51 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
$eplug_admin = true;
require_once("../../class2.php");
@include_once e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_admin.php';
@include_once e_PLUGIN.'forum/languages/English/lan_forum_admin.php';

if (!getperms("P"))
{
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'forum';

require_once(e_ADMIN.'auth.php');
require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER.'form_handler.php');
require_once(e_HANDLER.'ren_help.php');
require_once(e_PLUGIN.'forum/forum_class.php');
require_once(e_PLUGIN.'forum/forum_admin_class.php');
$rs = new form;
$for = new e107forum;
$forum = new forumAdmin;
define("IMAGE_new", "<img src='".img_path('new.png')."' alt='' style='border:0' />");
define("IMAGE_sub", "<img src='".e_PLUGIN."forum/images/forums_16.png' alt='".FORLAN_145."' title='".FORLAN_145."' style='border:0' />");
define("IMAGE_nosub", "<img src='".e_PLUGIN."forum/images/sub_forums_16.png' alt='".FORLAN_145."' title='".FORLAN_145."' style='border:0' />");

$deltest = array_flip($_POST);
if (e_QUERY)
{
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	$id = $tmp[2];
	unset($tmp);
}

if(isset($_POST['delete']))
{
	$tmp = array_pop(array_flip($_POST['delete']));
	list($delete, $del_id) = explode("_", $tmp);
}

if(isset($_POST['setMods']))
{
	foreach($_POST['mods'] as $fid => $modid)
	{
		$sql->db_Update('forum',"forum_moderators = '{$modid}' WHERE forum_id = {$fid}");
	}
	$forum->show_message(FORLAN_144);
}

if(isset($_POST['tools']))
{
	$msg = "";
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
			$for->forum_update_counts($fid, $_POST['counts_threads']);
			$msg .= FORLAN_167.": $fid <br />";
		}
		if(isset($_POST['lastpost']))
		{
			$with_threads = (isset($_POST['lastpost_nothread'])) ? FALSE : TRUE;
			$for->update_lastpost('forum', $fid, $with_threads);
			$msg .= FORLAN_168.": $fid <br />";
		}
	}
	if(isset($_POST['userpostcounts']))
	{
		$list = $for->get_user_counts();
		foreach($list as $uid => $cnt)
		{
			$sql->db_Update("user","user_forums = '{$cnt}' WHERE user_id = '{$uid}'");
		}
		$msg .= FORLAN_169." <br />";
	}

	$forum->show_message($msg);
}

if(isset($_POST['create_sub']))
{
	$fid = (int)($sub_action);
	$tmp = array();
	$tmp['forum_name']  = $tp->toDB($_POST['subname_new']);
	$tmp['forum_description']  = $tp->toDB($_POST['subdesc_new']);
	$tmp['forum_order'] = (int)$_POST['suborder_new'];

	if($tmp['forum_name'] != '' && $sql->db_Select('forum', '*', "forum_id = {$fid}"))
	{
		$row = $sql->db_Fetch();
		$tmp['forum_parent'] = $row['forum_parent'];
		$tmp['forum_moderators'] = $row['forum_moderators'];
		$tmp['forum_class'] = $row['forum_class'];
		$tmp['forum_postclass'] = $row['forum_postclass'];
		$tmp['forum_sub'] = $fid;
		if($sql->db_Insert('forum', $tmp))
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
			if ($sql->db_Delete("forum", "forum_id='$id' "))
			{
				$msg .= FORLAN_150." ".$id." ".LAN_DELETED."<br />";
				$cnt = $sql->db_Delete("forum_t", "thread_forum_id = {$id}");
				$msg .= $cnt." ".FORLAN_152." ".LAN_DELETED."<br />";
			}
		}
		else
		{
			$_name  = $tp->toDB($_POST['subname'][$id]);
			$_desc  = $tp->toDB($_POST['subdesc'][$id]);
			$_order = intval($_POST['suborder'][$id]);
			if($sql->db_Update("forum", "forum_name='{$_name}', forum_description='{$_desc}', forum_order='{$_order}' WHERE forum_id = {$id}"))
			{
				$msg .= FORLAN_150." ".$id." ".LAN_UPDATED."<br />";
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
	$tmp['forum_name'] = $tp->toDB($_POST['forum_name']);
	$tmp['forum_datestamp'] = time();
	$tmp['forum_class'] = (int)$_POST['forum_class'];
	$tmp['forum_postclass'] = (int)$_POST['forum_postclass'];
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
	$_POST['forum_name'] = $tp->toDB($_POST['forum_name']);
	$sql->db_Update("forum", "forum_name='".$_POST['forum_name']."', forum_class='".$_POST['forum_class']."', forum_postclass='{$_POST['forum_postclass']}'  WHERE forum_id=$id");
	$forum->show_message(FORLAN_14);
	$action = "main";
}

if(isset($_POST['submit_forum']))
{
	$tmp = array();
	$tmp['forum_moderators'] = (int)$_POST['forum_moderators'];
	$tmp['forum_name'] = $tp->toDB($_POST['forum_name']);
	$tmp['forum_description'] = $tp->toDB($_POST['forum_description']);
	$tmp['forum_datestamp'] = time();
	$tmp['forum_class'] = (int)$_POST['forum_class'];
	$tmp['forum_postclass'] = (int)$_POST['forum_postclass'];
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
	$mods = $_POST['forum_moderators'];
	$_POST['forum_name'] = $tp->toDB($_POST['forum_name']);
	$_POST['forum_description'] = $tp->toDB($_POST['forum_description']);
	$forum_parent = $row['forum_id'];
	$sql->db_Update("forum", "forum_name='".$_POST['forum_name']."', forum_description='".$_POST['forum_description']."', forum_parent='".$_POST['forum_parent']."', forum_moderators='".$mods."', forum_class='".$_POST['forum_class']."', forum_postclass='{$_POST['forum_postclass']}' WHERE forum_id=$id");
	$sql->db_Update("forum", "forum_moderators='".$mods."', forum_class='".$_POST['forum_class']."', forum_postclass='{$_POST['forum_postclass']}' WHERE forum_sub=$id");
	$forum->show_message(FORLAN_12);
	$action = "main";
}

if (isset($_POST['update_order']))
{
	extract($_POST);
	while (list($key, $id) = each($forum_order))
	{
		$tmp = explode(".", $id);
		$sql->db_Update("forum", "forum_order=".$tmp[1]." WHERE forum_id=".$tmp[0]);
	}
	$forum->show_message(FORLAN_73);
}

if (isset($_POST['updateoptions']))
{
	$pref['email_notify'] = $_POST['email_notify'];
	$pref['email_notify_on'] = $_POST['email_notify_on'];
	$pref['forum_poll'] = $_POST['forum_poll'];
	$pref['forum_popular'] = $_POST['forum_popular'];
	$pref['forum_track'] = $_POST['forum_track'];
	$pref['forum_eprefix'] = $_POST['forum_eprefix'];
	$pref['forum_enclose'] = $_POST['forum_enclose'];
	$pref['forum_title'] = $_POST['forum_title'];
	$pref['forum_postspage'] = $_POST['forum_postspage'];
	$pref['html_post'] = $_POST['html_post'];
	$pref['forum_attach'] = $_POST['forum_attach'];
	$pref['forum_redirect'] = $_POST['forum_redirect'];
	$pref['reported_post_email'] = $_POST['reported_post_email'];
	$pref['forum_tooltip'] = $_POST['forum_tooltip'];
	$pref['forum_tiplength'] = $_POST['forum_tiplength'];
	$pref['forum_hilightsticky'] = $_POST['forum_hilightsticky'];
	$pref['forum_maxwidth'] = $_POST['forum_maxwidth'];
	$pref['forum_linkimg'] = $_POST['forum_linkimg'];
	save_prefs();
	$forum->show_message(FORLAN_10);
}

if (isset($_POST['do_prune']))
{
	$msg = $for->forum_prune($_POST['prune_type'], $_POST['prune_days'], $_POST['pruneForum']);
	$forum->show_message($msg);
	$action = "main";
}

if (isset($_POST['set_ranks']))
{
	extract($_POST);
	for($a = 0; $a <= 9; $a++)
	{
		$r_names .= $tp->toDB($rank_names[$a]).",";
		$r_thresholds .= $tp->toDB($rank_thresholds[$a]).",";
		$r_images .= $tp->toDB($rank_images[$a]).",";
	}
	$pref['rank_main_admin'] = $_POST['rank_main_admin'];
	$pref['rank_main_admin_image'] = $_POST['rank_main_admin_image'];
	$pref['rank_admin'] = $_POST['rank_admin'];
	$pref['rank_admin_image'] = $_POST['rank_admin_image'];
	$pref['rank_moderator'] = $_POST['rank_moderator'];
	$pref['rank_moderator_image'] = $_POST['rank_moderator_image'];
	$pref['forum_levels'] = $r_names;
	$pref['forum_thresholds'] = $r_thresholds;
	$pref['forum_images'] = $r_images;
	save_prefs();
	$forum->show_message(FORLAN_95);
}

if (isset($_POST['frsubmit']))
{
	$guestrules = $tp->toDB($_POST['guestrules']);
	$memberrules = $tp->toDB($_POST['memberrules']);
	$adminrules = $tp->toDB($_POST['adminrules']);
	if(!$sql->db_Update("generic", "gen_chardata ='$guestrules', gen_intdata='".$_POST['guest_active']."' WHERE gen_type='forum_rules_guest' "))
	{
		$sql -> db_Insert("generic", "0, 'forum_rules_guest', '".time()."', 0, '', '".$_POST['guest_active']."', '$guestrules' ");
	}
	if(!$sql->db_Update("generic", "gen_chardata ='$memberrules', gen_intdata='".$_POST['member_active']."' WHERE gen_type='forum_rules_member' "))
	{
		$sql -> db_Insert("generic", "0, 'forum_rules_member', '".time()."', 0, '', '".$_POST['member_active']."', '$memberrules' ");
	}
	if(!$sql->db_Update("generic", "gen_chardata ='$adminrules', gen_intdata='".$_POST['admin_active']."' WHERE gen_type='forum_rules_admin' "))
	{
		$sql -> db_Insert("generic", "0, 'forum_rules_admin', '".time()."', 0, '', '".$_POST['admin_active']."', '$adminrules' ");
	}
}


if ($delete == 'main') {
	if ($sql->db_Delete("forum", "forum_id='$del_id' ")) {
		$forum->show_message(FORLAN_96);
	}
}

if ($action == "create")
{
	if ($sql->db_Select("forum", "*", "forum_parent='0' "))
	{
		$forum->create_forums($sub_action, $id);
	}
	else
	{
		header("location:".e_ADMIN."forum.php");
		exit;
	}
}

if ($delete == 'cat')
{
	if ($sql->db_Delete("forum", "forum_id='$del_id' "))
	{
		$sql->db_Delete("forum", "forum_parent='$del_id' ");
		$forum->show_message(FORLAN_97);
		$action = "main";
	}
}

if($action == "delete")
{
	$forum->delete_item(intval($sub_action));
}

if ($action == "cat") {
	$forum->create_parents($sub_action, $id);
}

if ($action == "order") {
	$forum->show_existing_forums($sub_action, $id, TRUE);
}

if ($action == "opt")
{
	$forum->show_prefs();
}

if ($action == "mods")
{
	$forum->show_mods();
}

if ($action == "tools")
{
	$forum->show_tools();
}

if ($action == "prune")
{
	$forum->show_prune();
}

if ($action == "rank")
{
	$forum->show_levels();
}

if ($action == "rules")
{
	$forum->show_rules();
}

if($action == 'subs')
{
	$forum->show_subs($sub_action);
}

if ($delete == 'reported')
{
	$sql->db_Delete("generic", "gen_id='$del_id' ");
	$forum->show_message(FORLAN_118);
}


if ($action == "sr")
{
	$forum->show_reported($sub_action);
}

if (!e_QUERY || $action == "main")
{
	$forum->show_existing_forums($sub_action, $id);
}

//$forum->show_options($action);
require_once(e_ADMIN."footer.php");
function headerjs()
{
	global $tp;
	// These functions need to be removed and replaced with the generic jsconfirm() function.
	$headerjs = "<script type=\"text/javascript\">
	function confirm_(mode, forum_id, forum_name) {
		if (mode == 'sr') {
			return confirm(\"".$tp->toJS(FORLAN_117)."\");
		} else if(mode == 'parent') {
			return confirm(\"".$tp->toJS(FORLAN_81)." [ID: \" + forum_name + \"]\");
		} else {
			return confirm(\"".$tp->toJS(FORLAN_82)." [ID: \" + forum_name + \"]\");
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
