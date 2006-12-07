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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/menus2.php,v $
|     $Revision: 1.2 $
|     $Date: 2006-12-07 21:49:18 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text .= "Itt beállíthatod, hogy hol és milyen sorrendben jelenjenek meg a menük. Használd a nyilakat a menük le- ill. fel-mozgatásához, amíg eléred a megfelelő pozíciót.<br />
A képernyő közepén elhelyezkedő linkek inaktívak, aktiválásukhoz ki kell választani és megnyomni a megfelelő menüterület gombját.
";

$ns -> tablerender("Menü - Súgó", $text);
?>
