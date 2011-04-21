<?php
/*
* e107 website system
*
* Copyright (C) 2008-2009 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Forum admin functions
*
* $URL$
* $Id$
*
*/
class forumAdmin
{

	function show_options($action)
	{
		global $sql;
		if ($action == '') { $action = 'main'; }
		// ##### Display options ---------------------------------------------------------------------------------------------------------
		$var['main']['text'] = FORLAN_76;
		$var['main']['link'] = e_SELF;
		$var['cat']['text'] = FORLAN_83;
		$var['cat']['link'] = e_SELF.'?cat';
		if ($sql->db_Select('forum', 'forum_id', "forum_parent='0' LIMIT 1"))
		{
			$var['create']['text'] = FORLAN_77;
			$var['create']['link'] = e_SELF.'?create';
		}
		$var['order']['text'] = FORLAN_78;
		$var['order']['link'] = e_SELF.'?order';
		$var['opt']['text'] = FORLAN_79;
		$var['opt']['link'] = e_SELF.'?opt';
		$var['prune']['text'] = FORLAN_59;
		$var['prune']['link'] = e_SELF.'?prune';
		$var['rules']['text'] = FORLAN_123;
		$var['rules']['link'] = e_SELF.'?rules';
		$var['sr']['text'] = FORLAN_116;
		$var['sr']['link'] = e_SELF.'?sr';
		$var['mods']['text'] = FORLAN_33;
		$var['mods']['link'] = e_SELF.'?mods';
		$var['tools']['text'] = FORLAN_153;
		$var['tools']['link'] = e_SELF.'?tools';

		show_admin_menu(FORLAN_7, $action, $var);
	}

