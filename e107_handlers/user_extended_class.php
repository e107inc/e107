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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/user_extended_class.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-03-11 03:11:52 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

/*

User_extended rewrite for version 0.7

this code uses two tables,
user_extended
user_extended_struct
to store its data and structural information.

*/

include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_user_extended.php");

class e107_user_extended
{
	var $user_extended_types;

	function e107_user_extended()
	{
		//		1 = text
		//		2 = radio
		//		3 = dropdown
		//		4 = db field
		//		5 = textarea
		//		6 = integer

		$this->user_extended_types = array(
		1 => UE_LAN_1,
		2 => UE_LAN_2,
		3 => UE_LAN_3,
		4 => UE_LAN_4,
		5 => UE_LAN_5,
		6 => UE_LAN_6,
		);
	}

	function user_extended_type_text($type, $default)
	{
		switch ($type)
		{
			case 6:
				// integer
				$db_type = 'INT(11)';
				break;

			case 1:
			case 2:
			case 3:
			case 4:
				//text, dropdown, radio, db field
				$db_type = 'VARCHAR(255)';
				break;

			case 5:
				//textarea
				$db_type = 'TEXT';
				break;
		}
		if($type != 4 && $default != ''){
			$default_text = " DEFAULT '{$default}'";
		}
		else
		{
			$default_text = '';
		}
		return $db_type.$default_text;
	}

	function user_extended_field_exist($name)
	{
		global $sql;
		return $sql->db_Count('user_extended_struct','(*)', "WHERE user_extended_struct_name = '{$name}'");
	}

