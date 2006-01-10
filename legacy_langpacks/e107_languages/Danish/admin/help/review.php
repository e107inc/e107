<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Danish/admin/help/review.php,v $
|        $Revision: 1.2 $
|        $Date: 2006-01-10 16:31:21 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/








if (!defined('e107_INIT')) { exit; }

$text = "Anmeldelser er magen til Artikler men de vil blive vist i deres egen menu.<br />
 For en a multi-side artikel indel hver side med teksten [newpage], eks. <br /><code>Test1 [newpage] Test2</code><br /> vil lave en to sidet artikel med 'Test1' p&aring; side 1 og 'Test2' p&aring; side 2.";
$ns -> tablerender("Anmeldelse Hj&aelig;lp", $text);
?>