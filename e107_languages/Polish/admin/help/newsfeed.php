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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/newsfeed.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/newsfeed.php rev. 1.2
+-----------------------------------------------------------------------------+
*/
 
if (!defined('e107_INIT')) { exit; }

$text = "Za pośrednictwem tej strony możesz pobierać i wyświetlać kanały wiadomości RSS pochodzące z innych serwisów na Twojej własnej stronie.<br />Wpisz pełną ścieżkę adresu URL do kanału (np. http://e107.org/news.xml). Możesz również dodać ścieżkę do obrazka, jeśli nie podoba Ci się domyślny lub nie jest on zdefiniowany. Z poziomu tej strony możesz również aktywować i dezaktywować kanały, jeśli strona przestała je nadawać.<br /><br />Aby zobaczyć nagłówki na swojej stronie upewnij się, że na stronie <i>Menu</i> zostało aktywowane menu <i>headlines_menu</i>.";

$ns -> tablerender("Kolporter RSS", $text);

?>
