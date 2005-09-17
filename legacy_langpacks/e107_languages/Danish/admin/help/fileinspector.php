<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Danish/admin/help/fileinspector.php,v $
|        $Revision: 1.1 $
|        $Date: 2005-09-17 09:29:27 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/

$text = "<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_core.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Kerne Fil</div>
<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_check.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Kerne Fil (Integritet Ok)</div>
<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_warning.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Kerne Fil (Integritet Fejlet)</div>
<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_missing.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Manglende Kerne Fil</div>
<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_unknown.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Ikke Kerne Fil</div>";
$ns -> tablerender("Fil N&oslash;gle", $text);

$text = "Fil inspekt&oslash;ren scanner og analyserer filerne p&aring; dit sites server. N&aring;r inspekt&oslash;ren m&oslash;der 
en e107 kerne fil kontrollerer den for fil regelm&aelig;ssighed for at sikre at den ikke er korrupt.";

$ns -> tablerender("Fil Inspekt&oslash;r Hj&aelig;lp", $text);
?>