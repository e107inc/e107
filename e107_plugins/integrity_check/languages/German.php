<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/integrity_check/languages/German.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-07-26 15:05:06 $
|     $Author: gatowlion $
|     $translated by: admin@cms-myway.vom (http://www.cms-myway.com)
+----------------------------------------------------------------------------+
*/	
define("Integ_01", "Speichern war erfolgreich");
define("Integ_02", "Speichern fehlgeschlagen");
define("Integ_03", "Fehlende Dateien:");
define("Integ_04", "CRC-Fehler:");
define("Integ_05", "Konnte nicht geöffnet werden...");
define("Integ_06", "File-Integrität überprüfen");
define("Integ_07", "Keine Files vorhanden");
define("Integ_08", "Überprüfen");
define("Integ_09", "Core Sfv-File erstellen");
define("Integ_10", "Ausgewählten Ordner+Unterordner <u>nicht</u> mit einbeziehen.");
define("Integ_11", "Dateiname:");
define("Integ_12", "Sfv-File erstellen");
define("Integ_13", "Integritäts-Überprüfung");
define("Integ_14", "SFV-Erstellung nicht möglich, da der Ordner ".e_PLUGIN."integrity_check/<b>{output}</b> nicht beschreibbar ist. Bitte chmodde diesen Ordner mit 777!");
define("Integ_15", "Alle Files sind völlig in Ordnung!");
define("Integ_16", "Kein Core-crc-File vorhanden");
define("Integ_17", "Keine Plugin-crc-Files vorhanden");
define("Integ_18", "Erstelle CRC-File fÃ¼r Plugin");
define("Integ_19", "Core-Checksum-Files");
define("Integ_20", "Plugin-Checksum-Files");
define("Integ_21", "Wähle das Plugin aus, für das du ein crc-file erstellen willst.");
define("Integ_22", "Benutze gzip");
define("Integ_23", "ÃœberprÃ¼fe nur Themes-Ordner, die auf deinem Host auch existieren");
define("Integ_24", "Administrator Haupt-Seite");
define("Integ_25", "Verlasse Admin-Breich");
define("Integ_26", "Lade Seite mit normalem Kopf");
	
//define("Integ_29", "<br /><br /><b>*<u>CRC-FEHLER:</u></b><br />Dies sind Prüfsummen-Fehler. Es gibt folgende Erklärungen:<br />-Du hast an einer Datei rumgebastelt, so dass sie nicht mehr in der Originalform ist.<br />-Die genannte Datei ist beschädigt, du solltest sie neu hochladen!");
//Sprachdatei sollte kein html verwenden.

define("Integ_30", "Du kannst, um die CPU-Intensität zu verringern, das Checken des CRC-Files in bis zu 10 Schritten durchführen lassen.");
define("Integ_31", "Schritte: ");
define("Integ_32", "Du hast noch eine Datei namens <b>log_crc.txt</b> in deinem Crc-Ordner. Bitte löschen! (Du kannst auch einen Refresh versuchen).");
define("Integ_33", "Du hast noch eine Datei namens <b>log_miss.txt</b> in deinem Crc-Ordner.  Bitte löschen! (Du kannst auch einen Refresh versuchen).");
define("Integ_34", "Dein Crc-Ordner ist nicht beschreibbar!");
define("Integ_35", "Aus den folgenden Gründen kannst du nur einen Schritt einstellen:");
define("Integ_36", "Klicke hier, wenn du es nicht erwarten kannst, zum nächsten Schritt zu kommen:");
define("Integ_37", "Klick mich");
define("Integ_38", "Noch <i><u>{counts}</u></i> Zeilen zu bearbeiten...");
define("Integ_39", "Bitte lösche die Datei:<br />".e_PLUGIN."integrity_check/<u><i>do_core_file.php</i></u>!<br />Sie ist nicht mehr aktuell, und war eh nie für die Öffentlichkeit gedacht...");
?>
