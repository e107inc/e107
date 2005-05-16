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
|     $Revision: 1.20 $
|     $Date: 2005-05-16 08:32:03 $
|     $Author: e107coders $
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
		//		7 = date
		//		8 = language

		$this->user_extended_types = array(
		1 => UE_LAN_1,
		2 => UE_LAN_2,
		3 => UE_LAN_3,
		4 => UE_LAN_4,
		5 => UE_LAN_5,
		6 => UE_LAN_6,
		7 => UE_LAN_7,
		8 => UE_LAN_8
		);
	}

	function user_extended_get_categories($byID = TRUE)
	{
		global $sql;
		if($sql->db_Select("user_extended_struct", "*", "user_extended_struct_type = 0 ORDER BY user_extended_struct_order ASC"))
		{

			if($byID == TRUE)
			{
				while($row = $sql->db_Fetch())
				{
					$ret[$row['user_extended_struct_id']][] = $row;
				}
			}
			else
			{
				$ret = $sql->db_getList();
			}
		}
		return $ret;
	}

	function user_extended_get_fields($cat = "")
	{
		global $sql;
		$more = ($cat) ? " AND user_extended_struct_parent = $cat " : "";
		if($sql->db_Select("user_extended_struct", "*", "user_extended_struct_type > 0 {$more} ORDER BY user_extended_struct_order ASC"))
		{
			while($row = $sql->db_Fetch())
			{
				$ret[$row['user_extended_struct_parent']][] = $row;
			}
		}
		return $ret;
	}

	function user_extended_get_fieldList()
	{
		global $sql;
		$more = ($cat) ? " AND user_extended_struct_parent = $cat " : "";
		if($sql->db_Select("user_extended_struct", "*", "user_extended_struct_type > 0 {$more} ORDER BY user_extended_struct_order ASC"))
		{
			while($row = $sql->db_Fetch())
			{
				$ret[$row['user_extended_struct_id']] = $row;
			}
		}
		return $ret;
	}

	function user_extended_type_text($type, $default)
	{
		switch ($type)
		{
			case 4:
			case 6:
				// integer, db_field
				$db_type = 'INT(11)';
				break;

			case 1:
			case 2:
			case 3:
			case 7:
			case 8:
				//text, dropdown, radio, date
				$db_type = 'VARCHAR(255)';
				break;

			case 5:
				//textarea
				$db_type = 'TEXT';
				break;
		}
		if($type != 4 && $default != '')
		{
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

	function user_extended_add($name, $text, $type, $parms, $values, $default, $required, $read, $write, $applicable, $order='', $parent)
	{
		global $sql;
		if(is_array($name))
		{
			extract($name);
		}
		if (!($this->user_extended_field_exist($name)))
		{
			$field_info = $this->user_extended_type_text($type, $default);
			if($order === '')
			{
				if($sql->db_Select("user_extended_struct","MAX(user_extended_struct_order) as maxorder","1"))
				{
					$row = $sql->db_Fetch();
					if(is_numeric($row['maxorder']))
					{
						$order = $row['maxorder']+1;
					}
				}
			}
			$sql->db_Select_gen("ALTER TABLE #user_extended ADD user_".$name.' '.$field_info);
			$sql->db_Insert("user_extended_struct","0,'{$name}','{$text}','{$type}','{$parms}','{$values}','{$default}','{$read}','{$write}','{$required}','0','{$applicable}', '{$order}', '{$parent}'");
			if ($this->user_extended_field_exist($name))
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	function user_extended_modify($id, $name, $text, $type, $parms, $values, $default, $required, $read, $write, $applicable, $parent)
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
			user_extended_struct_write = '{$write}',
			user_extended_struct_applicable = '{$applicable}',
			user_extended_struct_parent = '{$parent}'
			WHERE user_extended_struct_id = '{$id}'
			";
			return $sql->db_Update("user_extended_struct", $newfield_info);
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

	function user_extended_hide($struct, $curval)
	{
		$chk = ($curval) ? " checked='checked' " : "";
		$name = "hide[user_".$struct['user_extended_struct_name']."]";
		return "<input type='checkbox' {$chk} value='1' name='{$name}' />&nbsp;".UE_LAN_HIDE;
	}

	function user_extended_edit($struct, $curval)
	{
		global $cal, $tp;
		$choices = explode(",",$struct['user_extended_struct_values']);
		foreach($choices as $k => $v)
		{
			$choices[$k] = str_replace("[E_COMMA]", ",", $choices[$k]);
		}
		$parms = explode("^,^",$struct['user_extended_struct_parms']);
		$include = preg_replace("/\n/", " ", $tp->toText($parms[0]));
		$regex = $tp->toText($parms[1]);
		$regexfail = $tp->toText($parms[2]);
		$fname = "ue[user_".$struct['user_extended_struct_name']."]";
		if(strpos($include, 'class') === FALSE)
		{
			$include .= " class='tbox' ";
		}

		switch($struct['user_extended_struct_type'])
		{
			case 1:  //textbox
			case 6:  //integer
				$ret = "<input name='{$fname}' value='{$curval}' {$include} />";
				return $ret;
				break;

			case 2: //radio
				foreach($choices as $choice)
				{
					$choice = trim($choice);
					$chk = ($curval == $choice)? " checked='checked' " : "";
					$ret .= "<input {$include} type='radio' name='{$fname}' value='{$choice}' {$chk} /> {$choice}";
				}
				return $ret;
				break;

			case 3: //dropdown
				$ret = "<select {$include} name='{$fname}'>\n";
				$ret .= "<option value=''></option>\n";  // ensures that the user chose it.
				foreach($choices as $choice){
					$choice = trim($choice);
					$sel = ($curval == $choice) ? " selected='selected' " : "";
					$ret .= "<option value='{$choice}' {$sel}>{$choice}</option>\n";
				}
				$ret .= "</select>\n";
				return $ret;
				break;

			case 4: //db_field
				global $sql;
				if($sql->db_Select($choices[0],"{$choices[1]},{$choices[2]}","1 ORDER BY {$choices[2]}"))
				{
					$choiceList = $sql->db_getList();
					$ret = "<select class='tbox' name='{$fname}'>\n";
					$ret .= "<option value=''></option>\n";  // ensures that the user chose it.
					foreach($choiceList as $cArray){
						$cID = $cArray[$choices[1]];
						$cText = trim($cArray[$choices[2]]);
						$sel = ($curval == $cID) ? " selected='selected' " : "";
						$ret .= "<option {$include} value='{$cID}' {$sel}>{$cText}</option>\n";
					}
					$ret .= "</select>\n";
					return $ret;
				}
				else
				{
					return "";
				}
				break;

			case 5: //textarea
				return "<textarea {$include} name='{$fname}' >{$curval}</textarea>";
				break;

			case 7: //date
				return $cal->make_input_field(array(), array('class' => 'tbox', 'name' => $fname, 'value' => $curval));
				break;

            case 8: // language
				require_once(e_HANDLER."file_class.php");
				$fl = new e_file;
				$lanlist = $fl->get_dirs(e_LANGUAGEDIR);
				sort($lanlist);
            	$ret = "<select {$include} name='{$fname}'>\n";
				$ret .= "<option value=''></option>\n";  // ensures that the user chose it.
					foreach($lanlist as $choice){
						$choice = trim($choice);
						$sel = ($curval == $choice || (!USER && $choice == e_LANGUAGE))? " selected='selected' " : "";
						$ret .= "<option value='{$choice}' {$sel}>{$choice}</option>\n";
					}
				$ret .= "</select>\n";
           		break;

		}

		return $ret;
	}

	function user_extended_getStruct($orderby="user_extended_struct_order")
	{
		if($ueStruct = getcachedvars('ue_struct'))
		{
			return $ueStruct;
		}
		global $sql;
		$ret = array();
		$parms = "";
		if($orderby != "")
		{
			$parms = "1 ORDER BY {$orderby}";
		}
		if($sql->db_Select('user_extended_struct','*',$parms))
		{
			while($row = $sql->db_Fetch())
			{
				$ret['user_'.$row['user_extended_struct_name']] = $row;
			}
		}
		cachevars('ue_struct',$ret);
		return $ret;
	}
}

?>