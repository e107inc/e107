<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Hungarian Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/search.php,v $
|     $Revision: 1.5 $
|     $Date: 2007-02-18 01:59:51 $
|     $Author: lisa_
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Ha a MySQL szerver verziója támogatja, akkor beállíthatod
a MySQL rendezési módszerét, amely gyorsabb, mint a PHP rendezési módszere. Nézd meg a beállításokat.<br /><br />
Ha az oldalad képírásos nyelvet használ, úgy mint Kínai vagy Japán, akkor mindenképpen
PHP rendezési módszert kell használni és ki kell kapcsolnod az egész szavak keresését.";
$ns -> tablerender("Keresés - Súgó", $text);
?>
