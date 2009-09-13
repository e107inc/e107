<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File.
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107 v0.7
|     Polskie wsparcie: http://e107.org.pl - http://e107poland.org
|
|     $Revision: 1.8 $
|     $Date: 2009-09-13 10:26:27 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/search.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/search.php rev. 1.6
+-----------------------------------------------------------------------------+
*/
 
if (!defined('e107_INIT')) { exit; }

$text = "Jeśli Twoja wersja serwera MySQL wspiera ta opcję możesz przełączyć na metodę sortowania MySQL, ponieważ jest ona szybsza niż metoda sortowania PHP. Zobacz w preferencjach.<br /><br />
Jeśli Twoja strona zawiera języki ideograficzne takie jak chiński oraz japoński, musisz używać metody sortowania PHP oraz musisz również wyłączyć opcję <i>Tylko pełne słowa</i>.";
$ns -> tablerender("Wyszukiwanie", $text);

?>
