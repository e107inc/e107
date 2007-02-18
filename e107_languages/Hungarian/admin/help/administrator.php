<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Hungarian Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/administrator.php,v $
|     $Revision: 1.4 $
|     $Date: 2007-02-18 01:59:51 $
|     $Author: lisa_
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Admin súgó";
$text = "Itt hozhatsz létre új adminisztrátorokat, törölheted a meglévőket. Az adminisztrátorok csak azokkal a jogokkal fognak rendelkezni, amiket itt beállítasz.<br /><br />
Új admin létrehozásához lépj a Felhasználók oldalra és írd felül a meglévő Tag admin státuszát.";
$ns -> tablerender($caption, $text);
?>