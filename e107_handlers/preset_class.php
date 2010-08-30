<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

class e_preset 
{

	var $form;
	var $page;
	var $id;

	function save_preset($exclude_fields = '')    // Comma separated list of fields not to save
	{
		global $sql, $ns, $tp;
		$qry = explode(".", e_QUERY);
		$unique_id = is_array($this->id) ? $this->id : array($this->id);
		$uid = $qry[1];

		if($_POST && $qry[0] == "savepreset")
		{
			$saveID = $tp -> toDB($unique_id[$uid], true);
			$exclude_array = explode(',',$exclude_fields);
			$existing = $sql->db_Count("preset", "(*)", " WHERE preset_name='".$saveID."'  ") ? TRUE : FALSE;
			foreach($_POST as $key => $value)
			{
				if (in_array($key,$exclude_array) || ($tp->toDB($key) != $key))
				{
					unset($_POST[$key]);		// Remove any fields excluded from preset, and those with potentially dubious key names
				}
				$_POST[$key] = $tp->toDB($value);
				
			}
			if ($existing)
			{		// Delete any existing entries for this preset (else checkbox settings not updated)
				$sql -> db_Delete("preset", "preset_name ='".$saveID."' ");
			}
			foreach($_POST as $key => $value)
			{
				$sql -> db_Insert("preset", "0, '".$saveID."', '$key', '$value' ");
			}
			$ns -> tablerender(LAN_SAVED, LAN_PRESET_SAVED);
		}

		if ($_POST['delete_preset'] && e_QUERY=="clr_preset")
		{
			$del = $_POST['del_id'];
			$text = ($sql -> db_Delete("preset", "preset_name ='".$unique_id[$del]."' ")) ? LAN_DELETED : LAN_DELETED_FAILED;
			$ns -> tablerender($text, LAN_PRESET_DELETED);
		}

	}

// ------------------------------------------------------------------------

	function read_preset($unique_id)
	{
		global $sql,$tp;
		if (!$_POST)
		{
			if ($sql -> db_Select("preset", "*", "preset_name ='$unique_id' "))
			{
				while ($row = $sql-> db_Fetch())
				{
					extract($row);
					$val[$preset_field] = $tp->toForm($preset_value);
					$_POST[$preset_field] = $tp->toForm($preset_value);
				}
				return $val;
			}
		}
	}

// ---------------------------------------------------


}

?>