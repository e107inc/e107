<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File.
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107 v0.7
|     Polskie wsparcie: http://e107.org.pl - http://e107poland.org
|
|     $Revision: 1.13 $
|     $Date: 2008-01-08 19:25:41 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/lan_upload.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/lan_upload.php rev. 1.7
+-----------------------------------------------------------------------------+
*/
 
define("UPLLAN_1", "Nadesłany plik został usunięty z listy.");
define("UPLLAN_2", "Ustawienia zostały zapisane w bazie danych");
define("UPLLAN_3", "ID nadesłanego pliku");

define("UPLLAN_5", "Nadawca");
define("UPLLAN_6", "Email");
define("UPLLAN_7", "Strona domowa");
define("UPLLAN_8", "Nazwa pliku");

define("UPLLAN_9", "Wersja");
define("UPLLAN_10", "Plik");
define("UPLLAN_11", "Rozmiar pliku");
define("UPLLAN_12", "Zrzut ekranu");
define("UPLLAN_13", "Opis");
define("UPLLAN_14", "Demo");

define("UPLLAN_16", "Kopiuj do formularza aktualności");
define("UPLLAN_17", "usuń nadesłane pliki z listy");
define("UPLLAN_18", "Wyświetl szczegóły");
define("UPLLAN_19", "Nie ma żadnych nadesłanych plików do sprawdzenia");
define("UPLLAN_20", "Obecnie");
define("UPLLAN_21", "załadowane pliki, które nie zostały sprawdzone");
define("UPLLAN_22", "ID");
define("UPLLAN_23", "Nazwa");
define("UPLLAN_24", "Rodzaj pliku");
define("UPLLAN_25", "Włączyć dział uploadu?");
define("UPLLAN_26", "Jeśli jest wyłączone, nadsyłane pliki będą odrzucane");
define("UPLLAN_27", "Niezweryfikowane pliki"); //unmoderated public uploads - sprawdzić

define("UPLLAN_29", "Sposób przechowywania");
define("UPLLAN_30", "Wybierz jak przechowywać nadesłane pliki, czy jako normalne pliki na serwerze, lub też jako obiekty binarne w bazie danych<br /><b>Uwaga</b> sposób binarny jest tylko odpowiedni da bardzo małych plików w przybliżeniu do 500kb");
define("UPLLAN_31", "Zwykły plik");
define("UPLLAN_32", "Binarny");
define("UPLLAN_33", "Maksymalny rozmiar pliku");
define("UPLLAN_34", "Maksymalny rozmiar pliku w bajtach - pozostaw puste, aby zastosować ustawienia z pliku php.ini");
define("UPLLAN_35", "Zezwalaj na następujące typy plików");
define("UPLLAN_36", "Proszę wpisać po jednym typie w linii");
define("UPLLAN_37", "Uprawnienia");
define("UPLLAN_38", "Zaznacz, aby pozwolić tylko niektórym użytkownikom na nadsyłanie");
define("UPLLAN_39", "Wyślij");

define("UPLLAN_41", "Uwaga - nadsyłanie plików jest wyłączone w pliku php.ini. Nnadsyłanie plików nie będzie możliwe dopóki nie ustawisz odpowiedniej opcji na <i>On</i> (włączone).");

define("UPLLAN_42", "Wykonaj");
define("UPLLAN_43", "Nadesłane pliki");
define("UPLLAN_44", "Upload");

define("UPLLAN_45", "Czy jesteś pewien usunięcia następujących plików...");

define("UPLAN_COPYTODLM", "Kopiuj do menedżera plików do pobrania");
define("UPLAN_IS", "jest ");
define("UPLAN_ARE", "są ");
define("UPLAN_COPYTODLS", "Kopiuj do działu download");

define("UPLLAN_48", "Ze względu na bezpieczeństwo dozwolone typy plików zostały usunięte z bazy danych do pliku 
flatfile zlokalizowanego w katalogu administracyjnym ".e_ADMIN.". Aby użyć tej funkcji, zmień nazwę pliku ".e_ADMIN."filetypes_.php na ".e_ADMIN."filetypes.php 
i dodaj do niego po przecinkach listę dozwolonych rozszerzeń typów plików. Nie powinieneś zezwalać na nadsyłanie plików .html, .txt, etc,
jako że napastnik może załadować tego typu plik z zawartym w nim złośliwym kodem javascript. Powinieneś również, jeśli to możliwe, nie zezwalać 
na nadsyłanie plików .php oraz innego typu wykonywalnych skryptów.");

?>
