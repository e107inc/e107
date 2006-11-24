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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/chatbox.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/chatbox.php rev. 1.2
+-----------------------------------------------------------------------------+
*/
 
if (!defined('e107_INIT')) { exit; }

$text = "Na tej stronie możesz ustawić preferencje dla czata..<br />Jeśli zaznaczysz pole <i>Zastąp linki</i>, każdy wpisany link będzie zastąpiony przez tekst, który wpiszesz w pole tekstowe obok zaznaczonego pola - Ta opcja rozwiązuje problem z wyświetlaniem długich linków.<br />Opcja <i>Zawijanie wyrazów</i> będzie zwijać tekst dłuższy niż zdefiniowanej tutaj długości.";

$ns -> tablerender("Czat", $text);

?>
