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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/search/search_user.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-27 19:52:31 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
	
if ($results = $sql->db_Select("user", "user_id, user_name, user_email, user_homepage, user_location, user_signature", "user_name REGEXP('".$query."') OR user_email REGEXP('".$query."') OR user_homepage  REGEXP('".$query."') OR user_location REGEXP('".$query."') OR user_signature REGEXP('".$query."') ")) {
	while (list($user_id, $user_name, $user_email, $user_homepage, $user_location, $user_signature) = $sql->db_Fetch()) {
		$user_name = parsesearch($user_name, $query);
		$user_email = parsesearch($user_email, $query);
		$user_homepage = parsesearch($user_homepage , $query);
		$user_signature = parsesearch($user_signature, $query);
		$user_location = parsesearch($user_location, $query);
		$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"user.php?id.".$user_id."\">".$user_name."</a><br />";
	}
} else {
	$text .= LAN_198;
}
?>