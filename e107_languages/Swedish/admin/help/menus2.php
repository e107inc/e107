<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Swedish/admin/help/menus2.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-01-24 12:49:09 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Menyhjälp";
$text .= "Du kan arrangera var och i vilken ordning dina menyer skall visas härifrån. Använd pilarna för att flytta menyerna upp och ner tills du är nöjd med placeringarna.<br />
Menyerna i mitten på skärmen är avaktiverade, du kan aktivera dem genom att välja en plats att visa dem på.
";

$ns -> tablerender("Menyhjälp", $text);

?>
