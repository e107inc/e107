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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/user_extended.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-02-16 21:42:18 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
	
/*
	
User_extended rewrite for version 0.7
	
this code uses two tables,
user_extended
user_extended_struct
to store its data and structural information.
	
*/
	
	
function user_extended_field_exist($name) {
	 
	$found = FALSE;
	$sql->db_Query("SHOW COLUMNS FROM user_extended");
	while ($row = $sql->db_Fetch() && !$found) {
		if ($row['Field'] == $name) {
			$found = TRUE;
		}
	}
	return $found;
}
	
function user_extended_add($name, $type, $access, $default = '', $values = '') {
	 
	if (!(user_extended_field_exist($name))) {
		$db_type = '';
		switch ($type) {
			case 'radio':
			$db_type = 'INT(11)';
			break;
			case 'dropdown':
			$db_type = 'VARCHAR(255)';
			break;
			default:
			$db_type = $type;
			break;
		}
		$sql->db_Query("ALTER TABLE user_extended ADD ".$name.' '.$db_type.($default != ''?' DEFAULT '.$default:''));
		$access = explode(',', $access);
		$sql->db_Insert("user_extended_struct (0,'".$name."','".$type."','".$values."',".$access[0].",".$access[1].",".$access[2].",".$access[3].",".$access[4].");
	}
}
			 
function user_extended_remove($name) {
	if (!(user_extended_field_exist($name)) {
		$sql->db_Query("ALTER TABLE user_extended DROP ".$name);
		$sql->db_Delete("user_extended_struct", "user_extended_struct_name = '$name' ");
	}
}
			 
function user_extended_modify{$name,$type,$access,$default='',$values='') {
	if (user_extended_field_exist($name)) {
		$db_type='';
		switch ($type) {
			case 'radio':
				$db_type='INT(11)';
			break;
			case 'dropdown':
				$db_type='VARCHAR(255)';
			break;
			default:
				$db_type=$type;
			break;
		}
		$sql->db_Query("ALTER TABLE user_extended MODIFY ".$name.' '.$db_type.($default!=''?' DEFAULT '.$default:''));
		$access=explode(',',$access);
		$sql->db_Update("user_extended_struct", "user_extended_struct_type = '".$type."', user_extended_struct_values = '".$values."', user_extended_struct_read = ".$access[0].", user_extended_struct_write = ".$access[1].", user_extended_struct_required = ".$access[2].", user_extended_struct_signup_show = ".$access[3].", user_extended_struct_signup_required = ".$access[4]." WHERE user_extended_struct_name = '".$name."');
	}
}
	
	
	
	
// $form_ext_name = extended-user-field string
// Usage of $form_ext_name - "Name|type|values|default|applicable to|visible to"
//
//  default is the default value
//        applicable to refers to who will see it in the userpref screen
//  visible to refers to who will see it in the user screen (ie able to hide certain fields)
//  applicable to and visible to are the class id (a number)
//
// eg.      myfield|radio|$value1,$value2,$value3|$value3|0|255
// eg.      myfield|dropdown|$value1,$value2,$value3
// eg.      myfield|text|$size
// eg.      myfield|table|$db_table,$dbfield_value,$dbfield_displayname.
//          eg. User|table|db_user,user_id,user_name
// $tdclass = table style class  eg. forumheader3.
// $alignit = alignment of form element. left,right,center.
	
	
function user_extended_name($extended) {
	// strips out the other information to just reveal the extended user-field name.
	$ut = explode("|", $extended);
	$ret = ($ut[0] != "") ? str_replace("_", " ", $ut[0]) :
	 trim($extended);
	return $ret;
}
	
	
function user_extended_edit($u_fieldnum, $form_ext_name, $tdclass = "", $alignit = "left") {
	 
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
}
	
?>