<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/upgrade_b1_to_b3.php.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+

This file will upgrade your e107 core from v5.4beta1 to v5.4beta3.

*/
require_once("class2.php");
require_once(HEADERF);


mysql_query("ALTER TABLE `".MUSER."content` CHANGE `content_parent` `content_summary` TEXT NOT NULL");
mysql_query("ALTER TABLE `".MUSER."menus` ADD `menu_class` TINYINT UNSIGNED NOT NULL");

$sql -> db_Select("e107");
$row = $sql -> db_Fetch();
$row['e107_author'] = "Steve Dunstan";
$row['e107_url'] = "http://e107.org";
$row['e107_version'] = "v5.4";
$row['e107_build'] = "beta3";
$row['e107_datestamp'] = time();

$tmp = serialize($row);
$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='e107' ");

echo "<div style=\"text-align:center\">Core updated to v5.4 beta 3.</div>";

require_once(FOOTERF);
?>