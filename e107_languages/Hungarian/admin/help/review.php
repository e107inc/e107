<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Hungarian Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/review.php,v $
|     $Revision: 1.5 $
|     $Date: 2007-02-18 01:59:51 $
|     $Author: lisa_
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Leírások súgó";
$text = "A leírások egy egyszerű része a cikkeknek, de saját menürészben jelenik meg.<br />
 A többoldalas leírást a [newpage] kóddal lehet elválasztani, Például <br /><code>Test1 [newpage] Test2</code><br /> kóddal kétoldalas leírást hozhatsz létre, ahol a 'Test1' az első oldal és a 'Test2' a második oldal.";
$ns -> tablerender("Leírások súgó", $text);
?>
