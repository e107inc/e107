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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/forum/forum_admin_class.php,v $
|     $Revision: 1.1.2.1 $
|     $Date: 2008-03-11 02:39:37 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

class e107forum_admin {

	
	/**
 	* Create a forum
 	* @param array $info e107_forum field values
 	* @return boolean 
 	*/
 	function forum_create($info)
	{
		global $sql;
		return $sql->db_Insert("forum", $info);
	}

	
}
