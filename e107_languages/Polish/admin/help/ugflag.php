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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/ugflag.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/ugflag.php rev. 1.3
+-----------------------------------------------------------------------------+
*/
 
if (!defined('e107_INIT')) { exit; }

$text = "Jeśli aktualizujesz e107 do nowszej wersji lub po prostu potrzebujesz przełączyć swoją stronę na chwilę w tryb offline zaznacz pole <i>Aktywuj tryb konserwacji</i>. Od tej chwili odwiedzający Twoja stronę będą przekierowani do strony wyjaśniającej przyczyny przerwy technicznej. Gdy zakończysz odznacz pole, aby przywrócić normalne funkcjonowanie serwisu.";

$ns -> tablerender("Konserwacja", $text);

?>
