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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/review.php,v $
|     $Revision: 1.2 $
|     $Date: 2006-12-07 21:49:18 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Leírások súgó";
$text = "A leírások egy egyszerű része a cikkeknek, de saját menürészben jelenik meg.<br />
 A többoldalas leírást a [newpage] kóddal lehet elválasztani, Például <br /><code>Test1 [newpage] Test2</code><br /> kóddal kétoldalas leírást hozhatsz létre, ahol a 'Test1' az első oldal és a 'Test2' a második oldal.";
$ns -> tablerender("Leírások súgó", $text);
?>
