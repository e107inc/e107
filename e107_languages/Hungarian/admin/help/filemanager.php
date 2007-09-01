<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ŠSteve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/filemanager.php,v $
|     $Revision: 1.6 $
|     $Date: 2007-09-01 11:13:15 $
|     $Author: e107hun-lac $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Innét lehetőséged van a /files könyvtárakban lévő állományok kezelésére. Ha feltöltés közben jogosultsággal kapcsolatos hibaüzeneteket kapsz, akkor adj 777 -es jogosultságot a célmappára.";
$ns -> tablerender("Fájlkezelő Súgó", $text);
?>
