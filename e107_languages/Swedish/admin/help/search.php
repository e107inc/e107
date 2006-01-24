<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Swedish/admin/help/search.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-01-24 12:49:09 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Om din version av MySQL servern stöder det så kan du ändra till MySQL sorteringsmetod vilken är snabbare än PHPs sorteringsmetod. Se preferenserna.<br /><br />
Om din sajt innehåller Ideografiska språk som Kinesiska eller Japanska måste du använda PHP's sorteringsmetod och stänga av hel-ords matchningen.";
$ns -> tablerender("Sökhjälp", $text);

?>