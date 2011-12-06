<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 *  $URL$
 *	$Revision$
 *  $Id$
 */

if(!defined('e107_INIT')) { exit(); }

/**
 * Form presets handler
 *
 * NEW in 0.8 - it wont output messages anymore,
 * all pages should use eMessage:render() to catch up the
 * preset message.
 *
 * TODO - multiple user defined presets per unique_id
 *
 */
class e_preset
{

	var $form;

	var $page;

	var $id;

	/**
	 * Save preset
	 *
	 * @param string $exclude_fields Comma separated list of fields not to save
	 * @param bool $output output message or use message handler. NOTE - default value will be changed to false, update your code.
	 */
	function save_preset($exclude_fields = '', $output = true)
	{
		global $sql, $ns, $tp;
		$qry = explode(".", e_QUERY);
		$unique_id = is_array($this->id) ? $this->id : array($this->id);
		$uid = $tp->toDB(varset($qry[1], 0));

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
				else
				{
					$_POST[$key] = $tp->toDB($value);
				}
				
			}
			if ($existing)
			{		// Delete any existing entries for this preset (else checkbox settings not updated)
				$sql -> db_Delete("preset", "preset_name ='".$saveID."' ");
			}
			foreach($_POST as $key => $value)
			{
				$sql -> db_Insert("preset", "0, '".$saveID."', '$key', '$value' ");
			}
			if(!$output)
			{
				$ns->tablerender(LAN_SAVED, LAN_PRESET_SAVED);
				return;
			}

			require_once (e_HANDLER."message_handler.php");
			$emessage = &eMessage::getInstance();
			$emessage->add(LAN_PRESET_SAVED, E_MESSAGE_SUCCESS);
		}

		if($_POST['delete_preset'] && e_QUERY == "clr_preset")
		{
			$del = $tp->toDB($_POST['del_id']);
			$check = $sql->db_Delete("preset", "preset_name ='".$unique_id[$del]."' ");

			if($output)
			{
				$ns->tablerender(LAN_SAVED, $check ? LAN_PRESET_DELETED : LAN_DELETED_FAILED);
				return;
			}

			require_once (e_HANDLER."message_handler.php");
			$emessage = &eMessage::getInstance();
			if($check)
				$emessage->add(LAN_PRESET_DELETED, E_MESSAGE_SUCCESS);
			else
				$emessage->add(LAN_DELETED_FAILED, E_MESSAGE_ERROR);
		}

	}

	/**
	 * Read preset
	 *
	 * @param string $unique_id
	 * @return array values
	 */
	function read_preset($unique_id)
	{
		global $sql, $tp;

		$val = array();
		if(!$_POST)
		{
			if($sql->db_Select("preset", "*", "preset_name ='$unique_id' "))
			{
				while($row = $sql->db_Fetch())
				{
					$val[$row['preset_field']] = $tp->toForm($row['preset_value']);
					$_POST[$row['preset_field']] = $tp->toForm($row['preset_value']);
				}
			}
		}
		return $val;
	}

// ---------------------------------------------------


}

?>