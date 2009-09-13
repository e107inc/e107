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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/banlist.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/banlist.php rev. 1.3
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
Wpisanie adresu email *@bar.com zablokuje każdego zarejestrowanego użytkownika używającego adresu email pochodzącego ze wskazanej domeny.<br /><br />
<b>Blokowanie przez nazwę użytkownika</b><br />
Jest to wykonalne z podstrony 'Użytkownicy' w 'Panelu admina'.";
$ns -> tablerender($caption, $text);

?>
