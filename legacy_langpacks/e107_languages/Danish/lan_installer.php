<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Danish/lan_installer.php,v $
|     $Revision: 1.5 $
|     $Date: 2006-11-23 00:05:11 $
|     $Author: e107dk $
+----------------------------------------------------------------------------+
*/
define("LANINS_001", "e107 Installation");


define("LANINS_002", "Trin ");
define("LANINS_003", "1");
define("LANINS_004", "Sprog Valg");
define("LANINS_005", "V&aelig;lg venligst sprog der skal bruges under installations proceduren");
define("LANINS_006", "Benyt Sprog");
define("LANINS_007", "2");
define("LANINS_008", "PHP og MySQL Versioner Kontrol / Fil Tilladelses Kontrol");
define("LANINS_009", "Gentest Fil Tilladelser");
define("LANINS_010", "Fil er skrivebeskyttet: ");
define("LANINS_010a", "Mappe er skrivebeskyttet: ");
define("LANINS_011", "Fejl");
define("LANINS_012", "MySQL Funktioner lader ikke til at eksistere. Dette betyder formentlig at enten er MySQL PHP Udvidelsen ikke installeret eller den er ikke konfigureret korrekt."); // help for 012
define("LANINS_013", "Kunne ikke bestemme dit MySQL versions nummer. Dette er ikke en fatal fejl, s&aring; forts&aelig;t vblot med at installere, men v&aelig;r opm&aelig;rksom p&aring; at e107 kr&aelig;ver MySQL >= 3.23. for at fungere korrekt");
define("LANINS_014", "Fil Tilladelser");
define("LANINS_015", "PHP Version");
define("LANINS_016", "MySQL");
define("LANINS_017", "OK");
define("LANINS_018", "Sikrer dig at alle de viste filer eksisterer og ikke er skrivebeskyttede for serveren. Dette indeb&aelig;rer normalt CHMODe dem til 777, men milj&oslash;er varierer - kontakt din host hvis du har problemer.");
define("LANINS_019", "Den version PHP der er installeret p&aring; din server er ikke i stand til at k&oslash;re e107. e107 kr&aelig;ver en PHP version p&aring; mindst 4.3.0 for at k&oslash;re korrekt. Opgrader enten din PHP version, eller kontakt din host for en opgradering.");
define("LANINS_020", "Forts&aelig;t Installation");
define("LANINS_021", "2");
define("LANINS_022", "MySQL Server Detaljer");
define("LANINS_023", "Skriv venligst dine MySQL indstillinger her.
			  
Hvis du har rod tilladelser kan du oprette en ny database ved at markere boksen, hvis ikke skal du oprette en database eller bruge en eksisterende.

Hvis du kun har en database brug et prefix s&aring; andre scripts kan dele den samme database.
Hvis du ikke kender dine MySQL detaljer kontakt din web host.");
define("LANINS_024", "MySQL Server:");
define("LANINS_025", "MySQL Brugernavn:");
define("LANINS_026", "MySQL Kodeord:");
define("LANINS_027", "MySQL Database:");
define("LANINS_028", "Opret Database?");
define("LANINS_029", "Tabel prefix:");
define("LANINS_030", "Den MySQL server du &oslash;nsker e107 skal bruge. Den kan ogs&aring; indeholde et port nummer. eks. \"hostnavn:port\" eller en sti til en lokal sokkel eks. \":/sti/til/sokkel\" for localhost.");
define("LANINS_031", "Brugernavnet du &oslash;nsker e107 skal bruge til at forbinde til din MySQL server");
define("LANINS_032", "Kodeordet for den bruger du lige skrev");
define("LANINS_033", "Den MySQL database du &oslash;nsker e107 skal befinde sig i, nogle gange refereret til som skematisk. Hvis brugeren har database oprettelses tilladelser kan du s&aelig;tte den til at oprette databasen automatisk hvis den ikke allerede eksisterer.");
define("LANINS_034", "Det prefix du &oslash;nsker e107 skal bruge til oprettelse af e107 tabellerne. Nyttigt for flere installationer af e107 i en database.");
define("LANINS_035", "Forts&aelig;t");
define("LANINS_036", "4");
define("LANINS_037", "MySQL Forbindelses Bekr&aelig;ftigelse");
define("LANINS_038", " og Database Oprettelse");
define("LANINS_039", "Sikrer dig venligst at du udfylder alle felter, vigtigst, MySQL Server, MySQL Brugernavn og MySQL Database (Disse er altid kr&aelig;vet af MySQL Serveren)");
define("LANINS_040", "Fejl");
define("LANINS_041", "e107 var ikke i stand til at etablere en forbindelse til MySQL serveren med de informationer du oplyste. G&aring; venligst tilbage til forrige side og kontroller at informationerne er korrekte.");
define("LANINS_042", "Forbindelse til MySQL serveren etableret og verificeret.");
define("LANINS_043", "Kunne ikke oprette database, kontroller venligst at du har de korrekte tilladelser til at oprette databaser p&aring; din server.");
define("LANINS_044", "Oprettede database med succes.");
define("LANINS_045", "Klik venligst p&aring; knappen for at forts&aelig;tte til n&aelig;ste trin.");
define("LANINS_046", "5");
define("LANINS_047", "Administrator Detaljer");
define("LANINS_048", "G&aring; Tilbage Til Forrige Trin");
define("LANINS_049", "De to kodeord du skrev er ikke ens. G&aring; venligst tilbage og pr&oslash;v igen.");
define("LANINS_050", "XML Udvidelse");
define("LANINS_051", "Installeret");
define("LANINS_052", "Ikke Installeret");
define("LANINS_053", "e107 .700 kr&aelig;ver at PHP XML Udvidelsen er installeret. Kontakt venligst din host eller l&aelig;s informationerne p&aring; ");
define("LANINS_054", " f&oslash;r du forts&aelig;tter");
define("LANINS_055", "Installations Bekr&aelig;ftelse");
define("LANINS_056", "6");
define("LANINS_057", " e107 har nu alle de oplysninger det kr&aelig;ver for at forts&aelig;tte installationen.

Klik venligst p&aring; knappen for at oprette database tabelller og gemme alle dine indstillinger.

");
define("LANINS_058", "7");
define("LANINS_060", "Kan ikke l&aelig;se sql datafilen

Sikrer dig venligst at filen <b>core_sql.php</b> eksisterer i <b>/e107_admin/sql</b> mappen.");
define("LANINS_061", "e107 kunne ikke oprette alle de kr&aelig;vede database tabeller.
Ryd venligst databasen og ret eventuelle problemer f&oslash;r du pr&oslash;ver igen.");

define("LANINS_062", "[b]Velkommen til din nye hjemmeside![/b]
e107 blev installeret med succes og er nu klar til at modtage indhold.<br />Dit administrationt omr&aring;de er [link=e107_admin/admin.php]beliggende her[/link], klik for at g&aring; det til nu. Du skal logge ind ved hj&aelig;lp af det brugernavn og kodeord du skrev under installations processen.

[b]Support[/b]
e107 Hjemmeside: [link=http://e107.org]http://e107.org[/link], du kan finde FAQ og dokumentation her.
Forums: [link=http://e107.org/e107_plugins/forum/forum.php]http://e107.org/e107_plugins/forum/forum.php[/link]

[b]Downloads[/b]
Plugins: [link=http://e107coders.org]http://e107coders.org[/link]
Temaer: [link=http://e107styles.org]http://e107styles.org[/link] | [link=http://e107themes.org]http://e107themes.org[/link]

Tak fordi du pr&oslash;ver e107, vi h&aring;ber det opfylder dine hjemmeside behov.
(Du kan slette denne besked fra din admin sektion.)");

define("LANINS_063", "Velkommen til e107");

define("LANINS_069", "e107 er blevet installeret med succes!

Af sikkerheds messige &aring;rsager b&oslash;r du nu indstille fil rettighederne p&aring; <b>e107_config.php</b> filen tilbage til 644.

Slet ogs&aring; install.php og e107_install mappen fra din server efter du har klikket p&aring; knappen herunder
");
define("LANINS_070", "e107 var ikke i stand til at gemme hoved konfigurations filen til din server.

Kontroller venligst at <b>e107_config.php</b> filen har de rette tilladelser");
define("LANINS_071", "Afslutter Installation");

define("LANINS_072", "Admin Brugernavn");
define("LANINS_073", "Dette er navnet du bruger til at logge ind p&aring; sitet. Hvis du ogs&aring; &oslash;nsker at bruge dette som dit fremviste navn");
define("LANINS_074", "Admin Fremvist Navn");
define("LANINS_075", "Det er det navn du &oslash;nsker dine brugere skal se i din profil, forums og andre omr&aring;der. Hvis du &oslash;nsker at bruge det samme som du logger ind med lad dette felt st&aring; tomt.");
define("LANINS_076", "Admin Kodeord");
define("LANINS_077", "Skriv venligst det admin kodeord du &oslash;nsker at bruge her");
define("LANINS_078", "Admin Kodeord Bekr&aelig;ftelse");
define("LANINS_079", "Skriv venligst admin kodeordet igen for bekr&aelig;ftelse");
define("LANINS_080", "Admin Email");
define("LANINS_081", "Skriv din email adresse");

define("LANINS_082", "bruger@ditsite.dk");

// Better table creation error reporting
define("LANINS_083", "MySQL Rapoterede Fejl:");
define("LANINS_084", "Installationen kunne ikke etablere en forbindelse til databasen");
define("LANINS_085", "Installationen kunne ikke v&aelig;lge database:");

define("LANINS_086", "Admin Brugernavn, Admin Kodeord og Admin Email er <b>kr&aelig;vede</b> felter. G&aring; venligst tilbage til forrige side og sikker dig at informationerne er korrekt udfyldt.");
?>