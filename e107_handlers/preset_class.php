<?
/*
+ ----------------------------------------------------------------------------+
|		e107 website system
|
|		©Steve Dunstan 2001-2002
|		http://e107.org
|		jalist@e107.org
|
|		Released under the terms and conditions of the
|		GNU General Public License (http://gnu.org).
|
|		$Source: /cvs_backup/e107_0.7/e107_handlers/preset_class.php,v $
|		$Revision: 1.4 $
|		$Date: 2005-02-04 08:23:00 $
|		$Author: e107coders $
+----------------------------------------------------------------------------+
*/

class e_preset {

	var $form;
	var $page;
	var $id;

	function save_preset(){
	global $sql,$tp,$ns;
	$qry = explode(".",e_QUERY);
	$unique_id = is_array($this->id) ? $this->id : array($this->id);
	$uid = $qry[1];

		if($_POST && $qry[0] =="savepreset"){
			foreach($_POST as $key => $value){
				$value = $tp->toDB($value);
			if ($sql -> db_Update("preset", "preset_value='$value'  WHERE preset_name ='".$unique_id[$uid]."' AND preset_field ='$key' ")){

			} elseif ($value !="" && !$sql -> db_Select("preset","*","preset_name ='".$unique_id[$uid]."' AND preset_field ='$key' ")){
				$sql -> db_Insert("preset", "0, '".$unique_id[$uid]."', '$key', '$value' ");
			}

			if($value == ""){
				$sql -> db_Delete("preset", "preset_field ='".$key."' ");
			}

			}
			$ns -> tablerender(LAN_SAVED, LAN_PRESET_SAVED);
		}

		if ($_POST['delete_preset'] && e_QUERY=="clr_preset"){
			$del = $_POST['del_id'];
			$text = ($sql -> db_Delete("preset", "preset_name ='".$unique_id[$del]."' ")) ? LAN_DELETED : LAN_DELETED_FAILED;
			$ns -> tablerender($text, LAN_PRESET_DELETED);
		}

	}

// ------------------------------------------------------------------------

	function read_preset($unique_id){
		global $sql,$tp;
		if (!$_POST){
			if ($sql -> db_Select("preset", "*", "preset_name ='$unique_id' ")){
				while ($row = $sql-> db_Fetch()){
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