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
|     $Revision: 1.7 $
|     $Date: 2005-03-20 04:06:26 $
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
			case 4:
			case 6:
				// integer, db_field
				$db_type = 'INT(11)';
				break;

			case 1:
			case 2:
			case 3:
				//text, dropdown, radio
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

	function user_extended_add($name, $text, $type, $parms, $values, $default, $required, $read, $write, $applicable, $order, $icon)
	{
		global $sql;
		if(is_array($name))
		{
			extract($name);
		}
		if (!($this->user_extended_field_exist($name)))
		{
			$field_info = $this->user_extended_type_text($type, $default);
			$sql->db_Select_gen("ALTER TABLE #user_extended ADD user_".$name.' '.$field_info);
			$sql->db_Insert("user_extended_struct","0,'{$name}','{$text}','{$type}','{$parms}','{$values}','{$default}','{$read}','{$write}','{$required}','0','{$applicable}', '{$order}', '{$icon}'");
			if ($this->user_extended_field_exist($name))
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	function user_extended_modify($id, $name, $text, $type, $parms, $values, $default, $required, $read, $write, $applicable, $order, $icon)
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
			user_extended_struct_order = '{$order}',
			user_extended_struct_icon = '{$icon}'
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
			case 6:  //integer
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
					foreach($choiceList as $cArray)
					{
						$cID = $cArray[$choices[1]];
						$cText = trim($cArray[$choices[2]]);
						if($curval == $cID)
						{
							$sel = " selected='selected' ";
						}
						else
						{
							$sel="";
						}
						$ret .= "<option value='{$cID}' {$sel}>{$cText}</option>\n";
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
				$extra = "";
				$extra .= " rows='".($p[0] ? $p[0] : 5)."' ";
				$extra .= " cols='".($p[1] ? $p[1] : 60)."' ";
				return "<textarea class='tbox' name='{$fname}' {$extra}>{$curval}</textarea>";
				break;
				
		}
		return $ret;
	}
	
	function user_extended_getStruct()
	{
		if($ueStruct = getcachedvars('ue_struct'))
		{
			return $ueStruct;
		}
		global $sql;
		$ret = array();
		if($sql->db_Select('user_extended_struct'))
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