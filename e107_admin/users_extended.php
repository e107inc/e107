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
|     $Source: /cvs_backup/e107_0.7/e107_admin/users_extended.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-04-06 03:52:34 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("4")) {
	header("location:".e_BASE."index.php");
	exit;
}
if (isset($_POST['cancel']))
{
	header("location:".e_SELF);
	exit;
}

$e_sub_cat = 'user_extended';
$user = new users_ext;
require_once("auth.php");
require_once(e_HANDLER."user_extended_class.php");
require_once(e_HANDLER."userclass_class.php");

$ue = new e107_user_extended;

if (e_QUERY) {
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	$id = $tmp[2];
	unset($tmp);
}

if (isset($_POST['up_x']))
{
	$qs = explode(".", $_POST['id']);
	$_id = $qs[0];
	$_order = $qs[1];
	$_parent = $qs[2];
	$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order+1 WHERE user_extended_struct_type > 0 AND user_extended_struct_parent = {$_parent} AND user_extended_struct_order<='".($_order)."'");
	$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order-1 WHERE user_extended_struct_type > 0 AND user_extended_struct_parent = {$_parent} AND user_extended_struct_id='".$_id."'");
}

if (isset($_POST['down_x']))
{
	$qs = explode(".", $_POST['id']);
	$_id = $qs[0];
	$_order = $qs[1];
	$_parent = $qs[2];
	$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order-1 WHERE user_extended_struct_type > 0 AND user_extended_struct_parent = {$_parent} AND user_extended_struct_order='".($_order)."'");
	$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order+1 WHERE user_extended_struct_type > 0 AND user_extended_struct_parent = {$_parent} AND user_extended_struct_id='".$_id."'");
}


if (isset($_POST['catup_x']))
{
	$qs = explode(".", $_POST['id']);
	$_id = $qs[0];
	$_order = $qs[1];
	$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order+1 WHERE user_extended_struct_parent = 0 AND user_extended_struct_order='".($_order)."'");
	$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order-1 WHERE user_extended_struct_parent = 0 AND user_extended_struct_id='".$_id."'");
}

if (isset($_POST['catdown_x']))
{
	$qs = explode(".", $_POST['id']);
	$_id = $qs[0];
	$_order = $qs[1];
	$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order-1 WHERE user_extended_struct_type = 0 AND user_extended_struct_order='".($_order)."'");
	$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order+1 WHERE user_extended_struct_type = 0 AND user_extended_struct_id='".$_id."'");
}

if (isset($_POST['add_field']))
{
	if($ue->user_extended_add($_POST['user_field'], $_POST['user_text'], $_POST['user_type'], $_POST['user_parms'], $_POST['user_values'], $_POST['user_default'], $_POST['user_required'], $_POST['user_read'], $_POST['user_write'], $_POST['user_applicable'], 0, $_POST['user_parent']))
	{
		$message = EXTLAN_29;
	}
}

if (isset($_POST['update_field'])) {
	if($ue->user_extended_modify($sub_action, $_POST['user_field'], $_POST['user_text'], $_POST['user_type'], $_POST['user_parms'], $_POST['user_values'], $_POST['user_default'], $_POST['user_required'], $_POST['user_read'], $_POST['user_write'], $_POST['user_applicable'], $_POST['user_parent']))
	{
		$message = EXTLAN_29;
	}
}

if (isset($_POST['update_category']))
{
	$name = trim($tp->toHTML($_POST['user_field']));
	if($sql->db_Update("user_extended_struct","user_extended_struct_name = '{$name}', user_extended_struct_read = '{$_POST['user_read']}', user_extended_struct_write = '{$_POST['user_write']}', user_extended_struct_applicable = '{$_POST['user_applicable']}' WHERE user_extended_struct_id = '{$sub_action}'"))
	{
		$message = EXTLAN_43;
	}
	else
	{
		$message = LAN_UPDATED_FAILED;
	}
}