	function delete_item($id)
	{
		global $sql;
		$id = (int)$id;
		$confirm = isset($_POST['confirm']) ? true : false;

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

	function delete_parent($id, $confirm = false)
	{
		global $sql;
		$ret = '';
		if($sql->db_Select('forum', 'forum_id', "forum_parent = {$id} AND forum_sub = 0"))
		{
			$fList = $sql->db_getList();
			foreach($fList as $f)
			{
				$ret .= $this->delete_forum($f['forum_id'], $confirm);
			}
		}
		if($confirm)
		{
			if($sql->db_Delete('forum', "forum_id = {$id}"))
			{
				$ret .= 'Forum parent successfully deleted';
			}
			else
			{
				$ret .= 'Forum parent could not be deleted';
			}
			return $ret;
		}
		return 'The forum parent has the following info: <br />'.$ret;

	}

	function deleteForum($forumId)
	{
		$e107 = e107::getInstance();
		$forumId = (int)$forumId;
//		echo "id = $forumId <br />";
		// Check for any sub forums
		if($e107->sql->db_Select('forum', 'forum_id', "forum_sub = {$forumId}"))
		{
			$list = $e107->sql->db_getList();
			foreach($list as $f)
			{
				$ret .= $this->deleteForum($f['forum_id']);
			}
		}
		require_once(e_PLUGIN.'forum/forum_class.php');
		$f = new e107Forum;
		if($e107->sql->db_Select('forum_thread', 'thread_id','thread_forum_id='.$forumId))
		{
			$list = $e107->sql->db_getList();
			foreach($list as $t)
			{
				$f->threadDelete($t['thread_id'], false);
			}
		}
		return $e107->sql->db_Delete('forum', 'forum_id = '.$forumId);
	}

	function delete_forum($id, $confirm = false)
	{
		$e107 = e107::getInstance();
		$ret = '';
		if($e107->sql->db_Select('forum', 'forum_id', 'forum_sub = '.$id))
		{
			$fList = $sql->db_getList();
			foreach($fList as $f)
			{
				$ret .= $this->delete_sub($f['forum_id'], $confirm);
			}
		}
		if($confirm)
		{
			if($this->deleteForum($id))
			{
				$ret .= "Forum {$id} successfully deleted";
			}
			else
			{
				$ret .= "Forum {$id} could not be deleted";
			}
			return $ret;
		}

		$e107->sql->db_Select('forum', 'forum_name, forum_threads, forum_replies', 'forum_id = '.$id);
		$row = $e107->sql->db_Fetch();
		return "Forum {$id} [".$e107->tp->toHTML($row['forum_name'])."] has {$row['forum_threads']} threads, {$row['forum_replies']} replies. <br />".$ret;
	}

	function delete_sub($id, $confirm = FALSE)
	{
		$e107 = e107::getInstance();
		if($confirm)
		{
			if($this->deleteForum($id))
			{
				$ret .= "Sub-forum {$id} successfully deleted";
			}
			else
			{
				$ret .= "Sub-forum {$id} could not be deleted";
			}
			return $ret;
		}

		$e107->sql->db_Select('forum', '*', 'forum_id = '.$id);
		$row = $e107->sql->db_Fetch();
		return "Sub-forum {$id} [".$e107->tp->toHTML($row['forum_name'])."] has {$row['forum_threads']} threads, {$row['forum_replies']} replies. <br />".$ret;
	}

	function delete_show_confirm($txt)
	{
		global $ns;
		$this->show_message($txt);
		$txt = "
		<form method='post' action='".e_SELF.'?'.e_QUERY."'>
		<div style='text-align:center'>".FORLAN_180."<br /><br />
		<input type='submit' class='button' name='confirm' value='".FORLAN_181."' />
		</div>
		</form>
		";
		$ns->tablerender(FORLAN_181, $txt);
	}

	function show_subs($id)
	{
		global $sql, $tp, $ns;
		$txt = "
		<form method='post' action='".e_SELF.'?'.e_QUERY."'>
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

	function show_existing_forums($sub_action, $id, $mode = false)
	{
		global $e107, $for;

		$subList = $for->forumGetSubs();

		if (!$mode)
		{
			$text = "<div style='padding : 1px; ".ADMIN_WIDTH."; margin-left: auto; margin-right: auto; text-align: center;'>";
		}
		else
		{
			$text = "<form method='post' action='".e_SELF."?".e_QUERY."'>";
		}
		$text .= "
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td colspan='2' style='width:70%; text-align:center' class='fcaption'>".FORLAN_28."</td>
		<td style='width:30%; text-align:center' class='fcaption'>".FORLAN_80."</td>
		</tr>";

		if (!$parent_amount = $e107->sql->db_Select('forum', '*', "forum_parent='0' ORDER BY forum_order ASC"))
		{
			$text .= "<tr><td class='forumheader3' style='text-align:center' colspan='3'>".FORLAN_29."</td></tr>";
		}
		else
		{
			while ($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
			{
				$parentList[] = $row;
			}
			foreach($parentList as $parent)
			{
				$text .= "
				<tr>
				<td colspan='2' class='forumheader'>".$parent['forum_name']."
				<br /><b>".FORLAN_140.":</b> ".$e107->user_class->uc_get_classname($parent['forum_class'])."&nbsp;&nbsp;<b>".FORLAN_141.":</b> ".$e107->user_class->uc_get_classname($parent['forum_postclass'])."
				</td>";

				$text .= "<td class='forumheader' style='text-align:center'>";

				if ($mode)
				{
					$text .= "<select name='forum_order[]' class='tbox'>\n";
					for($a = 1; $a <= $parent_amount; $a++)
					{
						$text .= ($parent['forum_order'] == $a ? "<option value='{$parent['forum_id']}.{$a}' selected='selected'>$a</option>\n" : "<option value='{$parent['forum_id']}.{$a}'>$a</option>\n");
					}
					$text .= "</select>";
				}
				else
				{
					$text .= "
					<div style='text-align:left; padding-left: 30px'>
					<a href='".e_SELF."?cat.edit.{$parent['forum_id']}'>".ADMIN_EDIT_ICON."</a>
					<a href='".e_SELF."?delete.{$parent['forum_id']}'>".ADMIN_DELETE_ICON."</a>
					</div>
					";
				}
				$text .= "</td></tr>";

				$forumCount = $e107->sql->db_Select('forum', '*', "forum_parent='".$parent['forum_id']."' AND forum_sub = 0 ORDER BY forum_order ASC");
				if (!$forumCount)
				{
					$text .= "<td colspan='4' style='text-align:center' class='forumheader3'>".FORLAN_29."</td>";
				}
				else
				{
					$forumList = array();
					while ($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
					{
						$forumList[] = $row;
					}
					foreach($forumList as $forum)
					{
						$text .= "
						<tr>
						<td style='width:5%; text-align:center' class='forumheader3'>".IMAGE_new."</td>\n<td style='width:55%' class='forumheader3'><a href='".$e107->url->getUrl('forum', 'forum', array('func' => 'view', 'id'=>$forum['forum_id']))."'>".$e107->tp->toHTML($forum['forum_name'])."</a>";
//						<td style='width:5%; text-align:center' class='forumheader3'>".IMAGE_new."</td>\n<td style='width:55%' class='forumheader3'><a href='".e_PLUGIN."forum/forum_viewforum.php?{$forum['forum_id']}'>".$e107->tp->toHTML($forum['forum_name'])."</a>";

						$text .= "
						<br /><span class='smallblacktext'>".$e107->tp->toHTML($forum['forum_description'])."&nbsp;</span>
						<br /><b>".FORLAN_140.":</b> ".$e107->user_class->uc_get_classname($forum['forum_class'])."&nbsp;&nbsp;<b>".FORLAN_141.":</b> ".$e107->user_class->uc_get_classname($forum['forum_postclass'])."

						</td>

						<td colspan='2' class='forumheader3' style='text-align:center'>";

						if ($mode)
						{
							$text .= "<select name='forum_order[]' class='tbox'>\n";
							for($a = 1; $a <= $forumCount; $a++)
							{
								$sel = ($forum['forum_order'] == $a ? "selected='selected'" : '');
								$text .= "<option value='{$forum['forum_id']}.{$a}' {$sel}>{$a}</option>\n";
							}
							$text .= "</select>";
						}
						else
						{
							$sub_img = count($subList[$forum['forum_parent']][$forum['forum_id']]) ? IMAGE_sub : IMAGE_nosub;
							$text .= "
							<div style='text-align:left; padding-left: 30px'>
							<a href='".e_SELF."?create.edit.{$forum['forum_id']}'>".ADMIN_EDIT_ICON."</a>
							<a href='".e_SELF."?delete.{$forum['forum_id']}'>".ADMIN_DELETE_ICON."</a>
							&nbsp;&nbsp;<a href='".e_SELF."?subs.{$forum['forum_id']}'>".$sub_img."</a> (".count($subList[$forum['forum_parent']][$forum['forum_id']]).")
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
			$e107->ns->tablerender(FORLAN_30, $text);
		}
		else
		{
			$text .= "<tr>\n<td colspan='4' style='text-align:center' class='forumheader'>\n<input class='button' type='submit' name='update_order' value='".FORLAN_72."' />\n</td>\n</tr>\n</table>\n</form>";
			$e107->ns->tablerender(FORLAN_37, $text);
		}

	}

	function create_parents($sub_action, $id)
	{
		global $e107;

		$id = (int)$id;
		if ($sub_action == 'edit' && !$_POST['update_parent'])
		{
			if ($e107->sql->db_Select('forum', '*', "forum_id=$id"))
			{
				$row = $e107->sql->db_Fetch(MYSQL_ASSOC);
			}
		}
		else
		{
			$row = array();
			$row['forum_name'] = '';
			$row['forum_class'] = e_UC_PUBLIC;
			$row['forum_postclass'] = e_UC_MEMBER;
			$row['forum_threadclass'] = e_UC_MEMBER;
		}

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF.'?'.e_QUERY."'>
		<table style='".ADMIN_WIDTH."' class='fborder'>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_31.":</td>
		<td style='width:60%' class='forumheader3'>
		<input class='tbox' type='text' name='forum_name' size='60' value='".$e107->tp->toForm($row['forum_name'])."' maxlength='250' />
		</td>
		</tr>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_23.":<br /><span class='smalltext'>(".FORLAN_24.")</span></td>
		<td style='width:60%' class='forumheader3'>".$e107->user_class->uc_dropdown('forum_class', $row['forum_class'], 'nobody,public,member,admin,classes')."</td>
		</tr>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_142.":<br /><span class='smalltext'>(".FORLAN_143.")</span></td>
		<td style='width:60%' class='forumheader3'>".$e107->user_class->uc_dropdown("forum_postclass", $row['forum_postclass'], 'nobody,public,member,admin,classes')."</td>
		</tr>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_184.":<br /><span class='smalltext'>(".FORLAN_185.")</span></td>
		<td style='width:60%' class='forumheader3'>".$e107->user_class->uc_dropdown('forum_threadclass', $row['forum_threadclass'], 'nobody,public,member,admin,classes')."</td>
		</tr>

		<tr style='vertical-align:top'>
		<td colspan='2'  style='text-align:center' class='forumheader'>";

		if ($sub_action == 'edit')
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

		$e107->ns->tablerender(FORLAN_75, $text);
	}

	function create_forums($sub_action, $id)
	{
		global $e107;

		$id = (int)$id;
		if ($sub_action == 'edit' && !$_POST['update_forum'])
		{
			if ($e107->sql->db_Select('forum', '*', "forum_id=$id"))
			{
				$fInfo = $e107->sql->db_Fetch(MYSQL_ASSOC);
			}
		}
		else
		{
			$fInfo = array(
				'forum_parent' => 0,
				'forum_moderators' => e_UC_ADMIN,
				'forum_class' => e_UC_PUBLIC,
				'forum_postclass' => e_UC_MEMBER,
				'forum_threadclass' => e_UC_MEMBER
			);
		}

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF.'?'.e_QUERY."'>\n
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_22.":</td>
		<td style='width:60%' class='forumheader3'>";

		$e107->sql->db_Select('forum', '*', 'forum_parent=0');
		$text .= "<select name='forum_parent' class='tbox'>\n";
		while (list($fid, $fname) = $e107->sql->db_Fetch(MYSQL_NUM))
		{
			$sel = ($fid == $fInfor['forum_parent'] ? "selected='selected'" : '');
			$text .= "<option value='{$fid}' {$sel}>{$fname}</option>\n";
		}
		$text .= "</select>
		</td>
		</tr>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_31.":
		<div class='smalltext'>".FORLAN_179."</div>
		</td>
		<td style='width:60%' class='forumheader3'>
		<input class='tbox' type='text' name='forum_name' size='60' value='".$e107->tp->toForm($fInfo['forum_name'])."' maxlength='250' />
		</td>
		</tr>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_32.": </td>
		<td style='width:60%' class='forumheader3'>
		<textarea class='tbox' name='forum_description' cols='50' rows='5'>".$e107->tp->toForm($fInfo['forum_description'])."</textarea>
		</td>
		</tr>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_33.":<br /><span class='smalltext'>(".FORLAN_34.")</span></td>
		<td style='width:60%' class='forumheader3'>";
		$text .= $e107->user_class->uc_dropdown('forum_moderators', $fInfo['forum_moderators'], 'admin,classes');

		$text .= "</td>
		</tr>
		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_23.":<br /><span class='smalltext'>(".FORLAN_24.")</span></td>
		<td style='width:60%' class='forumheader3'>".$e107->user_class->uc_dropdown('forum_class', $fInfo['forum_class'], 'nobody,public,member,admin,classes')."</td>
		</tr>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_142.":<br /><span class='smalltext'>(".FORLAN_143.")</span></td>
		<td style='width:60%' class='forumheader3'>".$e107->user_class->uc_dropdown('forum_postclass', $fInfo['forum_postclass'], 'nobody,public,member,admin,classes')."</td>
		</tr>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_184.":<br /><span class='smalltext'>(".FORLAN_185.")</span></td>
		<td style='width:60%' class='forumheader3'>".$e107->user_class->uc_dropdown('forum_threadclass', $fInfo['forum_threadclass'], 'nobody,public,member,admin,classes')."</td>
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
		$e107->ns->tablerender(FORLAN_28, $text);
	}

	function show_message($message)
	{
		global $e107;
		$e107->ns->tablerender('', "<div style='text-align:center'><b>".$message."</b></div>");
	}

	function show_tools()
	{
		global $sql, $ns, $tp;
		$txt = "
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<table style='width:".ADMIN_WIDTH."'>
		<tr style='width:100%'>
		<td class='fcaption'>".FORLAN_156."</td>
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
			$txt .= "<input type='checkbox' name='forum_all' value='1' /> <strong>".FORLAN_157."</strong>";
		}
		$txt .= "
		</td>
		</tr>
		<tr>
		<td class='fcaption'>".FORLAN_158."</td>
		</tr>
		<tr>
		<td class='forumheader3'>
		<input type='checkbox' name='lastpost' value='1' /> ".FORLAN_159." <br />&nbsp;&nbsp;&nbsp;&nbsp;
		<input type='checkbox' name='lastpost_nothread' value='1' checked='checked' /> ".FORLAN_160."
		</td>
		</tr>
		<tr>
		<td class='fcaption'>".FORLAN_161."</td>
		</tr>
		<tr>
		<td class='forumheader3'>
			<input type='checkbox' name='counts' value='1' /> ".FORLAN_162."<br />
			&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='counts_threads' value='1' /><span style='text-align: center'> ".FORLAN_182."<br />".FORLAN_183."</span><br />
		</td>
		</tr>
		<tr>
		<td class='fcaption'>".FORLAN_163."</td>
		</tr>
		<tr>
		<td class='forumheader3'>
		<input type='checkbox' name='userpostcounts' value='1' /> ".FORLAN_164."<br />
		</td>
		</tr>
		<tr>
		<td class='forumheader3' style='text-align:center'>
		<input class='button' type='submit' name='tools' value='".FORLAN_165."' />
		</td>
		</tr>
		</table>
		</form>
		";
		$ns->tablerender(FORLAN_166, $txt);
	}

	function show_prefs()
	{
		global $fPref, $ns, $sql;
		$e107 = e107::getInstance();
		$emessage = eMessage::getInstance();

		$poll_installed = plugInstalled('poll');


		if(!$poll_installed)
		{
			if($fPref->get('poll') == 1)
			{
				$fPref['forum_poll'] = e_UC_NOBODY;
				$fPref->save(false, true);
			}
		}

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		<table style='".ADMIN_WIDTH."' class='fborder'>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_44."<br /><span class='smalltext'>".FORLAN_45."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($fPref->get('enclose') ? "<input type='checkbox' name='forum_enclose' value='1' checked='checked' />" : "<input type='checkbox' name='forum_enclose' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_65."<br /><span class='smalltext'>".FORLAN_46."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_title' size='15' value='".$fPref->get('title')."' maxlength='100' /></td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_47."<br /><span class='smalltext'>".FORLAN_48."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($fPref->get('notify') ? "<input type='checkbox' name='email_notify' value='1' checked='checked' />" : "<input type='checkbox' name='email_notify' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_177."<br /><span class='smalltext'>".FORLAN_178."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($fPref->get('notify_on') ? "<input type='checkbox' name='email_notify_on' value='1' checked='checked' />" : "<input type='checkbox' name='email_notify_on' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_49."<br /><span class='smalltext'>".FORLAN_50."</span></td>";
		if($poll_installed)
		{
//			<td class='forumheader'>".$e107->user_class->uc_dropdown("mods[{$f['forum_id']}]", $f['forum_moderators'], 'admin,classes')."</td>
			$text .= "<td style='width:25%;text-align:center' class='forumheader3' >".$e107->user_class->uc_dropdown('forum_poll', $fPref->get('poll'), 'admin,classes').'</td>';
		}
		else
		{
			$text .= "<td style='width:25%;text-align:center' class='forumheader3' >".FORLAN_66."</td>";
		}
		$text .= "
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_70."<br /><span class='smalltext'>".FORLAN_71." <a href='".e_ADMIN."upload.php'>".FORLAN_130."</a> ". FORLAN_131."</span>";

		if(!$pref['image_post'])
		{
			$text .= "<br /><b>".FORLAN_139."</b>";
		}
		if(!is_writable(e_PLUGIN.'forum/attachments'))
		{
			$text .= "<br /><b>Attachment dir (".e_PLUGIN_ABS.'forum/attachments'.") is not writable!</b>";
		}

		$text .= "</td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($fPref->get('attach') ? "<input type='checkbox' name='forum_attach' value='1' checked='checked' />" : "<input type='checkbox' name='forum_attach' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_134."<br /><span class='smalltext'>".FORLAN_135."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' size='3' maxlength='5' name='forum_maxwidth' value='".$fPref->get('maxwidth')."' /></td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_136."<br /><span class='smalltext'>".FORLAN_137."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($fPref->get('linkimg') ? "<input type='checkbox' name='forum_linkimg' value='1' checked='checked' />" : "<input type='checkbox' name='forum_linkimg' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_51."<br /><span class='smalltext'>".FORLAN_52."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($fPref->get('track') ? "<input type='checkbox' name='forum_track' value='1' checked='checked' />" : "<input type='checkbox' name='forum_track' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_112."<br /><span class='smalltext'>".FORLAN_113."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($fPref->get('redirect') ? "<input type='checkbox' name='forum_redirect' value='1' checked='checked' />" : "<input type='checkbox' name='forum_redirect' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_116."<br /><span class='smalltext'>".FORLAN_122."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($fPref->get('reported_post_email') ? "<input type='checkbox' name='reported_post_email' value='1' checked='checked' />" : "<input type='checkbox' name='reported_post_email' value='1' />")."</td>
		</tr>


		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_126."<br /><span class='smalltext'>".FORLAN_127."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($fPref->get('forum_tooltip') ? "<input type='checkbox' name='forum_tooltip' value='1' checked='checked' />" : "<input type='checkbox' name='forum_tooltip' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_128."<br /><span class='smalltext'>".FORLAN_129."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_tiplength' size='15' value='".$fPref->get('tiplength')."' maxlength='20' /></td>
		</tr>


		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_53."<br /><span class='smalltext'>".FORLAN_54."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_eprefix' size='15' value='".$fPref->get('eprefix')."' maxlength='20' /></td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_55."<br /><span class='smalltext'>".FORLAN_56."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_popular' size='3' value='".$fPref->get('popular')."' maxlength='3' /></td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_57."<br /><span class='smalltext'>".FORLAN_58."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_postspage' size='3' value='".$fPref->get('postspage')."' maxlength='3' /></td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_186."<br /><span class='smalltext'>".FORLAN_187."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_threadspage' size='3' value='".$fPref->get('threadspage')."' maxlength='3' /></td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_132."<br /><span class='smalltext'>".FORLAN_133."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($fPref->get('hilightsticky') ? "<input type='checkbox' name='forum_hilightsticky' value='1' checked='checked' />" : "<input type='checkbox' name='forum_hilightsticky' value='1' />")."</td>
		</tr>

		<tr>
		<td colspan='2'  style='text-align:center' class='forumheader'>
		<input class='button' type='submit' name='updateoptions' value='".FORLAN_61."' />
		</td>
		</tr>
		</table>
		</form>
		</div>";
		$ns->tablerender(FORLAN_62, $emessage->render().$text);
	}

	function show_reported ($sub_action, $id)
	{
		global $sql, $rs, $ns, $tp;
		if ($sub_action) {
			$sql -> db_Select("generic", "*", "gen_id='".$sub_action."'");
			$row = $sql -> db_Fetch();
			$sql -> db_Select("user", "*", "user_id='". $row['gen_user_id']."'");
			$user = $sql -> db_Fetch();
			$con = new convert;
			$text = "<div style='text-align: center'>
			<table class='fborder' style='".ADMIN_WIDTH."'><tr>
			<td style='width:40%' class='forumheader3'>
			".FORLAN_171.":
			</td>
			<td style='width:60%' class='forumheader3'>
			<a href='".e_PLUGIN."forum/forum_viewtopic.php?".$row['gen_intdata'].".post' rel='external'>#".$row['gen_intdata']."</a>
			</td>
			</tr>
			<tr>
			<td style='width:40%' class='forumheader3'>
			".FORLAN_173.":
			</td>
			<td style='width:60%' class='forumheader3'>
			".$row['gen_ip']."
			</td>
			</tr>
			<tr>
			<td style='width:40%' class='forumheader3'>
			".FORLAN_174.":
			</td>
			<td style='width:60%' class='forumheader3'>
			<a href='".e_BASE."user.php?id.".$user['user_id']."'>".$user['user_name']."</a>
			</td>
			</tr>
			<tr>
			<td style='width:40%' class='forumheader3'>
			".FORLAN_175.":
			</td>
			<td style='width:60%' class='forumheader3'>
			".$con -> convert_date($row['gen_datestamp'], "long")."
			</td>
			</tr>
			<tr>
			<td style='width:40%' class='forumheader3'>
			".FORLAN_176.":
			</td>
			<td style='width:60%' class='forumheader3'>
			".$row['gen_chardata']."
			</td>
			</tr>
			<tr>
			<td style='text-align:center' class='forumheader' colspan='2'>
			".$rs->form_open("post", e_SELF."?sr", "", "", "", " onsubmit=\"return confirm_('sr',".$row['gen_datestamp'].")\"")."
			".$rs->form_button("submit", "delete[reported_{$row['gen_id']}]", FORLAN_172)."
			".$rs->form_close()."
			</td>
			</tr>\n";
			$text .= "</table>";
			$text .= "</div>";
			$ns -> tablerender(FORLAN_116, $text);
		} else {
			$text = "<div style='text-align: center'>";
			if ($reported_total = $sql->db_Select("generic", "*", "gen_type='reported_post' OR gen_type='Reported Forum Post'"))
			{
				$text .= "<table class='fborder' style='".ADMIN_WIDTH."'>
				<tr>
				<td style='width:80%' class='fcaption'>".FORLAN_170."</td>
				<td style='width:20%; text-align:center' class='fcaption'>".FORLAN_80."</td>
				</tr>";
				while ($row = $sql->db_Fetch())
				{
					$text .= "<tr>
					<td style='width:80%' class='forumheader3'><a href='".e_SELF."?sr.".$row['gen_id']."'>".FORLAN_171." #".$row['gen_intdata']."</a></td>
					<td style='width:20%; text-align:center; vertical-align:top; white-space: nowrap' class='forumheader3'>
					".$rs->form_open("post", e_SELF."?sr", "", "", "", " onsubmit=\"return confirm_('sr',".$row['gen_datestamp'].")\"")."
					".$rs->form_button("submit", "delete[reported_{$row['gen_id']}]", FORLAN_172)."
					".$rs->form_close()."
					</td>
					</tr>\n";
				}
				$text .= "</table>";
			}
			else
			{
				$text .= "<div style='text-align:center'>".FORLAN_121."</div>";
			}
			$text .= "</div>";
			$ns->tablerender(FORLAN_116, $text);
		}
	}

	function show_prune()
	{
		global $ns, $sql;

		//		$sql -> db_Select("forum", "forum_id, forum_name", "forum_parent!=0 ORDER BY forum_order ASC");
		$qry = "
		SELECT f.forum_id, f.forum_name, sp.forum_name AS sub_parent, fp.forum_name AS forum_parent
		FROM #forum AS f
		LEFT JOIN #forum AS sp ON sp.forum_id = f.forum_sub
		LEFT JOIN #forum AS fp ON fp.forum_id = f.forum_parent
		WHERE f.forum_parent != 0
		ORDER BY f.forum_parent ASC, f.forum_sub, f.forum_order ASC
		";
		$sql -> db_Select_gen($qry);
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
		".FORLAN_89." <input type='radio' name='prune_type' value='delete' />&nbsp;&nbsp;&nbsp;
		".FORLAN_90." <input type='radio' name='prune_type' value='make_inactive' checked='checked' />
		</td>
		</tr>

		<tr>
		<td class='forumheader3'>".FORLAN_138.": <br />";

		foreach($forums as $forum)
		{
			$for_name = $forum['forum_parent']." -> ";
			$for_name .= ($forum['sub_parent'] ? $forum['sub_parent']." -> " : "");
			$for_name .= $forum['forum_name'];
			$text .= "<input type='checkbox' name='pruneForum[]' value='".$forum['forum_id']."' /> ".$for_name."<br />";
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


	function show_mods()
	{
		global $sql, $ns, $for, $tp;
		$e107 = e107::getInstance();
		$forumList = $for->forum_getforums('all');
		$parentList = $for->forum_getparents('list');
		$subList   = $for->forumGetSubs('bysub');

		$txt = "<form method='post' action='".e_SELF."?".e_QUERY."'><table class='fborder' style='width:100%'><tr><td> &nbsp; </td>";

		foreach($parentList as $p)
		{
			$txt .= "
			<tr>
			<td colspan='2' class='fcaption'><strong>".$tp->toHTML($p['forum_name'])."</strong></td>
			</tr>
			";

			foreach($forumList[$p['forum_id']] as $f)
			{
				$txt .= "
				<tr>
				<td class='forumheader'>{$f['forum_name']}</td>
				<td class='forumheader'>".$e107->user_class->uc_dropdown("mods[{$f['forum_id']}]", $f['forum_moderators'], 'admin,classes')."</td>
				</tr>
				";
				foreach($subList[$f['forum_id']] as $s)
				{
					$txt .= "
					<tr>
					<td class='forumheader3'>&nbsp;&nbsp;&nbsp;&nbsp;{$s['forum_name']}</td>
					<td class='forumheader3'>".$e107->user_class->uc_dropdown("mods[{$s['forum_id']}]", $s['forum_moderators'], 'admin,classes')."</td>
					</tr>
					";
				}
			}
		}
			$txt .= "
			<tr>
			<td colspan='2' class='fcaption' style='text-align:center'>
			<input class='button' type='submit' name='setMods' value='".WMGLAN_4." ".FORLAN_33."' />
			</td>
			</tr>

			</table></form>";
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
			".display_help('helpb', 1, 'addtext1', 'help1')."
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
			".display_help('helpb', 1, 'addtext2', 'help2')."
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
			".display_help('helpb', 1, 'addtext3', 'help3')."
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
