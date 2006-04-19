<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Swedish/admin/help/review.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-04-19 09:33:06 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Recensioner &auml;r liknande artiklar, men de listas i egna menyer.<br />
F&ouml;r en flersidig recension, separera sidorna med texten [newpage], allts&aring; <br />&lt;code&gt;Test1 [newpage] Test2&lt;/code&gt;<br /> skapar en tv&aring;sidig recension med 'Test1' p&aring; sidan 1 och 'Test2' p&aring; sidan 2.";
$ns -> tablerender("Recensionshj&auml;lp", $text);

?>
