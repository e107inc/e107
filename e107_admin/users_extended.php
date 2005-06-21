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
|     $Revision: 1.18 $
|     $Date: 2005-06-21 17:03:06 $
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
$curtype = '1';
require_once(e_HANDLER."calendar/calendar_class.php");
$cal = new DHTML_Calendar(true);
require_once("auth.php");
require_once(e_HANDLER."user_extended_class.php");
require_once(e_HANDLER."userclass_class.php");
require_once("users_extended_predefined.php");


$ue = new e107_user_extended;

if (e_QUERY)
{
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
	if($_POST['user_type']==4){
    	$_POST['user_values'] = array($_POST['table_db'],$_POST['field_id'],$_POST['field_value'],$_POST['field_order']);
	}
	$new_values = make_delimited($_POST['user_values']);
	$new_parms = $tp->toDB($_POST['user_include']."^,^".$_POST['user_regex']."^,^".$_POST['user_regexfail']."^,^".$_POST['user_hide']);
	if($ue->user_extended_add($_POST['user_field'], $_POST['user_text'], $_POST['user_type'], $new_parms, $new_values, $_POST['user_default'], $_POST['user_required'], $_POST['user_read'], $_POST['user_write'], $_POST['user_applicable'], 0, $_POST['user_parent']))
	{
		$message = EXTLAN_29;
	}
}

