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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/search/search_links.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-27 19:52:31 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
	
if ($results = $sql->db_Select("links", "*", "link_name REGEXP('".$query."') OR link_description REGEXP('".$query."') ")) {
	while (list($link_id, $link_name, $link_url, $link_desciption, $link_button, $link_category, $link_refer) = $sql->db_Fetch()) {
		$link_name_ = parsesearch($link_name, $query);
		if (!$link_name_) {
			$link_name_ = $link_name;
		}
		$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"".$link_url."\">".$link_name."</a><br />";
	}
} else {
	$text .= LAN_198;
}
?>