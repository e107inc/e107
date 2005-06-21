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
|     $Revision: 1.26 $
|     $Date: 2005-06-21 14:32:31 $
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
	var $extended_xml;
	var $typeArray;
	
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

		$this->typeArray = array(
			'text' => 1, 
			'radio' => 2, 
			'dropdown' => 3, 
			'db field' => 4, 
			'textarea' => 5, 
			'integer' => 6, 
			'date' => 7, 
			'language' => 7
		);
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

			case 4:   // db table - supporting numeric and non-numeric ids.(eg country codes)
				$db_type = 'VARCHAR(11)';
				break;

			case 6:
				// integer,
				$db_type = 'INT(11)';
				break;

			case 1:
			case 2:
			case 3:
			case 4:
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
		if(!is_numeric($type))
		{
			$type = $this->typeArray[$type];
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
			if(is_numeric($id))
			{
				$sql->db_Delete("user_extended_struct", "user_extended_struct_id = '$id' ");
			}
			else
			{
				$sql->db_Delete("user_extended_struct", "user_extended_struct_name = '$id' ");
			}
			return !($this->user_extended_field_exist($name));
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
		foreach($choices as $k => $v){
			$choices[$k] = str_replace("[E_COMMA]", ",", $choices[$k]);
		}
		$parms = explode("^,^",$struct['user_extended_struct_parms']);
		$include = preg_replace("/\n/", " ", $tp->toText($parms[0]));
		$regex = $tp->toText($parms[1]);
		$regexfail = $tp->toText($parms[2]);
		$fname = "ue[user_".$struct['user_extended_struct_name']."]";
		if(strpos($include, 'class') === FALSE)	{
			$include .= " class='tbox' ";
		}



		switch($struct['user_extended_struct_type']){
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
				$order = ($choices[3]) ? "ORDER BY {$choices[3]}" : "";

				if($sql->db_Select($choices[0],"{$choices[1]},{$choices[2]}","1 $order")){
					$choiceList = $sql->db_getList();
					$ret = "<select {$include} class='tbox' name='{$fname}'  >\n";
					$ret .= "<option value=''></option>\n";  // ensures that the user chose it.
					foreach($choiceList as $cArray){
						$cID = trim($cArray[$choices[1]]);
						$cText = trim($cArray[$choices[2]]);
						$sel = ($curval == $cID) ? " selected='selected' " : "";
						$ret .= "<option value='{$cID}' {$sel}>{$cText}</option>\n";
					}
					$ret .= "</select>\n";
					return $ret;
				} else {
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
	
	function parse_extended_xml($contents, $no_cache = FALSE)
	{
		if($no_cache == FALSE && $this->extended_xml)
		{
			return $this->extended_xml;
		}

		require_once(e_HANDLER."xml_class.php");
		$xml = new CXml;
		if("getfile" == $contents)
		{
			$contents = file_get_contents(e_FILE."cache/user_extended.xml");
		}
		$xml->Set_XML_data($contents);
		$data = $xml->obj_data->e107_extended_user_fields[0];
		$ret['version'] = $data->version;
		unset($info);
		foreach($data->item as $item)
		{
			$info = array(
								"name" 			=> $item->name,
								"text" 			=> "UE_LAN_".strtoupper($item->name),
								"type" 			=> $item->type[0],
								"values" 		=> $item->values[0],
								"default" 		=> $item->default[0],
								"required" 		=> $item->required[0],
								"read" 			=> $item->read[0],
								"write" 			=> $item->write[0],
								"applicable" 	=> $item->applicable[0],
								"include_text"	=> $item->include_text[0],
								"parms"			=> $item->include_text[0],
								"regex" 			=> $item->regex[0]
							 );
			if($item->regex[0])
			{
				$info['parms'] .= $item->include_text[0]."^,^".$item->regex[0]."^,^LAN_UE_FAIL_".strtoupper($item->name);
			}
			$ret[$item->name] = $info;
		}
		$this->extended_xml = $ret;
		return $this->extended_xml;
	}
	function convert_old_fields()
	{
		global $sql;
		$sql2 = new db;
		$preList = $this->parse_extended_xml('getfile');
		$flist = array('user_aim', 'user_birthday', 'user_homepage', 'user_icq', 'user_msn', 'user_location');
		foreach($flist as $f)
		{
			$f = substr($f, 5);
			$preList[$f]['parms'] = addslashes($preList[$f]['parms']);
			$this->user_extended_add($preList[$f]);
		}
		if($sql->db_Select('user', "user_id, ".implode(", ", $flist)))
		{
			while($row = $sql->db_Fetch())
			{
				set_time_limit(30);
				$sql2->db_Select_gen("INSERT INTO #user_extended (user_extended_id) values ('{$row['user_id']}')");
				$newvals = "";
				foreach($flist as $f)
				{
					$newvals .= "{$f} = '".$row[$f]."',";
				}
				$newvals = substr($newvals, 0 ,-1);
				$qry = "
				UPDATE #user_extended SET
				{$newvals}
				WHERE 
				user_extended_id = '{$row['user_id']}'
				";
				$sql2->db_Select_gen($qry);
			}
		}
		foreach($flist as $f)
		{
			$sql->db_Select_gen("ALTER TABLE #user DROP {$f}");
		}
	}
}
?>