if (isset($_POST['add_category']))
{
	$name = $tp->toHTML($_POST['user_field']);
	if($sql->db_Insert("user_extended_struct","'0', '$name', '', 0, '', '', '', '{$_POST['user_read']}', '{$_POST['user_write']}', '', '', '{$_POST['user_applicable']}', '0', '0'"))
	{
		$message = EXTLAN_40;
	}
	else
	{
		$message = LAN_CREATED_FAILED;
	}
}

if ($_POST['eu_action'] == "delext")
{
	list($_id, $_name) = explode(",",$_POST['key']);
	if($ue->user_extended_remove($_id, $_name))
	{
		$message = EXTLAN_30;
	}
}

if ($_POST['eu_action'] == "delcat")
{
	list($_id, $_name) = explode(",",$_POST['key']);
	if($ue->user_extended_remove($_id, $_name))
	{
		$message = EXTLAN_41;
	}
}

if($message)
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

if (!e_QUERY || $action == 'main')
{
	$user->show_extended();
}

if ($action == "editext")
{
	if($sql->db_Select('user_extended_struct','*',"user_extended_struct_id = '{$sub_action}'"))
	{
		$tmp = $sql->db_Fetch();
		$user->show_extended($tmp);
	}
	$action = 'main';
}

if($action == 'cat')
{
	if(is_numeric($sub_action))
	{
		if($sql->db_Select('user_extended_struct','*',"user_extended_struct_id = '{$sub_action}'"))
		{
			$tmp = $sql->db_Fetch();
		}
	}
	$user->show_categories($tmp);
}

require_once("footer.php");

class users_ext
{

