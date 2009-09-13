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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/notify.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/notify.php rev. 1.2
+-----------------------------------------------------------------------------+
*/
 
if (!defined('e107_INIT')) { exit; }

$text = "Powiadomienia email będą rozsyłane, gdy na Twojej stronie zajdzie jakieś ściśle określone wydarzenie.<br /><br />
Na przykład, ustawienie powiadomienia o <i>Adresach IP zablokowanych w wyniku ataków typu flood</i> dla grupy <i>Adminstratorzy</i> spowoduje wysłanie emaili do wszystkich administratorów, kiedy Twojej stronie będzie groziło zapchanie w wyniku ataku typu flood.<br /><br />
Możesz również, jako inny przykład, ustawić powiadomienie o <i>Nowych pozycjach dodanych przez administratorów</i> dla grupy <i>Zarejestrowani</i>, co spowoduje wysłanie emaili o nowościach dodanych do serwisu do wszystkich użytkowników serwisu.<br /><br />
Jeśli chcesz, aby powiadomienia email były wysyłane na alternatywny adres email - zaznacz opcję <i>Email</i> i wpisz w pole docelowy adres email.";

$ns -> tablerender("Powiadomienia", $text);

?>
