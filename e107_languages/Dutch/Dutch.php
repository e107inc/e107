<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|
|	Â©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|	NL-vertaling AndrÃ© Koot, meneer.at.tken.net
|	Bijdragen van Eric Vanderfeesten, lisa.at.eindhovenseschool.net
|		        Frank Fokke, info.at.frankfokke.nl
|                   Robin, killa.at.dutchmp.tk   
|                   Patrick Buijs, afdrameetingpoint.at.chello.nl
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

//setlocale(LC_TIME, 'nl_NL'); 
setlocale(LC_ALL, 'nl_NL.utf8', 'nld_nld.utf8', 'nld.utf8', 'nl.utf8', 'nl');
define("CORE_LC", 'nl');
define("CORE_LC2", 'nl');


//define("CHARSET", "iso-8859-1");
define("CHARSET", "utf-8");
define("CORE_LAN1","Fout : theme ontbreekt.\\n\\nWijzig het te gebruiken theme in de Theme manager (in het beheerscherm) of upload de bestanden van het huidige theme naar de server.");
define("CORE_LAN2"," \\1 schreef:");       // "\\1" representeert de gebruikersnaam.
define("CORE_LAN3","bestandbijlage gedeactiveerd");
define("CORE_LAN4", "Verwijder install.php van je server");
define("CORE_LAN5", "als je dat niet doet bestaat er een beveiligingsrisico voor je website");
define("CORE_LAN6", "De 'flood'-bescherming op deze site is geactiveerd en je wordt gewaarschuwd dat je kunt worden geblokkeerd als je doorgaat pagina's op te vragen.");
define("CORE_LAN7", "Core probeert de voorkeuren vanuit de automatische backup te herstellen.");
define("CORE_LAN8", "Core voorkeuren FOUT");
define("CORE_LAN9", "Core kon niet worden hersteld vanaf de automatische backup. Uitvoering gestopt.");
define("CORE_LAN10", "Corrupt cookie gedetecteerd - uitgelogd.");
define("CORE_LAN11", "Opbouwtijd: ");
define("CORE_LAN12", " sec, ");
define("CORE_LAN13", " daarvan voor queries. ");
define("CORE_LAN14", "%2.3f cpu sec (%2.2f%% belasting, %2.3f opstart). Klok: ");
define("CORE_LAN15", "DB queries: ");
define("CORE_LAN16", "Geheugengebruik: ");
define("CORE_LAN_B", "b");
define("CORE_LAN_KB", "kb");
define("CORE_LAN_MB", "Mb");
define("CORE_LAN_GB", "Gb");
define("CORE_LAN_TB", "Tb");

define("LAN_WARNING", "Waarschuwing!");
define("LAN_ERROR", "Fout");
define("LAN_ANONYMOUS", "Anoniem");
define("LAN_EMAIL_SUBS", "-e-mailadres-");
?>