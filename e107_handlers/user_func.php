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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/user_func.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

function e107_userGetuserclass($user_id)
{
	global $cachevar;
	$key = 'userclass_'.$user_id;
	$val = getcachedvars($key);
	if ($val)
	{
		return $cachevar[$key];
	}
	else
	{
		$uc_sql = new db;
		if ($uc_sql->db_Select("user", "user_class, user_admin", "user_id=".intval($user_id)))
		{
			$row = $uc_sql->db_Fetch();
			$uc = $row['user_class'];
			$uc .= ",".e_UC_MEMBER;
			if($row['user_admin'])
			{
				$uc .= ",".e_UC_ADMIN;
			}
			return $uc;
		}
		else
		{
			return "";
		}
	}
}
?>