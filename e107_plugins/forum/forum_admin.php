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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/forum/forum_admin.php,v $
|     $Revision: 1.27 $
|     $Date: 2005-06-02 20:57:40 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
@include_once e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_admin.php';
@include_once e_PLUGIN.'forum/languages/English/lan_forum_admin.php';

if (!getperms("P"))
{
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'forum';

$forum = new forum;
require_once(e_ADMIN.'auth.php');
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."ren_help.php");
require_once(e_PLUGIN."forum/forum_class.php");
$rs = new form;
$for = new e107forum;
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
			$for->forum_update_counts($fid);
			$msg .= "Counts updated for forum: $fid <br />";
		}
		if(isset($_POST['lastpost']))
		{
			$with_threads = (isset($_POST['lastpost_nothread'])) ? FALSE : TRUE;
			$for->update_lastpost('forum', $fid, $with_threads);
			$msg .= "Lastpost info updated for forum: $fid <br />";
		}
	}
	if(isset($_POST['userpostcounts']))
	{
		$list = $for->get_user_counts();
		foreach($list as $uid => $cnt)
		{
			$sql->db_Update("user","user_forums = '{$cnt}' WHERE user_id = '{$uid}'");
		}
		$msg .= "User forum counts updated <br />";
	}

	$forum->show_message($msg);
}

