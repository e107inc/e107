<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/e107_languages/Danish/admin/help/fileinspector.php,v $
|        $Revision: 1.2 $
|        $Date: 2005-07-09 11:35:20 $
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
$ns -> tablerender("Fil Nøgle", $text);

$text = "Fil inspektøren scanner og analyserer filerne på dit sites server. Når inspektøren møder 
en e107 kerne fil kontrollerer den for fil regelmæssighed for at sikre at den ikke er korrupt.";

$ns -> tablerender("Fil Inspektør Hjælp", $text);
?>