	function show_extended($current)
	{
		global $sql, $ns, $ue;

		$text = "<div style='text-align:center'>";
		$text .= "
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td class='fcaption'>".EXTLAN_1."</td>
		<td class='fcaption'>".EXTLAN_2."</td>
		<td class='fcaption'>".EXTLAN_3."</td>
		<td class='fcaption'>".EXTLAN_4."</td>
		<td class='fcaption'>".EXTLAN_5."</td>
		<td class='fcaption'>".EXTLAN_6."</td>
		<td class='fcaption'>".EXTLAN_7."</td>
		<td class='fcaption'>&nbsp;</td>
		<td class='fcaption'>".EXTLAN_8."</td>
		</tr>
		";

		$catList = $ue->user_extended_get_categories();
		$catList[0][0] = array('user_extended_struct_name' => EXTLAN_36);
		$catNums = array_keys($catList);
		$extendedList = $ue->user_extended_get_fields();
		foreach($catNums as $cn)
		{
			$text .= "
			<tr>
			<td class='fcaption' colspan='9' style='text-align:center'>{$catList[$cn][0]['user_extended_struct_name']}</td>
			</tr>
			";

			$i=0;
			if(count($extendedList))
			{
				//	Show current extended fields
				foreach($extendedList[$cn] as $ext)
				{
					$text .= "
					<tr>
					<td class='forumheader3'>{$ext['user_extended_struct_name']}<br />[{$ext['user_extended_struct_text']}]</td>
					<td class='forumheader3'>".$ue->user_extended_types[$ext['user_extended_struct_type']]."</td>
					<td class='forumheader3'>{$ext['user_extended_struct_values']}";
					if($ext['user_extended_struct_values'])
					{
						$text .= "<br />[{$ext['user_extended_struct_default']}]";
					}
					$text .= "
					</td>
					<td class='forumheader3'>".($ext['user_extended_struct_required'] ? LAN_YES : LAN_NO)."</td>
					<td class='forumheader3'>".r_userclass_name($ext['user_extended_struct_applicable'])."</td>
					<td class='forumheader3'>".r_userclass_name($ext['user_extended_struct_read'])."</td>
					<td class='forumheader3'>".r_userclass_name($ext['user_extended_struct_write'])."</td>
					<td class='forumheader3'>
					<form method='post' action='".e_SELF."'>
					<input type='hidden' name='id' value='{$ext['user_extended_struct_id']}.{$ext['user_extended_struct_order']}.{$ext['user_extended_struct_parent']}' />
					";
					if($i > 0)
					{
						$text .= "
						<input type='image' alt='' title='".EXTLAN_26."' src='".e_IMAGE."/admin_images/up.png' name='up' value='{$ext['user_extended_struct_id']}.{$ext['user_extended_struct_order']}.{$ext['user_extended_struct_parent']}' />
						";
					}
					if($i <= count($extendedList[$cn])-2)
					{
						$text .= "<input type='image' alt='' title='".EXTLAN_25."' src='".e_IMAGE."/admin_images/down.png' name='down' value='{$ext['user_extended_struct_id']}.{$ext['user_extended_struct_order']}.{$ext['user_extended_struct_parent']}' />";
					}
					$text .= "
					</form>
					</td>
					<td class='forumheader3' style='text-align:center;'>
					<a style='text-decoration:none' href='".e_SELF."?editext.{$ext['user_extended_struct_id']}'>".ADMIN_EDIT_ICON."</a>
					&nbsp;
					<form method='post' action='".e_SELF."?extended' onsubmit='return confirm(\"".EXTLAN_27."\")'>
					<input type='hidden' name='eu_action' value='delext' />
					<input type='hidden' name='key' value='{$ext['user_extended_struct_id']},{$ext['user_extended_struct_name']}' />
					<input type='image' title='".LAN_DELETE."' name='eudel' src='".ADMIN_DELETE_ICON_PATH."' />
					</form>
					</td>
					</tr>
					";
					$i++;
				}
			}
			else
			{
				$text .= "
				<tr>
				<td colspan='8' class='forumheader3' style='text-align:center'>".EXTLAN_28."</td>
				</tr>
				";
			}
		}
		//Show add/edit form
		$text .= "
		</table>
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		";
		$text .= "<div><br /></div><table style='".ADMIN_WIDTH."' class='fborder'>  ";
		$text .= "

		<tr>
		<td style='width:30%' class='forumheader3'>".EXTLAN_10.":</td>
		<td style='width:70%' class='forumheader3' colspan='3'>user_";
		if(is_array($current))
		{
			$text .= $current['user_extended_struct_name']."
			<input type='hidden' name='user_field' value='".$current['user_extended_struct_name']."' />
			";
		}
		else
		{
			$text .= "
			<input class='tbox' type='text' name='user_field' size='40' value='".$current['user_extended_struct_name']."' maxlength='50' />
			";
		}
		$text .= "
		<br /><span class='smalltext'>".EXTLAN_11."</span>
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".EXTLAN_12.":</td>
		<td style='width:70%' class='forumheader3' colspan='3'>
		<input class='tbox' type='text' name='user_text' size='40' value='".$current['user_extended_struct_text']."' maxlength='50' /><br />
		<span class='smalltext'>".EXTLAN_13."</span>
		</td>
		</tr>
		";

		$text .= "<tr>
		<td style='width:30%' class='forumheader3'>".EXTLAN_14."</td>
		<td style='width:70%' class='forumheader3' colspan='3'>
		<select class='tbox' name='user_type'>";
		foreach($ue->user_extended_types as $key => $val)
		{
			$selected = ($current['user_extended_struct_type'] == $key) ? " selected='selected'": "";
			$text .= "<option value='".$key."' $selected>".$val."</option>";
		}

		$text .= "
		</select></td></tr>";

		$text .= "
		<tr>
		<td style='width:30%' class='forumheader3'>".EXTLAN_15."</td>
		<td style='width:70%' class='forumheader3' colspan='3'>
		<input class='tbox' type='text' name='user_parms' size='40' value='{$current['user_extended_struct_parms']}' /><br />
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".EXTLAN_3."</td>
		<td style='width:70%' class='forumheader3' colspan='3'>
		<input class='tbox' type='text' name='user_values' size='40' value='{$current['user_extended_struct_values']}' /><br />
		<span class='smalltext'>".EXTLAN_17."</span>
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".EXTLAN_16."</td>
		<td style='width:70%' class='forumheader3' colspan='3'>
		<input class='tbox' type='text' name='user_default' size='40' value='{$current['user_extended_struct_default']}' />
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".EXTLAN_44."</td>
		<td style='width:70%' class='forumheader3' colspan='3'>
		<select class='tbox' name='user_parent'>";
		foreach($catNums as $k)
		{
			$sel = ($k == $current['user_extended_struct_parent']) ? " selected='selected' " : "";
			$text .= "<option value='{$k}' {$sel}>{$catList[$k][0]['user_extended_struct_name']}</option>\n";
		}
		$text .= "</select>
		
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".EXTLAN_18."</td>
		<td style='width:70%' class='forumheader3' colspan='3'>
		<select class='tbox' type='text' name='user_required'>
		";
		if($current['user_extended_struct_required'])
		{
			$text .= "
			<option value='1' selected='selected'>".LAN_YES."
			<option value='0'>".LAN_NO;
		}
		else
		{
			$text .= "
			<option value='1'>".LAN_YES."
			<option value='0' selected='selected'>".LAN_NO;
		}
		$text .= "
		</select>
		<br />
		<span class='smalltext'>".EXTLAN_19."</span>
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".EXTLAN_5."</td>
		<td style='width:70%' class='forumheader3' colspan='3'>
		".r_userclass("user_applicable", $current['user_extended_struct_applicable'], 'off', 'member, admin, classes')."<br /><span class='smalltext'>".EXTLAN_20."</span>
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".EXTLAN_6."</td>
		<td style='width:70%' class='forumheader3' colspan='3'>
		".r_userclass("user_read", $current['user_extended_struct_read'], 'off', 'member, admin, classes')."<br /><span class='smalltext'>".EXTLAN_22."</span>
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".EXTLAN_7."</td>
		<td style='width:70%' class='forumheader3' colspan='3'>
		".r_userclass("user_write", $current['user_extended_struct_write'], 'off', 'member, admin, classes')."<br /><span class='smalltext'>".EXTLAN_21."</span>
		</td>
		</tr>";


		$text .= "<tr>
		<td colspan='4' style='text-align:center' class='forumheader'>";

		if (!is_array($current))
		{
			$text .= "
			<input class='button' type='submit' name='add_field' value='".EXTLAN_23."' />
			";
		}
		else
		{
			$text .= "
			<input class='button' type='submit' name='update_field' value='".EXTLAN_24."' /> &nbsp; &nbsp;
			<input class='button' type='submit' name='cancel' value='".EXTLAN_33."' />
			";
		}
		// ======= end added by Cam.
		$text .= "</td>
		</tr>

		</table></form></div>";
		$ns->tablerender(EXTLAN_9, $text);
	}

