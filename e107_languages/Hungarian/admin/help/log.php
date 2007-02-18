<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Hungarian Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/log.php,v $
|     $Revision: 1.5 $
|     $Date: 2007-02-18 01:59:50 $
|     $Author: lisa_
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Az oldal sztatisztikájának rögzítését kapcsolhatod be itt. Ha korlátozott méretű adatbázisod van, akkor tanácsos csak a domain rögzítését kérni, mivel a statisztika nagymértékben megnöveli az adatbázis méretét. Pl.: 'jalist.com' a teljes cím: 'http://jalist.com/links.php' helyett.";
$ns -> tablerender("Statisztika - Súgó", $text);
?>
