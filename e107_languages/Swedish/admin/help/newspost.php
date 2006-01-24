<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Swedish/admin/help/newspost.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-01-24 12:49:09 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Hjälp nyhetsposter";
$text = "<b>Generellt</b><br />
Texten kommer att visas på huvudsidan, den utökade texten kan läsas genom att klicka på en 'Läs mer' länk.
<br />
<br />
<b>Visa endast rubrik</b>
<br />
Aktivera detta för att endast visa rubriken på förstasidan, med en klickbar länk till hela nyhetsartikeln.
<br /><br />
<b>Aktivering</b>
<br />
Om du sätter ett start och/eller stlutdatum på din nyhet kommer den endast att visas mellan dessa datum.
";
$ns -> tablerender($caption, $text);

?>
