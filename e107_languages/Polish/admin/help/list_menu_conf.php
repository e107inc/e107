<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File.
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107 v0.7
|     Polskie wsparcie: http://e107.org.pl - http://e107poland.org
|
|     $Revision: 1.7 $
|     $Date: 2008-08-27 11:57:51 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/list_menu_conf.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/list_menu_conf.php rev. 1.2
+-----------------------------------------------------------------------------+
*/
 
if (!defined('e107_INIT')) { exit; }

$text = "W tej sekcji możesz skonfigurować 3 menu<br>
<b> Menu nowych artykułów</b> <br>
Wpisz w pierwszym polu liczbę, na przykład '5', aby wyświetlić 5 pierwszych artykułów. Pozostaw puste, aby wyświetlić wszystkie dostępne artykuły, W drugim polu możesz skonfigurować nazwę linku do reszty artykułów. Jeśli wskazane pole pozostawisz puste nie zostanie utworzony link do działu artykułów, na przykład: 'Wszystkie artykuły'<br>
<b> Menu komentarzy/forum</b> <br>
Domyślna ilość komentarzy to 5, natomiast domyślna dozwolona ilość znaków to 10000. Przyrostek służy do przycięcia linii, które są zbyt długie, a następnie dodania na ich końcu wskazanego tutaj ciągu znaków, dobrym wyborem dla tej opcji są trzy kropki '...'. Sprawdź oryginalny temat, jeśli chcesz zobaczyć tą funkcję w praktyce.<br>
";
$ns -> tablerender("Konfiguracja menu", $text);

?>
