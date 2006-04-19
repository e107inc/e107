<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Swedish/admin/help/fileinspector.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-04-19 09:33:06 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "&lt;div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'&gt;
&lt;img src='".e_IMAGE."fileinspector/file_core.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' /&gt;&nbsp;K&auml;rnfil&lt;/div&gt;
&lt;div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'&gt;
&lt;img src='".e_IMAGE."fileinspector/file_warning.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' /&gt;&nbsp;K&auml;nd os&auml;kerhet&lt;/div&gt;
&lt;div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'&gt;
&lt;img src='".e_IMAGE."fileinspector/file_check.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' /&gt;&nbsp;K&auml;rnfil (Integritet Pass)&lt;/div&gt;
&lt;div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'&gt;
&lt;img src='".e_IMAGE."fileinspector/file_fail.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' /&gt;&nbsp;K&auml;rnfil (Integritet Felaktig)&lt;/div&gt;
&lt;div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'&gt;
&lt;img src='".e_IMAGE."fileinspector/file_uncalc.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' /&gt;&nbsp;K&auml;rnfil (Ej ber&auml;kningsbar)&lt;/div&gt;
&lt;div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'&gt;
&lt;img src='".e_IMAGE."fileinspector/file_missing.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' /&gt;&nbsp;Saknad k&auml;rnfil&lt;/div&gt;
&lt;div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'&gt;
&lt;img src='".e_IMAGE."fileinspector/file_old.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' /&gt;&nbsp;Gammal k&auml;rnfil&lt;/div&gt;
&lt;div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'&gt;
&lt;img src='".e_IMAGE."fileinspector/file_unknown.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' /&gt;&nbsp;Ej k&auml;rnfil&lt;/div&gt;";
$ns -> tablerender("Filenyckel", $text);

$text = "Filinspekt&ouml;ren skannar och analyserar filerna p&aring; dins sajts server. N&auml;r inspekt&ouml;ren st&ouml;ter p&aring; en
e107 k&auml;rnfil kontrolleras denna f&ouml;r integritet och att den inte blivit korrupt.";

if ($pref['developer']) {
$text .= "<br /><br />
Det extra str&auml;ng-matchningsverktyget (endast i utvecklarl&auml;ge) l&aring;ter dig s&ouml;ka efter textstr&auml;ngar i filerna p&aring; din server
genom att avn&auml;nda regulj&auml;ra uttryck. Regex motorn som anv&auml;nds &auml;r PHP's &lt;a href='http://php.net/pcre'&gt;PCRE&lt;/a&gt;
(preg_* funktionerna), s&aring; ange din fr&aring;ga som #m&ouml;nster#modifierare i f&auml;lten f&ouml;r detta.";
}

$ns -> tablerender("Hj&auml;lp Filinspekt&ouml;ren", $text);

?>
