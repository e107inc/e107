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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/avatar_handler.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-27 19:52:26 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
function avatar($avatar) {
	if (eregi("-upload-", $avatar)) {
		return e_FILE."public/avatars/".str_replace("-upload-", "", $avatar);
	}
	else if(eregi("Binary", $avatar)) {
		$sqla = new db;
		preg_match("/Binary\s(.*?)\//", $avatar, $result);
		$sqla->db_Select("rbinary", "*", "binary_id='".$result[1]."' ");
		$row = $sqla->db_Fetch();
		 extract($row);
		return $binary_data;
	}
	else if(!eregi("http://", $avatar)) {
		return e_IMAGE."avatars/".$avatar;
	} else {
		return $avatar;
	}
}
	
?>