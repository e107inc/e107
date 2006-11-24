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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/banlist.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/banlist.php rev. 1.2
+-----------------------------------------------------------------------------+
*/
 
if (!defined('e107_INIT')) { exit; }

$caption = "Blokowanie użytkowników";
$text = "Na tej stronie możesz blokować użytkowników odwiedzających Twoja stronę.<br />
Proszę wpisywać pełny adres IP lub używać znaku zastępczego (*) do zablokowania wskazanej puli adresów IP. Możesz również wpisać adres email w celu zablokowania użytkowników zarejestrowanych na Twojej stronie.<br /><br />
<b>Blokowanie przez adres IP:</b><br />
Wpisanie adresu 123.123.123.123 zablokuje użytkowników odwiedzających Twoją stronę ze wskazanego adresu IP.<br />
Wpisanie adresu 123.123.123.* zablokuje użytkowników odwiedzających Twoją stronę ze wskazanej puli adresów IP.<br /><br />
<b>Blokowanie przez adres email:</b><br />
Wpisanie adresu email foo@bar.com zablokuje każdego zarejestrowanego użytkownika używającego tego adresu na Twojej stronie.<br />
Wpisanie adresu email *@bar.com zablokuje każdego zarejestrowanego użytkownika używającego adresu email pochodzącego ze wskazanej domeny.";
$ns -> tablerender($caption, $text);

?>
