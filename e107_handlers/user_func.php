<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/e107_handlers/user_func.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

function e107_userGetuserclass($user_id){
	global $cachevar;
	$key = 'userclass_'.$user_id;
	$val = getcachedvars($key);
	if($val)
	{
		return $cachevar[$key];
	}
	else
	{
		$uc_sql = new db;
		if($uc_sql -> db_Select("user","user_class","user_id={$user_id}"))
		{
			$row = $uc_sql -> db_Fetch();
			return $row['user_class'];
		}
		else 
		{
			return "";
		}
	}
}
?>