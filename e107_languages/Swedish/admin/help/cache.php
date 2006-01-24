<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Swedish/admin/help/cache.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-01-24 12:49:09 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Cachning";
$text = "Om du har cachning påkopplad kommer hasigheten på din sajt att öka markant och antalet förfrågningar till SQL-databasen minskas.<br /><br /><b>VIKTIGT! Om du håller på att göra ett eget tema, koppla från cachning under tiden, annars kommer dina ändringar inte att ha någon effekt.</b>";
$ns -> tablerender($caption, $text);

?>
