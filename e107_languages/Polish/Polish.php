<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107 v0.7
|     Polskie wsparcie: http://e107.org.pl - http://e107poland.org
|
|     $Revision: 1.6 $
|     $Date: 2007-10-23 17:03:43 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/Polish.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/English.php rev. 1.11
+-----------------------------------------------------------------------------+
*/
 
setlocale(LC_ALL, 'pl_PL.UTF-8', 'pl_PL@euro', 'pl_PL', 'pl', 'Polish', 'Polish_Poland');
define("CORE_LC", 'pl');
define("CORE_LC2", 'pl');
// define("TEXTDIRECTION","rtl");
define("CHARSET", "utf-8");
define("CORE_LAN1","Błąd : brak tematu.\\n\\nZmień używany temat w ustawieniach (panel administratora) lub załaduj pliki aktualnie używanego tematu na serwer.");

//v.616
define("CORE_LAN2"," \\1 napisał:");// "\\1" reprezentuje nazwę użytkownika.
define("CORE_LAN3","wysyłanie plików zostało zablokowane");

//v0.7+
define("CORE_LAN4", "Proszę usunąć plik install.php ze swojego serwera.");
define("CORE_LAN5", "Jeśli tego nie zrobisz, może istnieć ryzyko naruszenia bezpieczeństwa na Twojej stronie internetowej.");

// v0.7.6
define("CORE_LAN6", "Ochrona przeciwko atakom typu flood na tej stronie została aktywowana. Ostrzegam, że kontynuacja wywoływania tej strony może spowodować dla Ciebie  zablokowanie dostępu do serwisu.");
define("CORE_LAN7", "Usiłuję przywrócić preferencje jądra z automatycznej kopii bezpieczeństwa.");
define("CORE_LAN8", "Błąd preferencji jądra");
define("CORE_LAN9", "Jądro nie może zostać przywrócone z automatycznej kopii bezpieczeństwa. Zatrzymano wykonywanie.");
define("CORE_LAN10", "Wykryto uszkodzony plik cookie - wyloguj się.");

// Footer
define("CORE_LAN11", "Czas generowania: ");
define("CORE_LAN12", " sek., ");
define("CORE_LAN13", " z tego dla zapytań. ");
define("CORE_LAN14", "");			// Used in 0.8
define("CORE_LAN15", "Zapytań BD: ");
define("CORE_LAN16", "Użycie pamięci: ");


define("CORE_LAN_B", "b");
define("CORE_LAN_KB", "kB");
define("CORE_LAN_MB", "MB");
define("CORE_LAN_GB", "GB");
define("CORE_LAN_TB", "TB");

define("LAN_WARNING", "Ostrzeżenie!");
define("LAN_ERROR", "Błąd");
define("LAN_ANONYMOUS", "Anonim");
define("LAN_EMAIL_SUBS", "-e-mail-");

?>