	function user_extended_add($name, $text, $type, $parms, $values, $default, $required, $read, $write, $applicable)
	{
		global $sql;
		if (!($this->user_extended_field_exist($name)))
		{
			$field_info = $this->user_extended_type_text($type, $default);
			$sql->db_Select_gen("ALTER TABLE #user_extended ADD user_".$name.' '.$field_info, TRUE);
			$sql->db_Insert("user_extended_struct","0,'{$name}','{$text}','{$type}','{$parms}','{$values}','{$default}','{$read}','{$write}','{$required}','0','0', '{$applicable}'", TRUE);
			if ($this->user_extended_field_exist($name))
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	function user_extended_modify($id, $name, $text, $type, $parms, $values, $default, $required, $read, $write, $applicable)
	{
		global $sql;
		if ($this->user_extended_field_exist($name))
		{
			$field_info = $this->user_extended_type_text($type, $default);
			$sql->db_Select_gen("ALTER TABLE #user_extended MODIFY user_".$name.' '.$field_info);
			$newfield_info = "
			user_extended_struct_text = '{$text}',
			user_extended_struct_type = '{$type}',
			user_extended_struct_parms = '{$parms}',
			user_extended_struct_values = '{$values}',
			user_extended_struct_default = '{$default}',
			user_extended_struct_required = '{$required}',
			user_extended_struct_read = '{$read}',
			user_extended_struct_write = '{$write}'
			user_extended_struct_applicable = '{$applicable}'
			WHERE user_extended_struct_id = '{$id}'
			";
			$sql->db_Update("user_extended_struct", $newfield_info);
		}
	}

	function user_extended_remove($id, $name)
	{
		global $sql;
		if ($this->user_extended_field_exist($name))
		{
			$sql->db_Select_gen("ALTER TABLE #user_extended DROP user_".$name);
			$sql->db_Delete("user_extended_struct", "user_extended_struct_id = '$id' ");
		}
	}

	function user_extended_edit($struct, $curval)
	{
		
		$p = explode(",",$struct['user_extended_struct_parms']);
		$choices = explode(",",$struct['user_extended_struct_values']);
		$fname = "ue[user_".$struct['user_extended_struct_name']."]";
		$extra = "";
		switch($struct['user_extended_struct_type'])
		{
			case 1:  //textbox
			case 6:  //textbox
				if($p[0]) { $extra .= "size = '{$p[0]}' "; }
				if($p[1]) { $extra .= "maxlength = '{$p[1]}' "; }
				$ret = "<input class='tbox' name='{$fname}' value='{$curval}' {$extra} />";
				return $ret;
				break;

			case 2: //radio
				foreach($choices as $choice)
				{
					$choice = trim($choice);
					if($curval == $choice)
					{
						$chk = " checked='checked' ";
					}
					else
					{
						$chk="";
					}
					$ret .= "<input class='tbox' type='radio' name='{$fname}' value='{$choice}' {$chk} /> {$choice}";
				}
				return $ret;
				break;
				
			case 3: //dropdown
				$ret = "<select class='tbox' name='{$fname}'>\n";
				foreach($choices as $choice)
				{
					$choice = trim($choice);
					if($curval == $choice)
					{
						$sel = " selected='selected' ";
					}
					else
					{
						$sel="";
					}
					$ret .= "<option value='{$choice}'>{$choice}</option>\n";
				}
				$ret .= "</select>\n";
				return $ret;
				break;

			case 5: 
				$extra = "";
				$extra .= " rows='".($p[0] ? $p[0] : 5)."' ";
				$extra .= " cols='".($p[1] ? $p[1] : 60)."' ";
				return "<textarea class='tbox' name='{$fname}' {$extra}>{$curval}</textarea>";
				break;
				
		}
		return $ret;

/*
		global $pref, $key, $sql, $user_pref, $signup_ext, $_POST;
		$ut = explode("|", $form_ext_name);
		$u_name = ($ut[0] != "") ? str_replace("_", " ", $ut[0]) :
		trim($form_ext_name);
		$u_type = trim($ut[1]);
		$u_value = $ut[2];
		$v_default = $ut[3];
		$u_visible = $ut[4];
		if ($ut[4] && check_class($ut[4]) == FALSE) {
			return;
		}
		$ufield_name = "ue_{$u_fieldnum}";

		$ret = "<tr><td class='".$tdclass."' style='vertical-align:top'>".$u_name.req($pref[$signup_ext])."</td>\n";
		$ret .= "<td class='".$tdclass."' style='text-align:".$alignit."'><div style='text-align:left;width:10%;white-space:nowrap'>";
		$tmp = explode(",", $u_value);

		switch ($u_type) {
			case "radio":

			for ($i = 0; $i < count($tmp); $i++) {
				$checked = ($tmp[$i] == $user_pref[$ufield_name] || ($tmp[$i] == $v_default && !$user_pref[$ufield_name])) ? " checked='checked'" :
				"";
				if (!USER || $_POST[$ufield_name]) {
					$checked = ($_POST[$ufield_name] == $tmp[$i] || ($tmp[$i] == $v_default && !$_POST[$ufield_name]))? " checked='checked'" :
					"";
				}
				$ret .= "<input  type='radio' name='".$ufield_name."'  value='".$tmp[$i]."' $checked /> $tmp[$i] ";
				$ret .= ($pref['signup_ext_req'.$key] && $i == 0 && (!USER))? "<span style='font-size:15px; color:red'> *</span>":
				"";
				$ret .= "<br />";
			};
			$ret .= "</div>";
			$ret .= "</td></tr>\n\n";
			break;

			case "dropdown":
			$ret .= "\n<select class='tbox' style='width:200px'  name='".$ufield_name."'><option></option>\n";
			for ($i = 0; $i < count($tmp); $i++) {
				$selected = ($user_pref[$ufield_name] == "$tmp[$i]" || $_POST[$ufield_name] == $tmp[$i])? " selected='selected'" :
				"";
				$ret .= "<option value=\"".$tmp[$i]."\" ".$selected." >". $tmp[$i] ."</option>\n";
			};
			$ret .= "</select>";
			$ret .= ($pref['signup_ext_req'.$key] && (!USER))? "<span style='font-size:15px; color:red'> *</span>":
			"";
			$ret .= "</div></td></tr>\n\n";

			break;

			case "text":
			if ($u_value == "") {
				$u_value = "40";
			};
			$valuehere = ($_POST[$ufield_name])? $_POST[$ufield_name] :
			($user_pref[$ufield_name])? $user_pref[$ufield_name] :
			$v_default;
			if (!USER || $_POST[$ufield_name]) {
				$valuehere = $_POST[$ufield_name];
			}
			$ret .= "<input class='tbox' type='text' name='".$ufield_name."' size='".$u_value."' value='".$valuehere."' maxlength='200' />";
			$ret .= ($pref['signup_ext_req'.$key] && (!USER))? "<span style='font-size:15px; color:red'> *</span>":
			"";
			$ret .= "</div></td></tr>\n\n";
			break;

			case "table":
			$ret .= "
			<select class='tbox' style='width:200px'  name='".$ufield_name."'><option></option>";

			$tmp = explode(",", $u_value);
			$fieldid = $row[$tmp[1]];
			$fieldvalue = $row[$tmp[2]];
			$sql->db_Select($tmp[0], "*", "$tmp[1] !='' ORDER BY $tmp[2]");
			while ($row = $sql->db_Fetch()) {
				$fieldid = $row[$tmp[1]];
				$fieldvalue = $row[$tmp[2]];
				$checked = ($fieldid == $user_pref[$ufield_name] || ($fieldid == $v_default && !$user_pref[$ufield_name]))? " selected='selected'" :
				"";
				if (!USER || $_POST[$ufield_name]) {
					$checked = ($_POST[$ufield_name] == $fieldid)? " selected='selected'" :
					($fieldid == $v_default)? " selected='selected'" :
					"";
				}
				$ret .= "<option value='".$fieldid."' $checked > $fieldvalue </option>";
			}
			$ret .= "</select>";
			$ret .= ($pref['signup_ext_req'.$key] && e_PAGE == "customsignup.php")? "<span style='font-size:15px; color:red'> *</span>":
			"";
			$ret .= "</div></td></tr>";
			break;

			default:
			//    $ret = "<tr>
			//    <td style='width:20%' class='".$tdclass."'>".$form_ext_name."</td>
			//    <td style='width:80%; text-align:".$alignit."' class='".$tdclass."' nowrap>";
			$valuehere = ($_POST[$ufield_name])? $_POST[$ufield_name] :
			($user_pref[$ufield_name])? $user_pref[$ufield_name] :
			$v_defualt;
			if (!USER || $_POST[$ufield_name]) {
				$valuehere = $_POST[$ufield_name];
			}
			$ret .= "<input class='tbox' type='text' name='".$ufield_name."' size='40' value='".$valuehere."' maxlength='200' />";
			$ret .= ($pref['signup_ext_req'.$key])? "<span style='font-size:15px; color:red'> *</span>":
			"";
			$ret .= "</div></td></tr>";
			break;
		}
		return $ret;
*/
	}


}

?>