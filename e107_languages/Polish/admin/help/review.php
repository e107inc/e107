<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File.
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107 v0.7
|     Polskie wsparcie: http://e107.org.pl - http://e107poland.org
|
|     $Revision: 1.3 $
|     $Date: 2006-11-24 15:37:55 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/review.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/review.php rev. 1.3
+-----------------------------------------------------------------------------+
*/
 
if (!defined('e107_INIT')) { exit; }

$text = "Recenzje są podobne do artykułów, lecz ich wykaz będzie pokazywany w ich własnym mene.<br />
Aby utworzyć recenzję wielostronicową, oddziel każdą stronę tekstem [newpage], np. <br /><code>Strona testowa 1 [newpage] Strona testowa 2</code><br /> utworzy recenzję dwustronicową z tekstem 'Strona testowa 1' na pierwszej stronie oraz 'Strona testowa 2' na stronie drugiej.";
$ns -> tablerender("Recenzje", $text);

?>