if(isset($_POST['create_sub']))
{
	$fid = intval($sub_action);
	$_name  = $tp->toDB($_POST['subname_new']);
	$_desc  = $tp->toDB($_POST['subdesc_new']);
	$_order = intval($_POST['suborder_new']);
	if($_name != "" && $sql->db_Select('forum', '*', "forum_id = {$fid}"))
	{
		$row = $sql->db_Fetch();
		if($sql->db_Insert("forum", "0, '{$_name}', '{$_desc}', '{$row['forum_parent']}', '{$fid}', '".time()."', '{$row['forum_moderators']}', 0, 0, '', '', '{$row['forum_class']}', '{$_order}', '{$row['forum_postclass']}'"))
		{
			$forum->show_message(LAN_CREATED);
		}
		else
		{
			$forum->show_message(LAN_CREATED_FAILED);
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
	$_POST['forum_name'] = $tp->toDB($_POST['forum_name']);
	$sql->db_Insert("forum", "0, '".$_POST['forum_name']."', '', '', '0', '".time()."', '', '0', '0', '', '', '".$_POST['forum_class']."', 0, '{$_POST['forum_postclass']}'");
	$forum->show_message(FORLAN_13);
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
	$mods = $_POST['forum_moderators'];
	$_POST['forum_name'] = $tp->toDB($_POST['forum_name']);
	$_POST['forum_description'] = $tp->toDB($_POST['forum_description'], "admin");
	$sql->db_Insert("forum", "0, '".$_POST['forum_name']."', '".$_POST['forum_description']."', '".$_POST['forum_parent']."', '0', '".time()."', '".$mods."', 0, 0, '', '', '".$_POST['forum_class']."', 0, '{$_POST['forum_postclass']}'");
	$forum->show_message(FORLAN_11);
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
	$pref['forum_user_customtitle'] = $_POST['forum_user_customtitle'];
	$pref['reported_post_email'] = $_POST['reported_post_email'];
	$pref['links_new_window'] = $_POST['links_new_window'];
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
	$sql->db_Delete("tmp", "tmp_time='$del_id' ");
	$forum->show_message(FORLAN_118);
	$forum->show_reported();
}


if ($action == "sr")
{
	$forum->show_reported();
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

class forum
{

	function show_options($action)
	{
		global $sql;
		if ($action == "")
		{
			$action = "main";
		}
		// ##### Display options ---------------------------------------------------------------------------------------------------------
		$var['main']['text'] = FORLAN_76;
		$var['main']['link'] = e_SELF;
		$var['cat']['text'] = FORLAN_83;
		$var['cat']['link'] = e_SELF."?cat";
		if ($sql->db_Select("forum", "*", "forum_parent='0' "))
		{
			$var['create']['text'] = FORLAN_77;
			$var['create']['link'] = e_SELF."?create";
		}
		$var['order']['text'] = FORLAN_78;
		$var['order']['link'] = e_SELF."?order";
		$var['opt']['text'] = FORLAN_79;
		$var['opt']['link'] = e_SELF."?opt";
		$var['prune']['text'] = FORLAN_59;
		$var['prune']['link'] = e_SELF."?prune";
		$var['rank']['text'] = FORLAN_63;
		$var['rank']['link'] = e_SELF."?rank";
		$var['rules']['text'] = FORLAN_123;
		$var['rules']['link'] = e_SELF."?rules";
		if ($sql->db_Select("tmp", "*", "tmp_ip='reported_post' "))
		{
			$var['sr']['text'] = FORLAN_116;
			$var['sr']['link'] = e_SELF."?sr";
		}
		$var['mods']['text'] = FORLAN_33;
		$var['mods']['link'] = e_SELF."?mods";
		$var['tools']['text'] = FORLAN_153;
		$var['tools']['link'] = e_SELF."?tools";
	
		show_admin_menu(FORLAN_7, $action, $var);
	}
	
	function delete_item($id)
	{
		global $sql;
		$id = intval($id);
		$confirm = isset($_POST['confirm']) ? TRUE : FALSE;

		if($sql->db_Select('forum', '*', "forum_id = {$id}"))
		{
			$txt = "";
			$row = $sql->db_Fetch();
			if($row['forum_parent'] == 0)
			{
				$txt .= $this->delete_parent($id, $confirm);
			}
			elseif($row['forum_sub'] > 0)
			{
				$txt .= $this->delete_sub($id, $confirm);
			}
			else
			{
				$txt .= $this->delete_forum($id, $confirm);
			}
			if($confirm)
			{
				$this->show_message($txt);
			}
			else
			{
				$this->delete_show_confirm($txt);
			}
		}
	}

	function delete_parent($id, $confirm = FALSE)
	{	
		global $sql;
		$ret = "";
		if($sql->db_Select("forum", "forum_id", "forum_parent = {$id} AND forum_sub = 0"))
		{
			$fList = $sql->db_getList();
			foreach($fList as $f)
			{
				$ret .= $this->delete_forum($f['forum_id'], $confirm);
			}
		}
		if($confirm)
		{
			if($sql->db_Delete("forum", "forum_id = {$id}"))
			{
				$ret .= "Forum parent successfully deleted";
			}
			else
			{
				$ret .= "Forum parent could not be deleted";
			}
			return $ret;
		}
		return "The forum parent has the following info: <br />".$ret;
		
	}
	
	function delete_forum($id, $confirm = FALSE)
	{
		global $sql, $tp;
		$ret = "";
		if($sql->db_Select("forum", "forum_id", "forum_sub = {$id}"))
		{
			$fList = $sql->db_getList();
			foreach($fList as $f)
			{
				$ret .= $this->delete_sub($f['forum_id'], $confirm);
			}
		}
		if($confirm)
		{
			$cnt = $sql->db_Delete("forum_t","thread_forum_id = {$id}");
			$ret .= $cnt." forum {$id} thread(s) deleted <br />";
			if($sql->db_Delete("forum", "forum_id = {$id}"))
			{
				$ret .= "Forum {$id} successfully deleted";
			}
			else
			{
				$ret .= "Forum {$id} could not be deleted";
			}
			return $ret;
		}
		
		$sql->db_Select("forum", "*", "forum_id = {$id}");
		$row = $sql->db_Fetch();
		return "Forum {$id} [".$tp->toHTML($row['forum_name'])."] has {$row['forum_threads']} threads, {$row['forum_replies']} replies. <br />".$ret;
	}
	
	function delete_sub($id, $confirm = FALSE)
	{
		global $sql, $tp;
		if($confirm)
		{
			$cnt = $sql->db_Delete("forum_t","thread_forum_id = {$id}");
			$ret .= $cnt." Sub-forum {$id} thread(s) deleted <br />";
			if($sql->db_Delete("forum", "forum_id = {$id}"))
			{
				$ret .= "Sub-forum {$id} successfully deleted";
			}
			else
			{
				$ret .= "Sub-forum {$id} could not be deleted";
			}
			return $ret;
		}
		
		$sql->db_Select("forum", "*", "forum_id = {$id}");
		$row = $sql->db_Fetch();
		return "Sub-forum {$id} [".$tp->toHTML($row['forum_name'])."] has {$row['forum_threads']} threads, {$row['forum_replies']} replies. <br />".$ret;
	}

	function delete_show_confirm($txt)
	{
		global $ns;
		$this->show_message($txt);
		$txt = "
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<div style='text-align:center'>
		Confirm delete operation<br /><br />
		<input type='submit' class='button' name='confirm' value='Confirm Delete' />
		</div>
		</form>
		";
		$ns->tablerender("Delete confirmation", $txt);
	}

	function show_subs($id)
	{
		global $sql, $tp, $ns;
		$txt = "
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<table style='width:100%'>
		<tr>
			<td class='fcaption'>".FORLAN_151."</td>
			<td class='fcaption'>".FORLAN_31."</td>
			<td class='fcaption'>".FORLAN_32."</td>
			<td class='fcaption'>".FORLAN_37."</td>
			<td class='fcaption'>".FORLAN_20."</td>
		</tr>
		";
		if($sql->db_Select('forum', 'forum_id, forum_name, forum_description, forum_order', "forum_sub = {$id} ORDER by forum_order ASC"))
		{
			$subList = $sql->db_getList();
			foreach($subList as $sub)
			{
				$txt .= "
				<tr>
				<td class='forumheader2' style='vertical-align:top'>{$sub['forum_id']}</td>
				<td class='forumheader2' style='vertical-align:top'><input class='tbox' type='text' name='subname[{$sub['forum_id']}]' value='{$sub['forum_name']}' size='30' maxlength='255' /></td>
				<td class='forumheader2' style='vertical-align:top'><textarea cols='60' rows='2' class='tbox' name='subdesc[{$sub['forum_id']}]'>{$sub['forum_description']}</textarea></td>
				<td class='forumheader2' style='vertical-align:top'><input class='tbox' type='text' name='suborder[{$sub['forum_id']}]' value='{$sub['forum_order']}' size='3' maxlength='4' /></td>
				<td class='forumheader2' style='vertical-align:top; text-align:center'>
				<a href='".e_SELF."?delete.{$sub['forum_id']}'>".ADMIN_DELETE_ICON."</a>
				</td>
				</tr>
				";
			}
			$txt .= "
			<tr>
			<td class='forumheader3' colspan='5' style='text-align:center'><input type='submit' class='button' name='update_subs' value='".FORLAN_147."' /></td>
			</tr>
			<tr>
			<td colspan='5' style='text-align:center'>&nbsp;</td>
			</tr>
			";

		}
		else
		{
			$txt .= "<tr><td colspan='5' class='forumheader3' style='text-align:center'>".FORLAN_146."</td>";
		}
		
		$txt .= "
		<tr>
			<td class='fcaption'>".FORLAN_151."</td>
			<td class='fcaption'>".FORLAN_31."</td>
			<td class='fcaption'>".FORLAN_32."</td>
			<td class='fcaption'>".FORLAN_37."</td>
			<td class='fcaption'>&nbsp;</td>
		</tr>
		<tr>
		<td class='forumheader2' style='vertical-align:top'>&nbsp;</td>
		<td class='forumheader2'><input class='tbox' type='text' name='subname_new' value='' size='30' maxlength='255' /></td>
		<td class='forumheader2'><textarea cols='60' rows='2' class='tbox' name='subdesc_new'></textarea></td>
		<td class='forumheader2'><input class='tbox' type='text' name='suborder_new' value='' size='3' maxlength='4' /></td>
		<td class='forumheader2'>&nbsp;</td>
		</tr>
		<tr>
		<td class='forumheader3' colspan='5' style='text-align:center'><input type='submit' class='button' name='create_sub' value='".FORLAN_148."' /></td>
		</tr>
		</table>
		</form>
		";
		$ns->tablerender(FORLAN_149, $txt);
	}
	
	function show_existing_forums($sub_action, $id, $mode = FALSE)
	{
		global $sql, $rs, $ns, $sql2, $sql3, $tp, $for;
		
		$subList = $for->forum_getsubs();
		if (!is_object($sql2))
		{
			$sql2 = new db;
		}
		if (!is_object($sql3))
		{
			$sql3 = new db;
		}
		if (!$mode)
		{
			$text = "<div style='padding : 1px; ".ADMIN_WIDTH."; margin-left: auto; margin-right: auto; text-align: center;'>";
		} else {
			$text = "<form method='post' action='".e_SELF."?".e_QUERY."'>";
		}
		$text .= "
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td colspan='2' style='width:70%; text-align:center' class='fcaption'>".FORLAN_28."</td>
			<td style='width:30%; text-align:center' class='fcaption'>".FORLAN_80."</td>
			</tr>";

		if (!$parent_amount = $sql->db_Select("forum", "*", "forum_parent='0' ORDER BY forum_order ASC"))
		{
			$text .= "<tr><td class='forumheader3' style='text-align:center' colspan='3'>".FORLAN_29."</td></tr>";
		}
		else
		{
			$sql2 = new db;
			$sql3 = new db;
			while ($row = $sql->db_Fetch())
			{
				extract($row);
				$parent_id = $forum_id;
				$text .= "
				<tr>
					<td colspan='2' class='forumheader'>".$forum_name."
					<br /><b>".FORLAN_140.":</b> ".r_userclass_name($forum_class)."&nbsp;&nbsp;<b>".FORLAN_141.":</b> ".r_userclass_name($forum_postclass)."
					</td>";

				$text .= "<td class='forumheader' style='text-align:center'>";

				if ($mode)
				{
					$text .= "<select name='forum_order[]' class='tbox'>\n";
					for($a = 1; $a <= $parent_amount; $a++)
					{
						$text .= ($forum_order == $a ? "<option value='$forum_id.$a' selected='selected'>$a</option>\n" : "<option value='$forum_id.$a'>$a</option>\n");
					}
					$text .= "</select>";
				}
				else
				{
					$forum_heading = str_replace("&#39;", "\'", $forum_name);
					$text .= "
						<div style='text-align:left; padding-left: 30px'>
						<a href='".e_SELF."?cat.edit.{$forum_id}'>".ADMIN_EDIT_ICON."</a>
						<a href='".e_SELF."?delete.{$forum_id}'>".ADMIN_DELETE_ICON."</a>
						</div>
						";
				}
				$text .= "</td></tr>";

				$forums = $sql2->db_Select("forum", "*", "forum_parent='".$forum_id."' AND forum_sub = 0 ORDER BY forum_order ASC");
				if (!$forums)
				{
					$text .= "<td colspan='4' style='text-align:center' class='forumheader3'>".FORLAN_29."</td>";
				}
				else
				{
					while ($row = $sql2->db_Fetch())
					{
						extract($row);

						$text .= "
						<tr>
							<td style='width:5%; text-align:center' class='forumheader3'>".IMAGE_new."</td>\n<td style='width:55%' class='forumheader3'><a href='".e_PLUGIN."forum/forum_viewforum.php?".$forum_id."'>".$forum_name."</a>" ;

						$text .= "
							<br /><span class='smallblacktext'>".$forum_description."</span>
							<br /><b>".FORLAN_140.":</b> ".r_userclass_name($forum_class)."&nbsp;&nbsp;<b>".FORLAN_141.":</b> ".r_userclass_name($forum_postclass)."
						
						</td>
						
							<td colspan='2' class='forumheader3' style='text-align:center'>";

						if ($mode)
						{
							$text .= "<select name='forum_order[]' class='tbox'>\n";
							for($a = 1; $a <= $forums; $a++)
							{
								$text .= ($forum_order == $a ? "<option value='$forum_id.$a' selected='selected'>$a</option>\n" : "<option value='$forum_id.$a'>$a</option>\n");
							}
							$text .= "</select>";
						}
						else
						{
							$forum_heading = str_replace("&#39;", "\'", $forum_name);
							$sub_img = count($subList[$parent_id][$forum_id]) ? IMAGE_sub : IMAGE_nosub;
							$text .= "
									<div style='text-align:left; padding-left: 30px'>
									<a href='".e_SELF."?create.edit.{$forum_id}'>".ADMIN_EDIT_ICON."</a>
									<a href='".e_SELF."?delete.{$forum_id}'>".ADMIN_DELETE_ICON."</a>
									&nbsp;&nbsp;<a href='".e_SELF."?subs.{$forum_id}'>".$sub_img."</a>
									</div>
								";
						}
						$text .= "</td>\n</tr>";
					}
				}
			}
		}

		if (!$mode)
		{
			$text .= "</table></div>";
			$ns->tablerender(FORLAN_30, $text);
		}
		else
		{
			$text .= "<tr>\n<td colspan='4' style='text-align:center' class='forumheader'>\n<input class='button' type='submit' name='update_order' value='".FORLAN_72."' />\n</td>\n</tr>\n</table>\n</form>";
			$ns->tablerender(FORLAN_37, $text);
		}

	}

	function create_parents($sub_action, $id)
	{
		global $sql, $ns;

		if ($sub_action == "edit" && !$_POST['update_parent'])
		{
			if ($sql->db_Select("forum", "*", "forum_id=$id"))
			{
				$row = $sql->db_Fetch();
				 extract($row);
			}
		}
		$text = "<div style='text-align:center'>
			<form method='post' action='".e_SELF."?".e_QUERY."'>\n
			<table style='".ADMIN_WIDTH."' class='fborder'>

			<tr>
				<td style='width:40%' class='forumheader3'>".FORLAN_31.":</td>
				<td style='width:60%' class='forumheader3'>
					<input class='tbox' type='text' name='forum_name' size='60' value='$forum_name' maxlength='250' />
				</td>
			</tr>

			<tr>
				<td style='width:40%' class='forumheader3'>".FORLAN_23.":<br /><span class='smalltext'>(".FORLAN_24.")</span></td>
				<td style='width:60%' class='forumheader3'>".r_userclass("forum_class", $forum_class, 'off', 'nobody,public,member,admin,classes')."</td>
			</tr>

			<tr>
				<td style='width:40%' class='forumheader3'>".FORLAN_142.":<br /><span class='smalltext'>(".FORLAN_143.")</span></td>
				<td style='width:60%' class='forumheader3'>".r_userclass("forum_postclass", $forum_postclass, 'off', 'nobody,public,member,admin,classes')."</td>
			</tr>

			<tr style='vertical-align:top'>
			<td colspan='2'  style='text-align:center' class='forumheader'>";

		if ($sub_action == "edit")
		{
			$text .= "<input class='button' type='submit' name='update_parent' value='".FORLAN_25."' />";
		}
		else
		{
			$text .= "<input class='button' type='submit' name='submit_parent' value='".FORLAN_26."' />";
		}

		$text .= "</td>
			</tr>
			</table>
			</form>
			</div>";

		$ns->tablerender(FORLAN_75, $text);
	}

	function create_forums($sub_action, $id)
	{
		global $sql, $ns;

		if ($sub_action == "edit" && !$_POST['update_forum'])
		{
			if ($sql->db_Select("forum", "*", "forum_id=$id"))
			{
				$row = $sql->db_Fetch();
				 extract($row);
			}
		}

		$text = "<div style='text-align:center'>
			<form method='post' action='".e_SELF."?".e_QUERY."'>\n
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td style='width:40%' class='forumheader3'>".FORLAN_22.":</td>
			<td style='width:60%' class='forumheader3'>";

		$sql->db_Select("forum", "*", "forum_parent=0");
		$text .= "<select name='forum_parent' class='tbox'>\n";
		while (list($forum_id_, $forum_name_) = $sql->db_Fetch())
		{
			extract($row);
			if ($forum_id_ == $forum_parent)
			{
				$text .= "<option value='$forum_id_' selected='selected'>".$forum_name_."</option>\n";
			}
			else
			{
				$text .= "<option value='$forum_id_'>".$forum_name_."</option>\n";
			}
		}
		$text .= "</select>
			</td>
			</tr>

			<tr>
			<td style='width:40%' class='forumheader3'>".FORLAN_31.":</td>
			<td style='width:60%' class='forumheader3'>
			<input class='tbox' type='text' name='forum_name' size='60' value='$forum_name' maxlength='250' />
			</td>
			</tr>

			<tr>
			<td style='width:40%' class='forumheader3'>".FORLAN_32.": </td>
			<td style='width:60%' class='forumheader3'>
			<textarea class='tbox' name='forum_description' cols='50' rows='5'>$forum_description</textarea>
			</td>
			</tr>

			<tr>
			<td style='width:40%' class='forumheader3'>".FORLAN_33.":<br /><span class='smalltext'>(".FORLAN_34.")</span></td>
			<td style='width:60%' class='forumheader3'>";
			$text .= r_userclass("forum_moderators", $forum_moderators, 'off', 'admin,classes');

//		$admin_no = $sql->db_Select("user", "*", "user_admin='1' AND user_perms REGEXP('A.') OR user_perms='0' ");
//		while ($row = $sql->db_Fetch())
//		{
//			extract($row);
//			$text .= "<input type='checkbox' name='mod[]' value='".$user_name ."'";
//			if (preg_match('/'.preg_quote($user_name).'/', $forum_moderators))
//			{
//				$text .= " checked";
//			}
//			$text .= "/> ".$user_name ."<br />";
//		}

		$text .= "</td>
			</tr>
			<tr>
				<td style='width:40%' class='forumheader3'>".FORLAN_23.":<br /><span class='smalltext'>(".FORLAN_24.")</span></td>
				<td style='width:60%' class='forumheader3'>".r_userclass("forum_class", $forum_class, 'off', 'nobody,public,member,admin,classes')."</td>
			</tr>

			<tr>
				<td style='width:40%' class='forumheader3'>".FORLAN_142.":<br /><span class='smalltext'>(".FORLAN_143.")</span></td>
				<td style='width:60%' class='forumheader3'>".r_userclass("forum_postclass", $forum_postclass, 'off', 'nobody,public,member,admin,classes')."</td>
			</tr>

		<tr style='vertical-align:top'>
			<td colspan='2'  style='text-align:center' class='forumheader'>";
		if ($sub_action == "edit")
		{
			$text .= "<input class='button' type='submit' name='update_forum' value='".FORLAN_35."' />";
		}
		else
		{
			$text .= "<input class='button' type='submit' name='submit_forum' value='".FORLAN_36."' />";
		}
		$text .= "</td>
			</tr>
			</table>
			</form>
			</div>";
		$ns->tablerender(FORLAN_28, $text);
	}

	function show_message($message)
	{
		global $ns;
		$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	}

	function show_tools()
	{
		global $sql, $ns, $tp;
		$txt = "
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<table style='width:".ADMIN_WIDTH."'>
		<tr style='width:100%'>
		<td class='fcaption'>Select forum(s) to perform action on</td>
		</tr>
		<tr>
		<td class='forumheader3'>
		";
		if($sql->db_Select("forum", "*", "1 ORDER BY forum_order"))
		{
			$fList = $sql->db_getList();
			foreach($fList as $f)
			{
				$txt .= "<input type='checkbox' name='forumlist[{$f['forum_id']}]' value='1' /> ".$tp->toHTML($f['forum_name'])."<br />";
			}
			$txt .= "<input type='checkbox' name='forum_all' value='1' /> All Forums";
		}
		$txt .= "
		</td>
		</tr>
		<tr>
		<td class='fcaption'>Recalculate forum lastpost info</td>
		</tr>
		<tr>
		<td class='forumheader3'>
			<input type='checkbox' name='lastpost' value='1' /> Select to recalculate lastpost info <br />&nbsp;&nbsp;&nbsp;&nbsp;
			<input type='checkbox' name='lastpost_nothread' value='1' checked='checked' /> Select to perform this on forums only, not threads
		</td>
		</tr>
		<tr>
		<td class='fcaption'>Recalculate post / reply counts</td>
		</tr>
		<tr>
		<td class='forumheader3'>
			<input type='checkbox' name='counts' value='1' /> Select to recalculate forum thread/reply counts<br />
		</td>
		</tr>
		<tr>
		<td class='fcaption'>Recalculate user forum posts counts</td>
		</tr>
		<tr>
		<td class='forumheader3'>
			<input type='checkbox' name='userpostcounts' value='1' /> Select to recalculate user forum counts<br />
		</td>
		</tr>
		<tr>
		<td class='forumheader3' style='text-align:center'>
			<input class='button' type='submit' name='tools' value='Execute functions' />
		</td>
		</tr>
		</table>
		</form>
		";
		$ns->tablerender("Forum Tools", $txt);
	}

	function show_prefs()
	{
		global $pref, $ns;
		$text = "<div style='text-align:center'>
			<form method='post' action='".e_SELF."?".e_QUERY."'>\n
			<table style='".ADMIN_WIDTH."' class='fborder'>

			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_44."<br /><span class='smalltext'>".FORLAN_45."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' >".($pref['forum_enclose'] ? "<input type='checkbox' name='forum_enclose' value='1' checked='checked' />" : "<input type='checkbox' name='forum_enclose' value='1' />")."</td>
			</tr>

			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_65."<br /><span class='smalltext'>".FORLAN_46."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_title' size='15' value='".$pref['forum_title']."' maxlength='100' /></td>
			</tr>

			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_47."<br /><span class='smalltext'>".FORLAN_48."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' >".($pref['email_notify'] ? "<input type='checkbox' name='email_notify' value='1' checked='checked' />" : "<input type='checkbox' name='email_notify' value='1' />")."</td>
			</tr>

			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_124."<br /><span class='smalltext'>".FORLAN_125."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' >".($pref['links_new_window'] ? "<input type='checkbox' name='links_new_window' value='1' checked='checked' />" : "<input type='checkbox' name='links_new_window' value='1' />")."</td>
			</tr>

			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_49."<br /><span class='smalltext'>".FORLAN_50."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' >".($pref['forum_poll'] ? "<input type='checkbox' name='forum_poll' value='1' checked='checked' />" : "<input type='checkbox' name='forum_poll' value='1' />")."</td>
			</tr>

			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_70."<br /><span class='smalltext'>".FORLAN_71." <a href='".e_ADMIN."upload.php'>".FORLAN_130."</a> ". FORLAN_131."</span>";
			
			if(!$pref['image_post'])
			{
				$text .= "<br /><b>".FORLAN_139."</b>";
			}

			$text .= "</td>
			<td style='width:25%;text-align:center' class='forumheader3' >".($pref['forum_attach'] ? "<input type='checkbox' name='forum_attach' value='1' checked='checked' />" : "<input type='checkbox' name='forum_attach' value='1' />")."</td>
			</tr>

			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_134."<br /><span class='smalltext'>".FORLAN_135."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' size='3' maxlength='5' name='forum_maxwidth' value='{$pref['forum_maxwidth']}'</td>
			</tr>

			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_136."<br /><span class='smalltext'>".FORLAN_137."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' >".($pref['forum_linkimg'] ? "<input type='checkbox' name='forum_linkimg' value='1' checked='checked' />" : "<input type='checkbox' name='forum_linkimg' value='1' />")."</td>
			</tr>

			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_51."<br /><span class='smalltext'>".FORLAN_52."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' >".($pref['forum_track'] ? "<input type='checkbox' name='forum_track' value='1' checked='checked' />" : "<input type='checkbox' name='forum_track' value='1' />")."</td>
			</tr>

			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_112."<br /><span class='smalltext'>".FORLAN_113."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' >".($pref['forum_redirect'] ? "<input type='checkbox' name='forum_redirect' value='1' checked='checked' />" : "<input type='checkbox' name='forum_redirect' value='1' />")."</td>
			</tr>

			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_114."<br /><span class='smalltext'>".FORLAN_115."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' >".($pref['forum_user_customtitle'] ? "<input type='checkbox' name='forum_user_customtitle' value='1' checked='checked' />" : "<input type='checkbox' name='forum_user_customtitle' value='1' />")."</td>
			</tr>

			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_116."<br /><span class='smalltext'>".FORLAN_122."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' >".($pref['reported_post_email'] ? "<input type='checkbox' name='reported_post_email' value='1' checked='checked' />" : "<input type='checkbox' name='reported_post_email' value='1' />")."</td>
			</tr>


			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_126."<br /><span class='smalltext'>".FORLAN_127."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' >".($pref['forum_tooltip'] ? "<input type='checkbox' name='forum_tooltip' value='1' checked='checked' />" : "<input type='checkbox' name='forum_tooltip' value='1' />")."</td>
			</tr>

			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_128."<br /><span class='smalltext'>".FORLAN_129."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_tiplength' size='15' value='".$pref['forum_tiplength']."' maxlength='20' /></td>
			</tr>


			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_53."<br /><span class='smalltext'>".FORLAN_54."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_eprefix' size='15' value='".$pref['forum_eprefix']."' maxlength='20' /></td>
			</tr>

			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_55."<br /><span class='smalltext'>".FORLAN_56."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_popular' size='3' value='".$pref['forum_popular']."' maxlength='3' /></td>
			</tr>

			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_57."<br /><span class='smalltext'>".FORLAN_58."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_postspage' size='3' value='".$pref['forum_postspage']."' maxlength='3' /></td>
			</tr>

			<tr>
			<td style='width:75%' class='forumheader3'>".FORLAN_132."<br /><span class='smalltext'>".FORLAN_133."</span></td>
			<td style='width:25%;text-align:center' class='forumheader3' >".($pref['forum_hilightsticky'] ? "<input type='checkbox' name='forum_hilightsticky' value='1' checked='checked' />" : "<input type='checkbox' name='forum_hilightsticky' value='1' />")."</td>
			</tr>

			<tr>
			<td colspan='2'  style='text-align:center' class='forumheader'>
			<input class='button' type='submit' name='updateoptions' value='".FORLAN_61."' />
			</td>
			</tr>
			</table>
			</form>
			</div>";
		$ns->tablerender(FORLAN_62, $text);
	}

	function show_reported ($sub_action, $id)
	{
		global $sql, $rs, $ns;
		$text = "<div style='padding : 1px; ".ADMIN_WIDTH."; height : 400px; overflow : auto; margin-left: auto; margin-right: auto;'>\n";
		if ($reported_total = $sql->db_Select("tmp", "*", "tmp_ip='reported_post' "))
		{
			$text .= "<table class='fborder' style='width:99%'>
				<tr>
				<td style='width:80%' class='fcaption'>".FORLAN_119."</td>
				<td style='width:20%; text-align:center' class='fcaption'>".FORLAN_80."</td>
				</tr>";
			while ($row = $sql->db_Fetch())
			{
				extract($row);
				$reported = explode("^", $tmp_info);
				$text .= "<tr>
					<td style='width:80%' class='forumheader3'><a href='".e_BASE."forum_viewtopic.php?".$reported[0].".".$reported[1]."#".$reported[2]."' rel='external'>".$reported[3]."</a></td>
					<td style='width:20%; text-align:center; vertical-align:top' class='forumheader3'>
					".$rs->form_open("post", e_SELF, "", "", "", " onsubmit=\"return confirm_('sr',$tmp_time)\"")."
					".$rs->form_button("submit", "reported_delete_{$tmp_time}", FORLAN_20)."
					".$rs->form_close()."
					</td>
					</tr>\n";
			}
			//                                     ".$rs->form_button("submit", "reported_delete", FORLAN_20, "onclick=\"confirm_('sr', $tmp_time);\"")."
			$text .= "</table>";
		}
		else
		{
			$text .= "<div style='text-align:center'>".FORLAN_121."</div>";
		}
		$text .= "</div>";
		$ns->tablerender(FORLAN_116, $text);
	}

	function show_prune()
	{
		global $ns, $sql;
		
		$sql -> db_Select("forum", "forum_id, forum_name", "forum_parent!=0 ORDER BY forum_order ASC");
		$forums = $sql -> db_getList();
		
		$text = "<div style='text-align:center'>
			<form method='post' action='".e_SELF."?".e_QUERY."'>\n
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td style='text-align:center' class='forumheader3'>".FORLAN_60."</td>
			</tr>
			<tr>

			<td style='text-align:center' class='forumheader3'>".FORLAN_87."
			<input class='tbox' type='text' name='prune_days' size='6' value='' maxlength='3' />
			</td>
			</tr>

			<tr>
			<td style='text-align:center' class='forumheader3'>".FORLAN_2."<br />
			".FORLAN_89." <input type='radio' name='prune_type' value='".FORLAN_3."' />&nbsp;&nbsp;&nbsp;
			".FORLAN_90." <input type='radio' name='prune_type' value='".FORLAN_111."' checked='checked' />
			</td>
			</tr>
			
			<tr>
			<td class='forumheader3'>".FORLAN_138.": <br />";

			foreach($forums as $forum)
			{
				$text .= "<input type='checkbox' name='pruneForum[]' value='".$forum['forum_id']."' /> ".$forum['forum_name']."<br />";
			}
			
			
			$text .= "<tr>
			<td colspan='2'  style='text-align:center' class='forumheader'>
			<input class='button' type='submit' name='do_prune' value='".FORLAN_5."' />
			</td>
			</tr>
			</table>
			</form>
			</div>";
		$ns->tablerender(FORLAN_59, $text);
	}

	function show_levels()
	{
		global $sql, $pref, $ns, $rs;

		$rank_names = explode(",", $pref['forum_levels']);
		$rank_thresholds = ($pref['forum_thresholds'] ? explode(",", $pref['forum_thresholds']) : array(20, 100, 250, 410, 580, 760, 950, 1150, 1370, 1600));
		$rank_images = ($pref['forum_images'] ? explode(",", $pref['forum_images']) : array("lev1.png", "lev2.png", "lev3.png", "lev4.png", "lev5.png", "lev6.png", "lev7.png", "lev8.png", "lev9.png", "lev10.png"));

		$text = "<div style='text-align:center'>
			<form method='post' action='".e_SELF."?".e_QUERY."'>\n
			<table style='".ADMIN_WIDTH."' class='fborder'>

			<tr>
			<td class='fcaption' style='width:40%'>".FORLAN_98."</td>
			<td class='fcaption' style='width:20%'>".FORLAN_102."<br /></td>
			<td class='fcaption' style='width:40%'>".FORLAN_104."<br /></td>
			</tr>

			<tr>
			<td class='forumheader3' style='width:40%'>&nbsp;</td>
			<td class='forumheader3' style='width:20%'><span class='smalltext'>".FORLAN_99."</span></td>
			<td class='forumheader3' style='width:40%'><span class='smalltext'>".FORLAN_100."</span></td>
			</tr>";

		$text .= "<tr>
			<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_main_admin' size='30' value='".($pref['rank_main_admin'] ? $pref['rank_main_admin'] : FORLAN_101)."' maxlength='30' /></td>
			<td class='forumheader3' style='width:40%'>&nbsp;</td>
			<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_main_admin_image' size='30' value='".($pref['rank_main_admin_image'] ? $pref['rank_main_admin_image'] : "main_admin.png")."' maxlength='30' /></td>
			</tr>

			<tr>
			<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_admin' size='30' value='".($pref['rank_admin'] ? $pref['rank_admin'] : FORLAN_103)."' maxlength='30' /></td>
			<td class='forumheader3' style='width:40%'>&nbsp;</td>
			<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_admin_image' size='30' value='".($pref['rank_admin_image'] ? $pref['rank_admin_image'] : "admin.png")."' maxlength='30' /></td>
			</tr>

			<tr>
			<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_moderator' size='30' value='".($pref['rank_moderator'] ? $pref['rank_moderator'] : FORLAN_105)."' maxlength='30' /></td>
			<td class='forumheader3' style='width:40%'>&nbsp;</td>
			<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_moderator_image' size='30' value='".($pref['rank_moderator_image'] ? $pref['rank_moderator_image'] : "moderator.png")."' maxlength='30' /></td>
			</tr>";

		for($a = 0; $a <= 9; $a++)
		{
			$text .= "<tr>
				<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_names[]' size='30' value='".($rank_names[$a] ? $rank_names[$a] : "")."' maxlength='30' /></td>
				<td class='forumheader3' style='width:20%; text-align:center'><input class='tbox' type='text' name='rank_thresholds[]' size='10' value='".$rank_thresholds[$a]."' maxlength='5' /></td>
				<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_images[]' size='30' value='".($rank_images[$a] ? $rank_images[$a] : "")."' maxlength='30' /></td>
				</tr>";
		}

		$text .= "<tr>
			<td colspan='3'  style='text-align:center' class='forumheader'>
			<input class='button' type='submit' name='set_ranks' value='".FORLAN_94."' />
			</td>
			</tr>
			</table>\n</form>\n</div>";
		$ns->tablerender("Ranks", $text);
	}

	function show_mods()
	{
		global $sql, $ns, $for;
		$forumList = $for->forum_getforums('list');
		$subList   = $for->forum_getsubs('bysub');
		$txt = "<form method='post' action='".e_SELF."?".e_QUERY."'><table class='fborder'><tr><td> &nbsp; </td>";
		foreach($forumList as $f)
		{
			$txt .= "
			<tr>
				<td class='forumheader'>{$f['forum_name']}</td>
				<td class='forumheader'>".r_userclass("mods[{$f['forum_id']}]", $f['forum_moderators'], 'off', 'admin,classes')."</td>
			</tr>
			";
			foreach($subList[$f['forum_id']] as $s)
			{
				$txt .= "
				<tr>
					<td class='forumheader3'>&nbsp;&nbsp;&nbsp;&nbsp;{$s['forum_name']}</td>
					<td class='forumheader3'>".r_userclass("mods[{$s['forum_id']}]", $s['forum_moderators'], 'off', 'admin,classes')."</td>
				</tr>
				";
			}
		}
		$txt .= "
		<tr>
		<td colspan='2' class='fcaption' style='text-align:center'>
			<input class='button' type='submit' name='setMods' value='".WMGLAN_4." ".FORLAN_33."' />
		</td>
		</tr>
		
		</table>";		
		$ns->tablerender(FORLAN_33, $txt);
	}

	function show_rules()
	{
		global $sql, $pref, $ns, $tp;

		$sql->db_Select("wmessage");
		list($null) = $sql->db_Fetch();
		list($null) = $sql->db_Fetch();
		list($null) = $sql->db_Fetch();
		list($id, $guestrules, $wm_active4) = $sql->db_Fetch();
		list($id, $memberrules, $wm_active5) = $sql->db_Fetch();
		list($id, $adminrules, $wm_active6) = $sql->db_Fetch();

		if($sql->db_Select('generic','*',"gen_type='forum_rules_guest'"))
		{
			$guest_rules = $sql->db_Fetch();
		}
		if($sql->db_Select('generic','*',"gen_type='forum_rules_member'"))
		{
			$member_rules = $sql->db_Fetch();
		}
		if($sql->db_Select('generic','*',"gen_type='forum_rules_admin'"))
		{
			$admin_rules = $sql->db_Fetch();
		}

		$guesttext = $tp->toFORM($guest_rules['gen_chardata']);
		$membertext = $tp->toFORM($member_rules['gen_chardata']);
		$admintext = $tp->toFORM($admin_rules['gen_chardata']);

		$text = "
			<div style='text-align:center'>
			<form method='post' action='".e_SELF."?rules'  id='wmform'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>";

		$text .= "

			<td style='width:20%' class='forumheader3'>".WMGLAN_1.": <br />
			".WMGLAN_6.":";
		if ($guest_rules['gen_intdata'])
		{
			$text .= "<input type='checkbox' name='guest_active' value='1'  checked='checked' />";
		}
		else
		{
			$text .= "<input type='checkbox' name='guest_active' value='1' />";
		}
		$text .= "</td>
			<td style='width:60%' class='forumheader3'>
			<textarea class='tbox' name='guestrules' cols='70' rows='10'>$guesttext</textarea>
			<br />
			<input class='helpbox' type='text' name='helpguest' size='100' />
			<br />
			".ren_help(1, "addtext1", "help1")."
			</td>
			</tr>

			<tr>
			<td style='width:20%' class='forumheader3'>".WMGLAN_2.": <br />
			".WMGLAN_6.":";
		if ($member_rules['gen_intdata'])
		{
			$text .= "<input type='checkbox' name='member_active' value='1'  checked='checked' />";
		}
		else
		{
			$text .= "<input type='checkbox' name='member_active' value='1' />";
		}
		$text .= "</td>
			<td style='width:60%' class='forumheader3'>
			<textarea class='tbox' name='memberrules' cols='70' rows='10'>$membertext</textarea>
			<br />
			<input class='helpbox' type='text' name='helpmember' size='100' />
			<br />
			".ren_help(1, "addtext2", "help2")."
			</td>
			</tr>

			<tr>
			<td style='width:20%' class='forumheader3'>".WMGLAN_3.": <br />
			".WMGLAN_6.": ";

		if ($admin_rules['gen_intdata'])
		{
			$text .= "<input type='checkbox' name='admin_active' value='1'  checked='checked' />";
		}
		else
		{
			$text .= "<input type='checkbox' name='admin_active' value='1' />";
		}

		$text .= "</td>
			<td style='width:60%' class='forumheader3'>
			<textarea class='tbox' name='adminrules' cols='70' rows='10'>$admintext</textarea>
			<br />
			<input class='helpbox' type='text' name='helpadmin' size='100' />
			<br />
			".ren_help(1, "addtext3", "help3")."
			</td>
			</tr>

			<tr style='vertical-align:top'>
			<td class='forumheader'>&nbsp;</td>
			<td style='width:60%' class='forumheader'>
			<input class='button' type='submit' name='frsubmit' value='".WMGLAN_4."' />
			</td>
			</tr>
			</table>
			</form>
			</div>";

		$ns->tablerender(WMGLAN_5, $text);

		echo "
			<script type=\"text/javascript\">
			function addtext1(sc){
			document.getElementById('wmform').guestrules.value += sc;
			}
			function addtext2(sc){
			document.getElementById('wmform').memberrules.value += sc;
			}
			function addtext3(sc){
			document.getElementById('wmform').adminrules.value += sc;
			}
			function help1(help){
			document.getElementById('wmform').helpguest.value = help;
			}
			function help2(help){
			document.getElementById('wmform').helpmember.value = help;
			}
			function help3(help){
			document.getElementById('wmform').helpadmin.value = help;
			}
			</script>
			";

	}
}	

function forum_admin_adminmenu()
{
	global $forum;
	global $action;
	$forum->show_options($action);
}
?>