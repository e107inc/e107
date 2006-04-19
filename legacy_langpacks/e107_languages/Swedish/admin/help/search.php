<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Swedish/admin/help/search.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-04-19 09:33:06 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Om din version av MySQL servern st&ouml;der det s&aring; kan du &auml;ndra till MySQL sorteringsmetod vilken &auml;r snabbare &auml;n PHPs sorteringsmetod. Se preferenserna.<br /><br />
Om din sajt inneh&aring;ller Ideografiska spr&aring;k som Kinesiska eller Japanska m&aring;ste du anv&auml;nda PHP's sorteringsmetod och st&auml;nga av hel-ords matchningen.";
$ns -> tablerender("S&ouml;khj&auml;lp", $text);

?>