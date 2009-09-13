<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File.
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107 v0.7
|     Polskie wsparcie: http://e107.org.pl - http://e107poland.org
|
|     $Revision: 1.8 $
|     $Date: 2009-09-13 10:26:30 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/integrity_check/languages/Polish.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_plugins/integrity_check/languages/English.php rev. 1.6
+-----------------------------------------------------------------------------+
*/
 
define("Integ_01", "Zapis zakończono pomyślnie");
define("Integ_02", "Zapis nieudany");
define("Integ_03", "Brakujące pliki:");
define("Integ_04", "Błędy CRC:");
define("Integ_05", "Nie mogę otworzyć pliku...");
define("Integ_06", "Sprawdzenie integralności plików");
define("Integ_07", "Nie ma dostępnych plików");
define("Integ_08", "Sprawdź integralność");
define("Integ_09", "Utwórz plik sfv");
define("Integ_10", "Wybrany katalog <u>nie</u> nie zostanie zapisany wewnątrz pliku crc.");
define("Integ_11", "Nazwa pliku:");
define("Integ_12", "Utwórz plik sfv");
define("Integ_13", "Sprawdzanie integralności");
define("Integ_14", "Utworzenie pliku SFV nie jest możliwe, ponieważ folder ".e_PLUGIN."integrity_check/<b>{output}</b> nie jest zapisywalny. Proszę ustawić chmod tego folderu na wartość 777!");
define("Integ_15", "Wszystkie pliki zostały sprawdzone i są poprawne!");
define("Integ_16", "Plik crc jądra jest niedostępny");
define("Integ_17", "Pliki crc pluginów są niedostępne");
define("Integ_18", "Tworzenie pliku CRC dla pluginów");
define("Integ_19", "Pliki sum kontrolnych jądra");
define("Integ_20", "Pliki sum kontrolnych dla pluginów");
define("Integ_21", "Wybierz plugin, dla którego chciałbyś utworzyć plik crc.");
define("Integ_22", "Użyj formatu gzip");
define("Integ_23", "Sprawdź tylko zainstalowane tematy");
define("Integ_24", "Panel administratora");
define("Integ_25", "Opuść panel admina");
define("Integ_26", "Załaduj stronę z normalnym nagłówkiem");
define("Integ_27", "UŻYJ INSPEKTORA PLIKÓW W CELU SPRAWDZENIA PLIKÓW JĄDRA");
	
// define("Integ_29", "<br /><br /><b>*<u>CRC-ERRORS:</u></b><br />These are checksum errors and there are two possible reasons for this:<br />-You changed something within the mentioned file, so it isn't longer the same as the original.<br />-The mentioned file is corrupt, you should reupload it!");
// language file should contain NO html. 

define("Integ_30", "Dla mniejszego zużycia procesora, możesz wykonać sprawdzenie plików w krokach od 1 do 10.");
define("Integ_31", "Kroki: ");
define("Integ_32", "Aktualnie w katalogu crc znajduje się plik nazwany <b>log_crc.txt</b>. Proszę go usunąć! (Lub spróbować odświeżyć).");
define("Integ_33", "Aktualnie w katalogu crc znajduje się plik nazwany <b>log_miss.txt</b>. Proszę go usunąć! (Lub spróbować odświeżyć).");
define("Integ_34", "Twój folder crc jest niezapisywalny!");
define("Integ_35", "Z powodu następujących przyczyn możesz wybrać tylko <b>jeden</b> krok:");
define("Integ_36", "Kliknij tutaj, jeśli nie chcesz czekać 5 sekund do następnego kroku:");
define("Integ_37", "Kliknij");
define("Integ_38", "Jeszcze <u><i>{counts}</i></u> linii do wykonania...");
define("Integ_39", "Proszę usunąć plik:<br />".e_PLUGIN."integrity_check/<u><i>do_core_file.php</i></u>!<br />Jest on nieaktualny i nigdy nie był przeznaczony do publicznego wypuszczenia...");

?>
