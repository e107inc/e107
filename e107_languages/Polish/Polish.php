<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107
|     e107 Polish Team
|     Polskie wsparcie: http://e107pl.org
|
|     $Revision: 1.14 $
|     $Date: 2009/09/13 08:17:46 $
|     $Author: marcelis_pl $
|     $Source: /cvsroot/e107pl/e107_main/0.7_PL_strict_utf8/e107_languages/Polish/Polish.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/English.php v 1.15
+-----------------------------------------------------------------------------+
*/
 
setlocale(LC_ALL, 'pl_PL.UTF-8', 'pl_PL.utf8', 'pol_pol.utf8', 'pl_PL@euro', 'pl_PL', 'pl', 'Polish', 'Polish_Poland');
define("CORE_LC", 'pl');
define("CORE_LC2", 'pl');
// define("TEXTDIRECTION","rtl");
define('CHARSET', 'utf-8');  // for a true multi-language site. :)
define("CORE_LAN1","Błąd: brak motywu graficznego.\\n\\nZmień używany motyw w ustawieniach (panel administratora) lub załaduj pliki bieżącego motywu na serwer.");

//v.616
//obsolete define("CORE_LAN2"," \\1 napisał:");// 1 reprezentuje nazwę użytkownika.
//obsolete define("CORE_LAN3","załączniki wyłączono");

//v0.7+
define("CORE_LAN4", "Proszę usunąć plik install.php ze swojego serwera.");
define("CORE_LAN5", "Jeśli tego nie zrobisz, zaistnieje ryzyko naruszenia bezpieczeństwa na twojej stronie internetowej.");

// v0.7.6
define("CORE_LAN6", "Ochrona przeciwko atakom przez przepełnienie została aktywowana. Uprzedzam, że kontynuacja wywoływania tej strony spowoduje zablokowanie dostępu do serwisu.");
define("CORE_LAN7", "Rdzeń próbuje przywrócić ustawienia z automatycznej kopii zapasowej.");
define("CORE_LAN8", "Błąd preferencji rdzenia");
define("CORE_LAN9", "Nie można przywrócić rdzenia z automatycznej kopii zapasowej. Zatrzymano wykonywanie.");
define("CORE_LAN10", "Wykryto uszkodzony plik ciasteczka - wyloguj się.");

// Footer
define("CORE_LAN11", "Czas generowania: ");
define("CORE_LAN12", " sek., ");
define("CORE_LAN13", " z tego dla zapytań. ");
define("CORE_LAN14", "");			// Used in 0.8
define("CORE_LAN15", "Zapytań do bazy danych: ");
define("CORE_LAN16", "Użycie pamięci: ");

// img.bb
define('CORE_LAN17', '[ grafika niedostępna ]');
define('CORE_LAN18', 'Grafika: ');

define("CORE_LAN_B", "B");
define("CORE_LAN_KB", "KB");
define("CORE_LAN_MB", "MB");
define("CORE_LAN_GB", "GB");
define("CORE_LAN_TB", "TB");

define("LAN_WARNING", "Ostrzeżenie!");
define("LAN_ERROR", "Błąd");
define("LAN_ANONYMOUS", "Anonimowy");
define("LAN_EMAIL_SUBS", "-e-mail-");

?>