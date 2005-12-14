<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Danish/admin/help/fileinspector.php,v $
|        $Revision: 1.2 $
|        $Date: 2005-12-14 16:16:10 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/

$text = "<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_core.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Kerne Fil</div>
<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_warning.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Kendt sikkerhedsbrist</div>
<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_check.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Kerne Fil (Integritet Ok)</div>
<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_fail.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Kerne Fil (Integritet Fejlet)</div>
<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_uncalc.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Kerne Fil (Uberegnelig)</div>
<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_missing.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Manglende Kerne Fil</div>
<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_old.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Gammel Kerne Fil</div>
<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
<img src='".e_IMAGE."fileinspector/file_unknown.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Ej Kerne Fil</div>";
$ns -> tablerender("Fil N&oslash;gle", $text);

$text = "Fil inspektøren scanner og analyserer filerne p&aring; dit sites server. N&aring;r inspekt&oslash;ren m&oslash;der
en e107 kerne fil kontrollerer den for fil regelm&aelig;ssighed for at sikre at den ikke er korrupt.";

if ($pref['developer']) {
$text .= "<br /><br />
Det ydeligere streng sammenligningsv&aelig;rkt&oslash;j (kun udvikler modus) lader dig scanne filerne p&aring; din server for tekst strenge
der bruger almindelige udtryk. regex motoren i brug er PHP's <a href='http://php.net/pcre'>PCRE</a>
(preg_* functions), s&aring; skriv din forsp&oslash;rgsel som #pattern#modifiers i de angivne felter.";
}

$ns -> tablerender("Fil Inspekt&oslash;r Hj&aelig;lp", $text);
?>