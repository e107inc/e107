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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/search.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-12-13 20:44:36 $
|     $Author: e107hun-lac $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Ha a MySQL szerver verziója támogatja, akkor beállíthatod 
a MySQL rendezési módszerét, amely gyorsabb, mint a PHP rendezési módszere. Nézd meg a beállításokat.<br /><br />
Ha az oldalad képírásos nyelvet használ, úgy mint Kínai vagy Japán, akkor mindenképpen 
PHP rendezési módszert kell használni és ki kell kapcsolnod az egész szavak keresését.";
$ns -> tablerender("Keresés - Súgó", $text);
?>
