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
|     $Revision: 1.2 $
|     $Date: 2005-03-31 21:12:26 $
|     $Author: sweetas $
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
	$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order+1 WHERE user_extended_struct_order='".($_order-1)."'");
	$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order-1 WHERE user_extended_struct_id='".$_id."'");
}

if (isset($_POST['down_x']))
{
	$qs = explode(".", $_POST['id']);
	$_id = $qs[0];
	$_order = $qs[1];
	$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order-1 WHERE user_extended_struct_order='".($_order+1)."'");
	$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order+1 WHERE user_extended_struct_id='".$_id."'");
}

if (isset($_POST['add_field']))
{
	if($ue->user_extended_add($_POST['user_field'], $_POST['user_text'], $_POST['user_type'], $_POST['user_parms'], $_POST['user_values'], $_POST['user_default'], $_POST['user_required'], $_POST['user_read'], $_POST['user_write'], $_POST['user_applicable']))
	{
		$message = EXTLAN_29;
	}
}
	
if (isset($_POST['update_field'])) {
	if($ue->user_extended_modify($sub_action, $_POST['user_field'], $_POST['user_text'], $_POST['user_type'], $_POST['user_parms'], $_POST['user_values'], $_POST['user_default'], $_POST['user_required'], $_POST['user_read'], $_POST['user_write'], $_POST['user_applicable']))
	{
		$message = EXTLAN_29;
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

if($message)
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}
	
if ($action == "editext")
{
	if($sql->db_Select('user_extended_struct','*',"user_extended_struct_id = '{$sub_action}'"))
	{
		$tmp = $sql->db_Fetch();
		$user->show_extended($tmp);
	}
}
	
if (!e_QUERY || $action == "extended") {
	$user->show_extended();
}
	
require_once("footer.php");

class users_ext {
 
	 
	function show_extended($current) {
		global $sql, $ns, $ue;
		require_once(e_HANDLER."userclass_class.php");
		
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
		if($sql->db_Select('user_extended_struct','*',"1 ORDER BY user_extended_struct_order"))
		{
			//Show current extended fields
			$extendedList = $sql->db_getList();
			$i=0;
			foreach($extendedList as $ext)
			{
				$text .= "
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
					<form method='post' action='".e_SELF."?extended'>
					<input type='hidden' name='id' value='{$ext['user_extended_struct_id']}.{$ext['user_extended_struct_order']}' />
				";
				if($i > 0)
				{
					$text .= "
					<input type='image' alt='' title='".EXTLAN_26."' src='".e_IMAGE."/admin_images/up.png' name='up' value='{$ext['user_extended_struct_id']}' />
					";
				}
				if($i <= count($extendedList)-2)
				{
					$text .= "<input type='image' alt='' title='".EXTLAN_25."' src='".e_IMAGE."/admin_images/down.png' name='down' value='{$ext['user_extended_struct_id']}' />";
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
			
		//Show add/edit form
		$text .= "
			</table>
			<form method='post' action='".e_SELF."?".e_QUERY."'>
			";
		$text .= "<div><br /></div><table style='".ADMIN_WIDTH."' class='fborder'>  ";
		$text .= "

			<tr>
				<td style='width:30%' class='forumheader3'>".EXTLAN_10.":</td>
				<td style='width:70%' class='forumheader3' colspan='3'>user_
				";
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
		};
		 
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
		 
		if (!is_array($current)) {
			$text .= "
				<input class='button' type='submit' name='add_field' value='".EXTLAN_23."' />
				";
		} else {
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
}
?>