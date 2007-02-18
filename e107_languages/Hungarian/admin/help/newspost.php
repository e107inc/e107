<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Hungarian Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/newspost.php,v $
|     $Revision: 1.5 $
|     $Date: 2007-02-18 01:59:51 $
|     $Author: lisa_
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Új hír - Súgó";
$text = "<b>Általános</b><br />
A törzs a főoldalon fog megjelenni, míg a bővített a 'Tovább' linkre kattintva lesz olvasható.
<br />
<br />
<b>Csak a cím mutatása</b>
<br />
Ennek engedélyezésekor a főoldalon csak a hír címe jelenik meg,egy kattintható linkkel a teljes szöveghez.
<br /><br />
<b>Aktiválás</b>
<br />
Ha megadsz egy kezdési és/vagy befejezési dátumot, akkor a cikk csak a két időpont közt jelenik meg.
";
$ns -> tablerender($caption, $text);
?>
