<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File.
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107 v0.7
|     Polskie wsparcie: http://e107.org.pl - http://e107poland.org
|
|     $Revision: 1.5 $
|     $Date: 2007-10-23 17:03:47 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/poll.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/poll.php rev. 1.2
+-----------------------------------------------------------------------------+
*/
 
if (!defined('e107_INIT')) { exit; }

$text = "Na tej stronie możesz tworzyć ankiety oraz sondy. Aby utworzyć ankietę (sondę) wpisz jej tytuł oraz opcje głosowania, następnie podejrzyj ją i, po zaakceptowaniu wyglądu, wybierz grupę, którą będzie mogła uczestniczyć w głosowaniu.<br /><br />
Aby zobaczyć ankietę, przejdź do strony <i>Menu</i> i upewnij się, że menu <i>poll_menu</i> jest aktywne.";
$ns -> tablerender("Ankiety", $text);

?>