if (isset($_POST['update_field'])) {
	if($_POST['user_type']==4){
    	$_POST['user_values'] = array($_POST['table_db'],$_POST['field_id'],$_POST['field_value'],$_POST['field_order']);
	}
	$upd_values = make_delimited($_POST['user_values']);
	$upd_parms = $tp->toDB($_POST['user_include']."^,^".$_POST['user_regex']."^,^".$_POST['user_regexfail']."^,^".$_POST['user_hide']);
	if($ue->user_extended_modify($sub_action, $_POST['user_field'], $_POST['user_text'], $_POST['user_type'], $upd_parms, $upd_values, $_POST['user_default'], $_POST['user_required'], $_POST['user_read'], $_POST['user_write'], $_POST['user_applicable'], $_POST['user_parent']))
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

if(isset($_POST['activate']))
{
	$message .= field_activate();
}

if(isset($_POST['deactivate']))
{
	$message .= field_deactivate();
}

if($message)
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

if(isset($_POST['table_db']) && !$_POST['add_field'] && !$_POST['update_field']){
	$action = "continue";
	$current['user_extended_struct_name'] = $_POST['user_field'];
    $current['user_extended_struct_parms'] = $_POST['user_include']."^,^".$_POST['user_regex']."^,^".$_POST['user_regexfail']."^,^".$_POST['user_hide'];
    $current['user_extended_struct_text'] = $_POST['user_text'];
	$current['user_extended_struct_type'] = $_POST['user_type'];
	$user->show_extended($current);
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
	else
	{
		$user->show_extended('new');
	}
}

if($action == 'pre')
{
	show_predefined();
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
		global $sql, $ns, $ue, $curtype, $tp, $mySQLdefaultdb, $action, $sub_action;

		$catList = $ue->user_extended_get_categories();
		$catList[0][0] = array('user_extended_struct_name' => EXTLAN_36);
		$catNums = array_keys($catList);
		$extendedList = $ue->user_extended_get_fields();

		if(!$current){
			$text = "<div style='text-align:center'>";
			$text .= "
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td class='fcaption'>".EXTLAN_1."</td>
			<td class='fcaption'>".EXTLAN_2."</td>";
		//	$text .="<td class='fcaption'>".EXTLAN_3."</td>";
			$text .="<td class='fcaption'>".EXTLAN_4."</td>
			<td class='fcaption'>".EXTLAN_5."</td>
			<td class='fcaption'>".EXTLAN_6."</td>
			<td class='fcaption'>".EXTLAN_7."</td>
			<td class='fcaption'>&nbsp;</td>
			<td class='fcaption'>".EXTLAN_8."</td>
			</tr>
			";

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
					foreach($extendedList[$cn] as $ext)	{
					$fname = "user_".$ext['user_extended_struct_name'];
					$uVal = str_replace(chr(1), "", $curVal[$fname]);
						$text .= "
						<tr>
						<td class='forumheader3'>{$ext['user_extended_struct_name']}<br />[{$ext['user_extended_struct_text']}]</td>
						<td class='forumheader3'>".$ue->user_extended_edit($ext,$uVal)."</td>
						<td class='forumheader3'>".($ext['user_extended_struct_required'] ? LAN_YES : LAN_NO)."</td>
						<td class='forumheader3'>".r_userclass_name($ext['user_extended_struct_applicable'])."</td>
						<td class='forumheader3'>".r_userclass_name($ext['user_extended_struct_read'])."</td>
						<td class='forumheader3'>".r_userclass_name($ext['user_extended_struct_write'])."</td>
						<td class='forumheader3' style='width:5px'>
						<form method='post' action='".e_SELF."'>
						<input type='hidden' name='id' value='{$ext['user_extended_struct_id']}.{$ext['user_extended_struct_order']}.{$ext['user_extended_struct_parent']}' />
						";
						if($i > 0){
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
						<td class='forumheader3' style='width:50px;text-align:center;'>
						<form method='post' action='".e_SELF."?extended' onsubmit='return confirm(\"".EXTLAN_27."\")'>
						<a style='text-decoration:none' href='".e_SELF."?editext.{$ext['user_extended_struct_id']}'>".ADMIN_EDIT_ICON."</a>
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
			</table>";
		}
		else
		{
			if($current == 'new')
			{
				$current = '';
			}
			list($current_include, $current_regex, $current_regexfail, $current_hide) = explode("^,^",$current['user_extended_struct_parms']);
			$text .= "
			<form method='post' action='".e_SELF."?".e_QUERY."'>
			";
			$text .= "<div><br /></div><table style='".ADMIN_WIDTH."' class='fborder'>  ";
			$text .= "

			<tr>
			<td style='width:30%;vertical-align:top' class='forumheader3'>".EXTLAN_10.":</td>
			<td style='width:70%' class='forumheader3' colspan='3'>user_";
			if(is_array($current) && $current['user_extended_struct_name'])
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
			<td style='width:30%;vertical-align:top' class='forumheader3'>".EXTLAN_12.":</td>
			<td style='width:70%' class='forumheader3' colspan='3'>
			<input class='tbox' type='text' name='user_text' size='40' value='".$current['user_extended_struct_text']."' maxlength='50' /><br />
			<span class='smalltext'>".EXTLAN_13."</span>
			</td>
			</tr>
			";

			$text .= "<tr>
			<td style='width:30%;vertical-align:top' class='forumheader3'>".EXTLAN_14."</td>
			<td style='width:70%' class='forumheader3' colspan='3'>
			<select onchange='changeHelp(this.value)' class='tbox' name='user_type' id='user_type'>";
			foreach($ue->user_extended_types as $key => $val)
			{
				$selected = ($current['user_extended_struct_type'] == $key) ? " selected='selected'": "";
				$text .= "<option value='".$key."' $selected>".$val."</option>";
			}
			$curtype = $current['user_extended_struct_type'];
			if(!$curtype)
			{
				$curtype = '1';
			}
			$text .= "
			</select>
			</td></tr>";



			$text .= "
			<tr>
			<td style='width:30%;vertical-align:top' class='forumheader3'>".EXTLAN_3."</td>
			<td style='width:70%' class='forumheader3' colspan='3'>";
  // Start of Values ---------------------------------

      		$val_hide = ($current['user_extended_struct_type'] != 4) ? "visible" : "none";

			$text .= "<div id='values' style='display:$val_hide'>\n";
			$text .= "<div id='value_container' >\n";
			$curVals = explode(",",$current['user_extended_struct_values']);
			if(count($curVals) == 0){
				$curVals[]='';
			}
			$i=0;
			foreach($curVals as $v){
				$id = $i ? "" : " id='value_line'";
				$i++;
				$text .= "
				<span {$id}>
				<input class='tbox' type='text' name='user_values[]' size='40' value='{$v}' /></span><br />";
			}
			$text .= "
			</div>
			<input type='button' class='button' value='".EXTLAN_48."' onclick=\"duplicateHTML('value_line','value_container');\"  />
			<br /><span class='smalltext'>".EXTLAN_17."</span></div>";
// End of Values. --------------------------------------
       		$db_hide = ($current['user_extended_struct_type'] == 4) ? "visible" : "none";

			$text .= "<div id='db_mode' style='display:$db_hide'>\n";
			$text .= "<table style='width:70%;margin-left:0px'><tr><td>";
            $text .= EXTLAN_62."</td><td style='70%'><select style='width:99%' class='tbox' name='table_db' onchange=\"this.form.submit()\" >
            <option value='' class='caption'>".EXTLAN_61."</option>\n";
			$result = mysql_list_tables($mySQLdefaultdb);
			while ($row2 = mysql_fetch_row($result)){
				$fld = str_replace(MPREFIX,"",$row2[0]);
				$selected =  ($_POST['table_db'] == $fld || $curVals[0] == $fld) ? " selected='selected'" : "";
         		$text .= (eregi(MPREFIX,$row2[0])) ? "<option value=\"".$fld."\" $selected>".$fld."</option>\n" : "";
			}
			$text .= " </select></td></tr>";
     	if($_POST['table_db'] || $curVals[0]){
			// Field ID
			$text .= "<tr><td>".EXTLAN_63."</td><td><select style='width:99%' class='tbox' name='field_id' >\n
			<option value='' class='caption'>".EXTLAN_61."</option>\n";
			$table_list = ($_POST['table_db']) ? $_POST['table_db'] : $curVals[0] ;
			if($sql -> db_Select_gen("DESCRIBE ".MPREFIX."{$table_list}")){
		   		while($row3 = $sql -> db_Fetch()){
    				$field_name=$row3[0];
					$selected =  ($curVals[1] == $field_name) ? " selected='selected' " : "";
					$text .="<option value=\"$field_name\" $selected>".$field_name."</option>\n";
				}
			}
    		$text .= " </select></td></tr><tr><td>";
             // Field Value
			$text .= EXTLAN_64."</td><td><select style='width:99%' class='tbox' name='field_value' >
			<option value='' class='caption'>".EXTLAN_61."</option>\n";
			$table_list = ($_POST['table_db']) ? $_POST['table_db'] : $curVals[0] ;
			if($sql -> db_Select_gen("DESCRIBE ".MPREFIX."{$table_list}")){
		   		while($row3 = $sql -> db_Fetch()){
    				$field_name=$row3[0];
					$selected =  ($curVals[2] == $field_name) ? " selected='selected' " : "";
					$text .="<option value=\"$field_name\" $selected>".$field_name."</option>\n";
				}
			}
    		$text .= " </select></td></tr><tr><td>";

			$text .= LAN_ORDER."</td><td><select style='width:99%' class='tbox' name='field_order' >
			<option value='' class='caption'>".EXTLAN_61."</option>\n";
			$table_list = ($_POST['table_db']) ? $_POST['table_db'] : $curVals[0] ;
			if($sql -> db_Select_gen("DESCRIBE ".MPREFIX."{$table_list}")){
		   		while($row3 = $sql -> db_Fetch()){
    				$field_name=$row3[0];
					$selected =  ($curVals[3] == $field_name) ? " selected='selected' " : "";
					$text .="<option value=\"$field_name\" $selected>".$field_name."</option>\n";
				}
			}
    		$text .= " </select></td></tr>";

     	}
        $text .= "</table></div>";
// ---------------------------------------------------------
			$text .= "
			</td>
			</tr>

			<tr>
			<td style='width:30%;vertical-align:top' class='forumheader3'>".EXTLAN_16."</td>
			<td style='width:70%' class='forumheader3' colspan='3'>
			<input class='tbox' type='text' name='user_default' size='40' value='{$current['user_extended_struct_default']}' />
			</td>
			</tr>


			<tr>
			<td style='width:30%;vertical-align:top' class='forumheader3'>".EXTLAN_15."</td>
			<td style='width:70%' class='forumheader3' colspan='3'>
			<textarea class='tbox' name='user_include' cols='60' rows='2'>{$current_include}</textarea><br />
			<span class='smalltext'>".EXTLAN_51."</span><br />
			</td>
			</tr>

			<tr>
			<td style='width:30%;vertical-align:top' class='forumheader3'>".EXTLAN_52."</td>
			<td style='width:70%' class='forumheader3' colspan='3'>
			<input class='tbox' type='text' name='user_regex' size='30' value='{$current_regex}' /><br />
			<span class='smalltext'>".EXTLAN_53."</span><br />
			</td>
			</tr>

			<tr>
			<td style='width:30%;vertical-align:top' class='forumheader3'>".EXTLAN_54."</td>
			<td style='width:70%' class='forumheader3' colspan='3'>
			<input class='tbox' type='text' name='user_regexfail' size='40' value='{$current_regexfail}' /><br />
			<span class='smalltext'>".EXTLAN_55."</span><br />
			</td>
			</tr>

			<tr>
			<td style='width:30%;vertical-align:top' class='forumheader3'>".EXTLAN_44."</td>
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
			<td style='width:30%;vertical-align:top' class='forumheader3'>".EXTLAN_18."</td>
			<td style='width:70%' class='forumheader3' colspan='3'>
			<select class='tbox' name='user_required'>
			";
			if($current['user_extended_struct_required'])
			{
				$text .= "
				<option value='1' selected='selected'>".LAN_YES."</option>
				<option value='0'>".LAN_NO."</option>";
			}
			else
			{
				$text .= "
				<option value='1'>".LAN_YES."</option>
				<option value='0' selected='selected'>".LAN_NO."</option>";
			}
			$text .= "
			</select>
			<br />
			<span class='smalltext'>".EXTLAN_19."</span>
			</td>
			</tr>

			<tr>
			<td style='width:30%;vertical-align:top' class='forumheader3'>".EXTLAN_5."</td>
			<td style='width:70%' class='forumheader3' colspan='3'>
			".r_userclass("user_applicable", $current['user_extended_struct_applicable'], 'off', 'member, admin, classes')."<br /><span class='smalltext'>".EXTLAN_20."</span>
			</td>
			</tr>

			<tr>
			<td style='width:30%;vertical-align:top' class='forumheader3'>".EXTLAN_6."</td>
			<td style='width:70%' class='forumheader3' colspan='3'>
			".r_userclass("user_read", $current['user_extended_struct_read'], 'off', 'public, member, admin, classes')."<br /><span class='smalltext'>".EXTLAN_22."</span>
			</td>
			</tr>

			<tr>
			<td style='width:30%;vertical-align:top' class='forumheader3'>".EXTLAN_7."</td>
			<td style='width:70%' class='forumheader3' colspan='3'>
			".r_userclass("user_write", $current['user_extended_struct_write'], 'off', 'member, admin, classes')."<br /><span class='smalltext'>".EXTLAN_21."</span>
			</td>
			</tr>

			<tr>
			<td style='width:30%;vertical-align:top' class='forumheader3'>".EXTLAN_49."
			</td>
			<td style='width:70%' class='forumheader3' colspan='3'>
			<select class='tbox' name='user_hide'>
			";
			if($current_hide)
			{
				$text .= "
				<option value='1' selected='selected'>".LAN_YES."</option>
				<option value='0'>".LAN_NO."</option>";
			}
			else
			{
				$text .= "
				<option value='1'>".LAN_YES."</option>
				<option value='0' selected='selected'>".LAN_NO."</option>";
			}
			$text .= "
			</select>
			<br /><span class='smalltext'>".EXTLAN_50."</span>
			</td>
			</tr>
			";

			$text .= "<tr>
			<td colspan='4' style='text-align:center' class='forumheader'>";

			if ((!is_array($current) || $action == "continue") && $sub_action == "")
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

			</table></form>
			";
		}
		//		$text .= "</div>";
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
		".r_userclass("user_read", $current['user_extended_struct_read'], 'off', 'public, member, admin, classes')."<br /><span class='smalltext'>".EXTLAN_22."</span>
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

		$var['editext']['text'] = EXTLAN_45;
		$var['editext']['link'] = e_SELF."?editext";

		$var['cat']['text'] = EXTLAN_35;
		$var['cat']['link'] = e_SELF."?cat";

		$var['pre']['text'] = EXTLAN_56;
		$var['pre']['link'] = e_SELF."?pre";

		show_admin_menu(EXTLAN_9, $action, $var);
	}
}

function users_extended_adminmenu() {
	global $user, $action, $ns, $curtype, $action;
	$user->show_options($action);
	if($action == 'editext' || $action == 'continue')
	{
		$ns->tablerender(EXTLAN_46." - <span id='ue_type'>&nbsp;</span>", "<div id='ue_help'>&nbsp;</div>");
		echo "<script type='text/javascript'>changeHelp('{$curtype}');</script>";
	}
}


function make_delimited($var)
{
	global $tp;
	foreach($var as $k => $v)
	{
		$var[$k] = $tp->toDB(trim($v));
		$var[$k] = str_replace(",", "[E_COMMA]", $var[$k]);
		if($var[$k] == "")
		{
			unset($var[$k]);
		}
	}
	$ret = implode(",", $var);
	return $ret;
}

function show_predefined()
{
	global $tp, $ns, $ue, $sql;

	// Get list of current extended fields
	$curList = $ue->user_extended_get_fieldlist();
	foreach($curList as $c)
	{
		$curNames[] = $c['user_extended_struct_name'];
	}

	//Get list of predefined fields, determine which are already activated.
	$preList = $ue->parse_extended_xml('getfile');
	ksort($preList);
	foreach($preList as $k => $v)
	{
		if($k != 'version')
		{
		if(in_array($v['name'], $curNames))
		{
			$active[] = $v;
		}
		else
		{
			$inactive[] = $v;
		}
		}
	}

	$txt = "
	You can activate / deactivate these fields if you want to test, but there is no upgrade script to move your existing data over yet.  It should arrive soon, please be patient.<br />
	Once there is an upgrade script all of these will be activate and data moved over.  Any fields you don't want will have to be deactivated.<br />
	<br />
	<form method='post'>
	<table class='width:".ADMIN_WIDTH."'>
	<tr>
	<td class='fcaption' colspan='4'>".EXTLAN_57."</td>
	</tr>
	";
	if(count($active))
	{
		foreach($active as $a)
		{
			$txt .= show_field($a, 'deactivate');
		}
	}
	else
	{
		$txt .= "<tr><td colspan='4'>".EXTLAN_61."</td></tr>";
	}

	$txt .= "
	<tr>
	<td class='fcaption' colspan='4'>".EXTLAN_58."</td>
	</tr>
	";
	foreach($inactive as $a)
	{
		$txt .= show_field($a);
	}
	$txt .= "</table></form>";
	$ns->tablerender(EXTLAN_56, $txt);
	require_once('footer.php');
	exit;
}

function show_field($var, $type='activate')
{
	global $tp;
	static $head_shown;
	$txt = "";
//	$showlist = array('type','text', 'values', 'include_text', 'regex');
	if($head_shown != 1)
	{
	
		$txt .= "
		<tr>
		<td class='fcaption'>Name</td>
		<td class='fcaption'>Type</td>
		<td class='fcaption'>Use</td>
		<td class='fcaption'>&nbsp;</td>
		</tr>
		";
		$head_shown = 1;
	}
	$txt .= "
	<tr>
	<td class='forumheader2'>{$var['name']}</td>
	<td class='forumheader2'>".$tp->toHTML($var['type'], false, 'defs')."</td>
	<td class='forumheader2'>".constant(strtoupper($var['text'])."_DESC")."</td>
	";
//	$txt .= constant("UE_LAN_".strtoupper($var['text'])."DESC")."<br />";
//	foreach($showlist as $f)
//	{
//		if($var[$f] != "" && $f != 'type' && $f !='text')
//		{
//			$txt .= "<strong>{$f}: </strong>".$tp->toHTML($var[$f], false, 'defs')."<br />";
//		}
//	}
	$val = ('activate' == $type) ? EXTLAN_59 : EXTLAN_60;
	$txt .= "
	</td>
	<td class='forumheader2'><input class='button' type='submit' name='{$type}[{$var['name']}]' value='{$val}' /></td>
	</tr>";
	return $txt;
}

function field_activate()
{
	global $ue, $ns, $tp;
	$ret = "";
	$preList = $ue->parse_extended_xml('getfile');
	$tmp = $preList;
	foreach(array_keys($_POST['activate']) as $f)
	{
		$tmp[$f]['parms'] = addslashes($tmp[$f]['parms']);
		if($ue->user_extended_add($tmp[$f]))
		{
			$ret .= "Field: $f has been activated <br />";
		}
		else
		{
			$ret .= "ERROR!!  Field: $f was not activated! <br />";
		}
	}
	return $ret;
}

function field_deactivate()
{
	global $ue, $ns, $tp;
	$ret = "";
	foreach(array_keys($_POST['deactivate']) as $f)
	{
		if($ue->user_extended_remove($f, $f))
		{
			$ret .= "Field: $f has been deactivated <br />";
		}
		else
		{
			$ret .= "ERROR!!  Field: $f was not deactivated! <br />";
		}
	}
	return $ret;
}

function headerjs()
{
	include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_user_extended.php");
	$text = "
	<script type='text/javascript'>
	function changeHelp(type) {
		var ftype;
		var helptext;
		";
		for($i=0; $i<=7; $i++)
		{
			$type_const = "UE_LAN_{$i}";
			$help_const = "EXTLAN_HELP_{$i}";
			$text .= "
			if(type == \"{$i}\")
			{
				xtype=\"".constant($type_const)."\";
				what=\"".constant($help_const)."\";
			}";
		}
		$text .= "
		document.getElementById('ue_type').innerHTML=''+xtype+'';
		document.getElementById('ue_help').innerHTML=''+what+'';

		if(type == 4){
			document.getElementById('db_mode').style.display = '';
			document.getElementById('values').style.display = 'none';
		}else{
            document.getElementById('values').style.display = '';
			document.getElementById('db_mode').style.display = 'none';
		}
	}
	</script>";

	global $cal;
	$text .= $cal->load_files();

	echo $text;
}
?>