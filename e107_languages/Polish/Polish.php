<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107 v0.7
|     Polskie wsparcie: http://e107.org.pl - http://e107poland.org
|
|     $Revision: 1.5 $
|     $Date: 2007-02-19 20:38:58 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/Polish.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/English.php rev. 1.9
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


define("LAN_WARNING", "Ostrzeżenie!");
define("LAN_ERROR", "Błąd");
define("LAN_ANONYMOUS", "Anonim");

?>
