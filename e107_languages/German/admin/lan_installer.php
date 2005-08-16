<?php

define("LANINS_001", "e107 Installation");


define("LANINS_002", "Schritt ");
define("LANINS_003", "1");
define("LANINS_004", "Sprachauswahl");
define("LANINS_005", "Bitte wählen Sie eine Sprache, die Sie während des Installationsprozesses verwenden möchten");
define("LANINS_006", "Sprache wählen");
define("LANINS_007", "2");
define("LANINS_008", "PHP &amp; MySQL Versionsüberprüfung / Dateirechte Überüfung");
define("LANINS_009", "Dateirechte nochmals prüfen");
define("LANINS_010", "Datei nicht beschreibbar: ");
define("LANINS_011", "Fehler");
define("LANINS_012", "MySQL Functionen scheinen nicht zu existieren. Dies kann entweder bedeuten, dass eine MySQL PHP Erweiterung nicht installiert oder nicht richtig konfiguriert ist."); // Hilfe für 012
define("LANINS_013", "Ihre MySQL Version's Nummer konnte nicht ermittelt werden. Dies könnte bedeuten, dass Ihr MySQL Server 'down' ist, oder keine Verbindung möglich ist.");
define("LANINS_014", "Datei Rechte");
define("LANINS_015", "PHP Version");
define("LANINS_016", "MySQL");
define("LANINS_017", "PASS");
define("LANINS_018", "Vergewissern Sie sich bitte, dass alle aufgelisteten Dateien existieren und auf dem Server beschreibbar sind. Dies bedeutet normalerweise, dass Sie sie mit 777 CHMOD-Rechten versehen müssen, kann aber von Server zu Server variieren - falls Sie Probleme haben, kontaktieren Sie Ihren Serverprovider.");
define("LANINS_019", "Die PHP Version auf Ihrem Server ist nicht ausreichend. e107 benötigt mindestens eine PHP Version 4.3.0. Upgraden Sie entweder Ihre PHP Version, oder kontaktieren Sie Ihren Serverprovider ob ein Upgrade serverseitig möglich ist.");
define("LANINS_020", "Installation fortsetzen");
define("LANINS_021", "3");
define("LANINS_022", "MySQL Server Details");
define("LANINS_023", "Bitte geben Sie Ihre Mysql Einstellungen an..
			  
Falls Sie Root-Rechte besitzen können Sie eine neue Datenbank erstellen lassen, indem Sie die Box markieren. Falls nicht, müssen Sie eine neue Datenbank manuell erstellen oder eine bereits existierende verwenden.

Falls Sie nur eine Datenbank zur Verfügung haben benutzen Sie bitte eine Prefix, sodass andere Scripte in der selben Datenbank installieren können.
Falls Sie Ihre Mysql-Benutzerdaten nicht kennen, kontaktieren Sie Ihren Serverprovider.");
define("LANINS_024", "MySQL Server:");
define("LANINS_025", "MySQL Benutzername:");
define("LANINS_026", "MySQL Passwort:");
define("LANINS_027", "MySQL Datenbank:");
define("LANINS_028", "Datenbank erstellen?");
define("LANINS_029", "Tabellen Prefix:");
define("LANINS_030", "Der MySQL Server den Sie für den e107 verwenden möchten. Kann auch aus einer Port-Nummer bestehen wie z.B. \"hostname:port\" oder einem Pfad zu einem local socket z.B. \":/path/to/socket\" für den localhost.");
define("LANINS_031", "Der Benutzername, um es e107 zu erlauben zur Datenbank zu connecten ");
define("LANINS_032", "Das Passwort für den Benutzer welches Sie schon eingegeben haben");
define("LANINS_033", "Die MySQL Datenbank inder e107 installiert werden soll. Falls der Benutzer, serverseitig, Erlaubnis hat Datenbanken automatisch erstellen zu lassen können Sie diese Option wählen, falls diese noch nicht angelegt ist.");
define("LANINS_034", "Die Prefix die e107 benutzt beim erstellen der Tabellen. Sinnvoll, falls Sie mehrere e107 in ein und dieselbe Datenbank installieren möchten.");
define("LANINS_035", "Fortfahren");
define("LANINS_036", "4");
define("LANINS_037", "MySQL Zugriff Vertifizierung");
define("LANINS_038", " und Datenbankerstellung");
define("LANINS_039", "Bitte gehen Sie sicher, alle Felder auszufüllen, am wichtigsten, MySQL Server, MySQL Benutzername und MySQL Datenbank (Diese werden immer benötigt für den MySQL Server)");
define("LANINS_040", "Fehler");
define("LANINS_041", "e107 konnte keine Verbindung zum Mysql Server aufbauen mit den Daten die Sie angegeben haben. Bitte gehen Sie zur vorherigen Seite und überprüfen Sie die Daten die Sie angegeben haben auf Richtigkeit.");
define("LANINS_042", "Zugriff zum MySQL server aufgebaut und vertifiziert.");
define("LANINS_043", "Es konnte keine Datenbank erstellt werden, bitte vergewissern Sie sich, nötige Rechte zu haben um auf dem Server Datenbanken erstellen zu lassen.");
define("LANINS_044", "Datenbank wurde erfolgreich erstellt.");
define("LANINS_045", "Bitte klicken Sie auf den Button, um mit dem nächsten Schritt fortzufahren.");
define("LANINS_046", "5");
define("LANINS_047", "Administrator Details");
define("LANINS_048", "Gehen Sie zurück zum vorherigen Schritt");
define("LANINS_049", "Die zwei Passwörter, die Sie eingegeben haben sind unterschiedlich. Bitte gehen Sie zurück und versuchen Sie es nochmals.");
define("LANINS_050", "XML Erweiterung");
define("LANINS_051", "Installiert");
define("LANINS_052", "Nicht installiert");
define("LANINS_053", "e107 .700 benötigt die PHP XML Erweiterung um installiert werden zu können. Bitte kontaktieren Sie Ihren Serverprovider oder lesen Sie die Information diesbezüglich auf ");
define("LANINS_054", " bevor Sie fortfahren");
define("LANINS_055", "Installations Bestätigung");
define("LANINS_056", "6");
define("LANINS_057", " e107 hat nun alle benötigten Information um installiert werden zu können.

Bitte klicken Sie den Button um die Datenbanktabellen erstellen zu lassen und Ihre Angaben abzuspeichern.

");
define("LANINS_058", "7");
define("LANINS_060", "Die sql datafile konnte nicht gelesen werden

Vergewissern Sie sich bitte das die File <b>core_sql.php</b> existiert im, <b>/e107_admin/sql</b> Verzeichnis.");
define("LANINS_061", "e107 konnte nicht alle nötigen Datenbanktabellen erstellen.
Bitte leeren Sie die Datenbank und berichtigen Sie alle Probleme die aufgetreten sind, bevor Sie fortfahren.");
define("LANINS_062", "Willkommen auf Ihrer neuen Webseite!");
define("LANINS_063", "e107 wurde erfolgreich erstellt und ist nun bereit Inhalte aufzunehmen.");
define("LANINS_064", "Ihr Administrationsbereich ist");
define("LANINS_065", "hier zu finden");
define("LANINS_066", "Bitte klicken Sie hier um dort hinzugelangen. Sie müssen sich mit denselben Benutzerdaten (Benutzername und Passwort), die Sie während des Installationsprozesses verwendet haben, einloggen.");
define("LANINS_067", "you will find the FAQ and documentation here.");
define("LANINS_068", "Danke schön, dass Sie sich für e107 entschieden haben. Wir hoffen Ihre Anforderungen werden erfüllt.\n(Diese Nachricht können Sie in Ihrem Adminbereich wieder löschen.)\n\n<b>Diese Version des e107 ist eine Beta version und sollte deshalb noch nicht auf Live Webseiten verwendet werden.</b>");
define("LANINS_069", "e107 wurde erfolgreich installiert!

Aus Sicherheitsgründen sollten Sie die Dateirechte von <b>e107_config.php</b> zurücksetzen auf 644.

Bitte löschen Sie auch die install.php und das e107_install Verzeichnis auf Ihrem Server nachdem Sie den unteren Button geklickt haben
");
define("LANINS_070", "e107 konnte die Hauptkonfigurationsdatei nicht auf Ihrem Server speichern.

Bitte vergewissern Sie sich, dass die <b>e107_config.php</b> Datei die nötigen Rechte besitzt");
define("LANINS_071", "Installation abschliessen");

?>
