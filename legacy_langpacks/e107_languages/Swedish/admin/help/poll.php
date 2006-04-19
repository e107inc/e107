<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Swedish/admin/help/poll.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-04-19 09:33:06 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Du skapar r&ouml;stningar/unders&ouml;kningar fr&aring;n denna sida, bara skriv in rubriken och de olika alternativen, f&ouml;rhandsgranska det och om det ser bra ut, markera rutan f&ouml;r att aktivera den.<br /><br />
F&ouml;r att visa r&ouml;stningen, g&aring; till din menysida och se till att poll_menu &auml;r aktiverad.";

$ns -> tablerender("R&ouml;stningar", $text);

?>