	function show_categories($current)
	{
		global $sql, $ns, $ue;

		$text = "<div style='text-align:center'>";
		$text .= "
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td class='fcaption'>".EXTLAN_1."</td>
		<td class='fcaption'>".EXTLAN_5."</td>
		<td class='fcaption'>".EXTLAN_6."</td>
		<td class='fcaption'>".EXTLAN_7."</td>
		<td class='fcaption'>&nbsp;</td>
		<td class='fcaption'>".EXTLAN_8."</td>
		</tr>
		";
		$catList = $ue->user_extended_get_categories(FALSE);
		if(count($catList))
		{
			//			Show current categories
			$i=0;
			foreach($catList as $ext)
			{
				$text .= "
				<td class='forumheader3'>{$ext['user_extended_struct_name']}</td>
				</td>
				<td class='forumheader3'>".r_userclass_name($ext['user_extended_struct_applicable'])."</td>
				<td class='forumheader3'>".r_userclass_name($ext['user_extended_struct_read'])."</td>
				<td class='forumheader3'>".r_userclass_name($ext['user_extended_struct_write'])."</td>
				<td class='forumheader3'>
				<form method='post' action='".e_SELF."?cat'>
				<input type='hidden' name='id' value='{$ext['user_extended_struct_id']}.{$ext['user_extended_struct_order']}' />
				";
				if($i > 0)
				{
					$text .= "
					<input type='image' alt='' title='".EXTLAN_26."' src='".e_IMAGE."/admin_images/up.png' name='catup' value='{$ext['user_extended_struct_id']}' />
					";
				}
				if($i <= count($catList)-2)
				{
					$text .= "<input type='image' alt='' title='".EXTLAN_25."' src='".e_IMAGE."/admin_images/down.png' name='catdown' value='{$ext['user_extended_struct_id']}' />";
				}
				$text .= "
				</form>
				</td>
				<td class='forumheader3' style='text-align:center;'>
				<a style='text-decoration:none' href='".e_SELF."?cat.{$ext['user_extended_struct_id']}'>".ADMIN_EDIT_ICON."</a>
				&nbsp;
				<form method='post' action='".e_SELF."?cat' onsubmit='return confirm(\"".EXTLAN_27."\")'>
				<input type='hidden' name='eu_action' value='delcat' />
				<input type='hidden' name='key' value='{$ext['user_extended_struct_id']},{$ext['user_extended_struct_name']}' />
				<input type='image' title='".LAN_DELETE."' name='eudel' src='".ADMIN_DELETE_ICON_PATH."' />
				</form>
				</td>
				</tr>
				";
				$i++;
			}
		}
		else
		{
			$text .= "
			<tr>
			<td colspan='8' class='forumheader3' style='text-align:center'>".EXTLAN_37."</td>
			</tr>
			";
		}

		//Show add/edit form
		$text .= "
		</table>
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		";
		$text .= "<div><br /></div><table style='".ADMIN_WIDTH."' class='fborder'>  ";
		$text .= "

		<tr>
		<td style='width:30%' class='forumheader3'>".EXTLAN_38.":</td>
		<td style='width:70%' class='forumheader3' colspan='3'>
		<input class='tbox' type='text' name='user_field' size='40' value='".$current['user_extended_struct_name']."' maxlength='50' />
		<br /><span class='smalltext'>".EXTLAN_11."</span>
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".EXTLAN_5."</td>
		<td style='width:70%' class='forumheader3' colspan='3'>
		".r_userclass("user_applicable", $current['user_extended_struct_applicable'], 'off', 'member, admin, classes')."<br /><span class='smalltext'>".EXTLAN_20."</span>
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".EXTLAN_6."</td>
		<td style='width:70%' class='forumheader3' colspan='3'>
		".r_userclass("user_read", $current['user_extended_struct_read'], 'off', 'member, admin, classes')."<br /><span class='smalltext'>".EXTLAN_22."</span>
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".EXTLAN_7."</td>
		<td style='width:70%' class='forumheader3' colspan='3'>
		".r_userclass("user_write", $current['user_extended_struct_write'], 'off', 'member, admin, classes')."<br /><span class='smalltext'>".EXTLAN_21."</span>
		</td>
		</tr>";


		$text .= "<tr>
		<td colspan='4' style='text-align:center' class='forumheader'>";

		if (!is_array($current))
		{
			$text .= "
			<input class='button' type='submit' name='add_category' value='".EXTLAN_39."' />
			";
		}
		else
		{
			$text .= "
			<input class='button' type='submit' name='update_category' value='".EXTLAN_42."' /> &nbsp; &nbsp;
			<input class='button' type='submit' name='cancel_cat' value='".EXTLAN_33."' />
			";
		}
		// ======= end added by Cam.
		$text .= "</td>
		</tr>

		</table></form></div>";
		$ns->tablerender(EXTLAN_9, $text);
	}

	function show_options($action) {
		// ##### Display options ---------------------------------------------------------------------------------------------------------
		if ($action == "") {
			$action = "main";
		}
		// ##### Display options ---------------------------------------------------------------------------------------------------------
		$var['main']['text'] = EXTLAN_34;
		$var['main']['link'] = e_SELF;

		$var['cat']['text'] = EXTLAN_35;
		$var['cat']['link'] = e_SELF."?cat";

		show_admin_menu(EXTLAN_9, $action, $var);
	}

}
function users_extended_adminmenu() {
	global $user;
	global $action;
	$user->show_options($action);
}

?>