<?php

define("LANINS_001", "e107 Installatie");


define("LANINS_002", "Stap ");
define("LANINS_003", "1");
define("LANINS_004", "Taalkeuze");
define("LANINS_005", "Kies de taal die je bij installatie wilt gebruiken");
define("LANINS_006", "Instellen taal");
define("LANINS_007", "2");
define("LANINS_008", "PHP &amp; MySQL Versie controle / Controle bestandspermissies");
define("LANINS_009", "Opnieuw testen permissions");
define("LANINS_010", "Bestand niet beschrijfbaar: ");
define("LANINS_011", "Fout");
define("LANINS_012", "MySQL functies lijken niet te bestaan. Dit betekent vermoedelijk dat de MySQL PHP extensies niet  zijn geïnstalleerd of dat ze niet goed zijn geconfigureerd."); // help for 012
define("LANINS_013", "Het MySQL versienummer is niet vast te stellen. Dat kan betekenen dat je MySQL server down is of dat de server de verbindingen weigert.");
define("LANINS_014", "Bestandspermissies");
define("LANINS_015", "PHP Versie");
define("LANINS_016", "MySQL");
define("LANINS_017", "GESLAAGD");
define("LANINS_018", "Zorg ervoor dat alle aangegeven bestanden bestaan en beschrijfbaar zijn door de server. Dat betekent in de regel CHMODden naar 777, maar omdat omgevingen variëren kun je het beste met je provider contact opnemen als er een probleem bestaat.");
define("LANINS_019", "De op je server geïnstalleerde versie van PHP kan e107 niet aan. e107 vereist een PHP versie van minimaal 4.3.0. Ofwel upgrade je versie van PHP of neem contact op met je provider voor een upgrade.");
define("LANINS_020", "Verder met installatie");
define("LANINS_021", "3");
define("LANINS_022", "MySQL server details");
define("LANINS_023", "Geef hier je MySQL instellingen op.
			  
Als je root permissies bezit, kun je een nieuwe database creëren door het vakje aan te kruisen. Als je dat niet hebt, dat moet je zelf de database creëren of een bestaande gebruiken.

Als je maar één database mag aanmaken, gebruik dan een prefix zodat ook andere scripts dezelfde database kunnen gebruiken.
Als je je MySQL details niet kent, neem dan contact op met je web provider.");
define("LANINS_024", "MySQL server:");
define("LANINS_025", "MySQL gebruikersnaam:");
define("LANINS_026", "MySQL wachtwoord:");
define("LANINS_027", "MySQL database:");
define("LANINS_028", "Aanmaken database?");
define("LANINS_029", "Tabel prefix:");
define("LANINS_030", "De MySQL server die je voor e107 wilt gebruiken. Het kan ook een poortnummer bevatten,  bijv. \"systeemnaam:poort\" of een pad naar een lokale socket bijv \":/pad/naar/socket\" voor de localhost.");
define("LANINS_031", "De gebruikersnaam waarmee e107 een verbinding maakt met de MySQL server");
define("LANINS_032", "Het wachtwoord voor de gebruikersnaam die je net opgaf");
define("LANINS_033", "De MySQL database waar e107 gebruik van maakt heet ook wel een schema. Als de gebruiker de rechten heeft om een database te creëren, kun je ervoor kiezen om de database automatisch te laten aanmaken als die tenminste nog niet bestaat.");
define("LANINS_034", "De prefix die je voor e107 wilt gebruiken bij het aanmaken van de tabellen. Zinvol als meerdere installaties van een applicatie (bijv. e107) gebruik moeten maken van één database schema.");
define("LANINS_035", "Verder");
define("LANINS_036", "4");
define("LANINS_037", "MySQL connectie verificatie");
define("LANINS_038", " en database creatie");
define("LANINS_039", "Let er goed op alle velden in te vullen, vooral MySQL Server, MySQL gebruikersnaam en MySQL Database (die zijn altijd nodig voor de MySQL Server)");
define("LANINS_040", "Fouten");
define("LANINS_041", "e107 kon met de informatie die je net opgaf geen verbinding maken met de MySQL server. Ga terug naar de vorige pagina en controleer de opgegeven informatie.");
define("LANINS_042", "Verbinding met de MySQL server gemaakt en geverifieerd.");

define("LANINS_043", "De database kan niet worden gecreëerd. Controleerd of je de juiste permissies hebt om databases op je server te creëren.");
define("LANINS_044", "Database succesvol aangemaakt.");
define("LANINS_045", "Druk op de knop om verder te gaan met de volgende stap.");
define("LANINS_046", "5");
define("LANINS_047", "Beheerdersinformatie");
define("LANINS_048", "Ga terug naar de laatste stap");
define("LANINS_049", "De twee wachtwoorden die je hebt opgegeven, zijn niet gelijk. Ga terug en probeer het nog een keer.");
define("LANINS_050", "XML Extensie");
define("LANINS_051", "Geïnstalleerd");
define("LANINS_052", "Niet geïnstalleerd");
define("LANINS_053", "e107 .700 vereist de PHP XML Extensies. Neem contact op met je provider of lees de informatie op ");
define("LANINS_054", " voordat je verder gaat");
define("LANINS_055", "Installatie bevestiging");
define("LANINS_056", "6");
define("LANINS_057", " e107 heeft nu alle informatie die nodig is om de installatie af te ronden.

Druk op de knop om de database tabellen aan te maken en de instellingen op te slaan.

");
define("LANINS_058", "7");
define("LANINS_060", "Het sql gegevensbestand kon niet worden gelezen.

Controleer of het bestand <b>core_sql.php</b> bestaat in de <b>/e107_admin/sql</b> directory.");
define("LANINS_061", "e107 kon niet alle vereiste databasetabellen aanmaken.
Maak de database leeg en herstel de problemen en probeer het daarna nog eens.");
define("LANINS_062", "Welkom bij je nieuwe website!");
define("LANINS_063", "e107 is succesvol geïnstalleerd en klaar voor gebruik.");
define("LANINS_064", "Je beheerscherm");
define("LANINS_065", "is hier");
define("LANINS_066", "klik om er nu naartoe te gaan. Je moet inloggen met de gebruikersnaam en het wachtwoord die je beide eerder opgaf bij installatie.");
define("LANINS_067", "je vindt de FAQ en documentatie hier.");
define("LANINS_068", "Hartelijk dank voor het installeren van e107. We hopen dat het voldoet aan al je website wensen.\n(Je kunt dit bericht verwijderen in je beheerscherm.)\n\n<b>Let op: deze versie van e107 is een beta versie en dus niet gegarandeerd voor gebruik op live websites.</b>");
define("LANINS_069", "e107 is succesvol geïnstalleerd!

Omwille van beveiliging moet je nu de bestandpermissies op het <b>e107_config.php</b> bestand terugzetten naar 644.

En verwijder ook het bestand install.php en de e107_install directory van je server nadat je op de knop hieronder hebt gedrukt
");
define("LANINS_070", "e107 kon het hoofdconfiguratiebestand niet wegschrijven op je server.

Let erop dat het <b>e107_config.php</b> bestand de juiste permissies kent");
define("LANINS_071", "Afronden installatie");
define("LANINS_072", "Gebruikersnaam beheerder");
define("LANINS_073", "Dit is de naam waarmee je moet inloggen op de site. Als je deze naam ook als weergegeven naam wilt gebruiken");
define("LANINS_074", "Weergegeven naam beheerder");
define("LANINS_075", "Dit is de naam die je gebruikers op de site weergegeven zien in je profiel, de forums en de andere onderdelen. Als je je loginnaam hiervoor wilt gebruiken, vul dan niets in.");
define("LANINS_076", "Beheerders wachtwoord");
define("LANINS_077", "Geef het beheerderswachtwoord op");
define("LANINS_078", "Bevestig het beheerderswachtwoord");
define("LANINS_079", "Voer het beheerderswachtwoord nogmaals in ter bevestiging");
define("LANINS_080", "E-mailadres beheerder");
define("LANINS_081", "Geef je e-mailadres op");
define("LANINS_082", "gebruiker@jouwsite.net");

?>