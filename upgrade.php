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
|     $Source: /cvs_backup/e107_0.7/upgrade.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-27 19:51:38 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);
	
	
$pref['cookie_name'] = "e107cookie";
save_prefs();
	
$sql->db_Select("core", "*", "e107_name='e107' ");
$row = $sql->db_Fetch();
$tmp = stripslashes($row['e107_value']);
$e107 = unserialize($tmp);
if (!is_array($e107)) {
	$e107 = unserialize($row['e107_value']);
}
	
$e107['e107_version'] = "0.601";
$e107['e107_build'] = "0";
$e107['e107_datestamp'] = time();
	
$tmp = addslashes(serialize($e107));
$sql->db_Update("core", "e107_value='$tmp' WHERE e107_name='e107' ");
	
$text = "<div style='text-align:center'>Upgrade complete, now running v0.601.</div>";
$ns->tablerender("Upgrade", $text);
	
	
require_once(FOOTERF);
?>