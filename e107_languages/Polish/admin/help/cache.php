<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107
|     e107 Polish Team
|     Polskie wsparcie: http://e107pl.org
|
|     $Revision: 1.8 $
|     $Date: 2009-09-13 10:26:27 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/cache.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/cache.php rev. 1.2
+-----------------------------------------------------------------------------+
*/
 
if (!defined('e107_INIT')) { exit; }

$caption = "Cache strony";
$text = "Włączenie pamięci podręcznej znacznie zwiększy szybkość działania Twojego serwisu oraz zminimalizujesz ilość połączeń do bazy danych SQL.<br /><br /><b>UWAGA! Podczas tworzenia własnego tematu graficznego powinieneś wyłączyć cachowanie, ponieważ w przeciwnym wypadku jakiekolwiek wprowadzone zmiany nie będą odzwierciedlone.</b>";
$ns -> tablerender($caption, $text);

?>
