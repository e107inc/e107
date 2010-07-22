<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvsroot/e107german/e107_0.7/e107_langpacks/e107_languages/German/German.php,v $
|     $Revision: 1.2 $
|     $Date: 2009/11/29 06:09:23 $
|     $Author: lars78 $
|     $translated by: admin@cms-myway.com (http://www.cms-myway.com) $
|     $ UTF-8 encoded $
|     $updated by: webmaster@e107cms.de (http://www.e107cms.de) $
+----------------------------------------------------------------------------+
*/
setlocale(LC_ALL, "de", "de_DE.utf8", "de_DE@euro", "de_DE", "deu_deu");
define("CORE_LC", "de");
define("CORE_LC2", "de");
// define("TEXTDIRECTION","rtl");
define("CHARSET", "utf-8");
define("CORE_LAN1", "Fehler : Theme fehlt.\n\nÄndern Sie das Theme unter Einstellungen oder laden Sie ein neues Theme auf den Server.");

//v.616
//obsolete define define("CORE_LAN2", " \1 schrieb:");// "\1");
//obsolete define define("CORE_LAN3", "Dateianhang nicht erlaubt/ausgeschaltet");

//v0.7+
define("CORE_LAN4", "Bitte löschen Sie die install.php auf dem Server");
define("CORE_LAN5", "falls Sie dem nicht nachkommen, besteht ein potenzielles Sicherheitsrisiko für Ihre Webseite");

// v0.7.6
define("CORE_LAN6", "Die -flood protection- wurde auf dieser Seite aktiviert und Sie sind hiermit gewarnt gebannt zu werden, sollten Sie weiterhin versuchen Seiten zu erreichen.");
define("CORE_LAN7", "Es wird versucht die Voreinstellungen des Cores aus dem automatischen Backup wieder herzustellen.");
define("CORE_LAN8", "Core Voreinstellungen Fehler");
define("CORE_LAN9", "Der Core konnte aus dem automatischen Backup nicht wieder hergestellt werden. Ausführung nicht möglich.");
define("CORE_LAN10", "Fehlerhaftes Cookie entdeckt - Sie wurden ausgelogged.");

// Footer
define("CORE_LAN11", "Seitenaufbauzeit:");
define("CORE_LAN12", "sek,");
define("CORE_LAN13", "davon für Abfragen");
define("CORE_LAN14", "");			// Used in 0.8
define("CORE_LAN15", "DB Abfragen");
define("CORE_LAN16", "Speicher Nutzung:");

// img.bb
define('CORE_LAN17', '[ Bild deaktiviert ]');
define('CORE_LAN18', 'Bild: ');
define("CORE_LAN_B", "B");
define("CORE_LAN_KB", "kB");
define("CORE_LAN_MB", "MB");
define("CORE_LAN_GB", "GB");
define("CORE_LAN_TB", "TB");


define("LAN_WARNING", "Warnung!");
define("LAN_ERROR", "Fehler");
define("LAN_ANONYMOUS", "Unbekannter");
define("LAN_EMAIL_SUBS", "-email-");

?>