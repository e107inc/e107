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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/links.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/links.php rev. 1.4
+-----------------------------------------------------------------------------+
*/
 
if (!defined('e107_INIT')) { exit; }

$text = "Tutaj możesz dodać wszystkie linki związane bezpośrednio z Twoim serwisem. Dodane linki zostaną wyświetlone w głównym menu strony. Jeśli chcesz utworzyć stronę z linkami do zewnętrznych serwisów użyj plugina <i>Links Page</i> (Strona linków).
<br />
";
$ns -> tablerender("Linki", $text);

?>
