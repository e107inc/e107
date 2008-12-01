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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/log.php,v $
|     $Revision: 1.8 $
|     $Date: 2008-12-01 16:42:39 $
|     $Author: e107hun-lac $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Az oldal sztatisztikájának rögzítését kapcsolhatod be itt. Ha korlátozott méretű adatbázisod van, akkor tanácsos csak a domain rögzítését kérni, mivel a statisztika nagymértékben megnöveli az adatbázis méretét. Pl.: 'jalist.com' a teljes cím: 'http://jalist.com/links.php' helyett.";
$ns -> tablerender("Statisztika - Súgó", $text);
?>
