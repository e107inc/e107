<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
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

function avatar($avatar) {
	global $tp;
	if (stristr($avatar, "-upload-") !== FALSE) {
		return e_FILE."public/avatars/".str_replace("-upload-", "", $avatar);
	} else if (stristr($avatar, "Binary") !== FALSE) {
		$sqla = new db;
		preg_match("/Binary\s(.*?)\//", $avatar, $result);
		$sqla->db_Select("rbinary", "*", "binary_id='".$tp -> toDB($result[1])."' ");
		$row = $sqla->db_Fetch();
		 extract($row);
		return $binary_data;
	} else if (strpos($avatar, "http://") === FALSE) {
		return e_IMAGE."avatars/".$avatar;
	} else {
		return $avatar;
	}
}
	
?>