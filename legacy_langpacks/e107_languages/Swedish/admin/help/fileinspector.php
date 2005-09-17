<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Swedish/admin/help/fileinspector.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-09-17 14:44:06 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/
$text = "<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_core.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;K&auml;rnfil</div>
<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_check.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;K&auml;rnfil (Integritet OK)</div>
<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_warning.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;K&auml;rnfil (Integritet Felaktig)</div>
<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_unknown.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Icke k&auml;rnfiler</div>";
$ns -> tablerender("Filnyckel", $text);

$text = "Filinspekt&ouml;ren skannar och analyserar filerna p&aring; dins sajts server. N&auml;r inspekt&ouml;ren st&ouml;ter p&aring; en
e107 k&auml;rnfil kontrolleras denna f&ouml;r integritet och att den inte blivit korrupt.";

$ns -> tablerender("Hj&auml;lp Filinspekt&ouml;ren", $text);

?>
