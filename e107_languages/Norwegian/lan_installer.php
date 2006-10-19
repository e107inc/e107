<?php

define("LANINS_001", "e107 Installasjon");


define("LANINS_002", "Steg ");
define("LANINS_003", "1");
define("LANINS_004", "Språkvalg");
define("LANINS_005", "Vennligst velg hvilket språk du vil bruke under installasjonen.");
define("LANINS_006", "Sett språk");
define("LANINS_007", "4");
define("LANINS_008", "PHP og MySQL Versjonskontroll / Filtillatelse kontroll");
define("LANINS_009", "Test filtillatelser på nytt");
define("LANINS_010", "Kan ikke skrive til filen: ");
define("LANINS_010a", "Katalgoen er ikke skrivbar: ");
define("LANINS_011", "Feil");
define("LANINS_012", "MySQL funskjoner ser ikke ut til å eksistere. Dette skyldes enten at MYSQL PHP utvidelsen ikker er installer eller at PHP-installasjonen ikke ble kompilert med MySQL støtte."); // help for 012
define("LANINS_013", "Kunne ikke avgjøre MySQL versjonsnummer. Dette er ikke en fatal feilmelding, så vennligst fortsett installasjonen, men vær oppmerksom på at e107 krever MySQL >= 3.23 for å fungere korrekt.");
define("LANINS_014", "Fil tillatelser");
define("LANINS_015", "PHP Versjon");
define("LANINS_016", "MySQL");
define("LANINS_017", "Godkjent");
define("LANINS_018", "Vær sikker på at alle de listede filene eksisterer og er skrivbare for serveren. Dette betyr at de bør være CHMODet til 777. Ved problemer kontakt din webhost.");
define("LANINS_019", "Versjonen av PHP som er installert på serveren kan ikke støtte kjøring av e107. e107 krever PHP versjon 4.3.0 eller høyere for å kjøre korrekt. Enten oppgrader din PHP-versjon eller kontakt din webhost.");
define("LANINS_020", "Fortsett installasjon");
define("LANINS_021", "2");
define("LANINS_022", "MySQL serverdetaljer");
define("LANINS_023", "Vennlgis skriv inn dine MySQL instillinger her.

Dersom du har root-tilgang kan du lage en ny databse ved å merke av boksen, dersom ikke må du lage en database eller bruke en eksisterende en.

Dersom du kun har en databse bør du bruke et tabell prefix slik at andre skript kan bruke den samme databasen.
Dersom du ikke kjenner til dine MySQL instillinger må du kontakte din webhost.");
define("LANINS_024", "MySQL- server:");
define("LANINS_025", "MySQL- brukernavn:");
define("LANINS_026", "MySQL- passord:");
define("LANINS_027", "MySQL- databasenavn:");
define("LANINS_028", "Lag database?");
define("LANINS_029", "Tabell prefix:");
define("LANINS_030", "MySQL-serveren som du ønsker at e107 skal bruke. Du kan også inkludere et portnummer: \"vert:port\" eller en sti til en lokal socket: \":/sti/til/socket\" for lokalverten.");
define("LANINS_031", "Brukernavnet som du vil at e107 skal bruke for å koble til din MySQL-server");
define("LANINS_032", "Passordet for brukerkontoen du nettopp skrev inn");
define("LANINS_033", "MySQL- databasen som du ønsker at e107 skal legges inn i, noen ganger henvist til som schema. Dersom brukeren har database opprettingstilgang kan du opprette databasen automatisk, dersom den ikke eksisterer.");
define("LANINS_034", "Prefikset som du ønsker at e107 skal bruke når tabellene opprettes. Nyttig dersom du skal ha flere e107 installasjoner i samme databaseschema.");
define("LANINS_035", "Fortsett");
define("LANINS_036", "3");
define("LANINS_037", "MySQL-tilkoblings verifisering");
define("LANINS_038", " og database oppretting");
define("LANINS_039", "Vennligst vær sikker på at du fyller ut alle felt, spesiellt, MySQL-server, MySQL-brukernavn og MySQL- database (Disse er alltid påkrevd av MySQL-serveren)");
define("LANINS_040", "Feil");
define("LANINS_041", "e107 klarte ikke å etablere kontakt med MySQL serveren med den informasjonen du tastet inn. Gå tilbake til forrige side og sjekk at informasjonen er korrekt.");
define("LANINS_042", "Tilkobling til MySql server er etabler og verifisert.");
define("LANINS_043", "Klarte ikke å opprette databasen, kontroller at du har tilganger til å opprette databaser på din server.");
define("LANINS_044", "Suksessfullt opprettet database.");
define("LANINS_045", "Klikk på knappen for å fortsette til neste steg.");
define("LANINS_046", "5");
define("LANINS_047", "Administratordetaljer");
define("LANINS_048", "Gå tilbake til forrige steg");
define("LANINS_049", "De to passordene som er skrevet inn er ikke samsvarende. Gå tilbake og prøv på nytt.");
define("LANINS_050", "XML-utvidelse");
define("LANINS_051", "Installert");
define("LANINS_052", "Ikke installert");
define("LANINS_053", "e107 .750 krever at PHP XML utvidelse blir installert. Kontakt din webhost eller les ");
define("LANINS_054", " før du fortsetter");
define("LANINS_055", "Installasjons bekreftelse");
define("LANINS_056", "6");
define("LANINS_057", " e107 har nå all nødvendig informasjon for å fullføre installasjonen.

Vennlgist klikk på knappen for å lage databasetabellene og lagre dine instillinger.

");
define("LANINS_058", "7");
define("LANINS_060", "Ikke i stand til å lese SQL-datafil

Sjekk at filen <b>core_sql.php</b> eksisterer i <b>/e107_admin/sql</b> katalogen.");
define("LANINS_061", "e107 klarte ikke å opprette alle database tabellene.
Tøm databasen og løs evt. problemer før du prøver på nytt.");

define("LANINS_062", "[b]Velkommen til din nye webside![/b]
e107 er installert og er klar til å brukes.<br />Ditt administrasjonsgrensesnitt [link=e107_admin/admin.php]finner du her[/link], klikk for å gå dit nå. Du må logge inn med navn og passord som du skrev inn under installasjonen.

[b]Støtte[/b]
e107 hjemmeside: [link=http://e107.org]http://e107.org[/link], du vil finne FAQ og dokumentasjon her (på engelsk).
Forum: [link=http://e107.org/e107_plugins/forum/forum.php]http://e107.org/e107_plugins/forum/forum.php[/link]

[b]Nedlastinger[/b]
Plugins: [link=http://e107coders.org]http://e107coders.org[/link]
Themes: [link=http://e107styles.org]http://e107styles.org[/link] | [link=http://e107themes.org]http://e107themes.org[/link]

Du kan slette denne meldingen når du går inn på adminområdet.");

define("LANINS_063", "Welkommen til e107");

define("LANINS_069", "e107 er installert!

For sikkerhetsgrunner må du nå sette filtilgangene til <b>e107_config.php</b> tilbake til 644.

Slett også og e107_install katalogen på server etter at du har klikket på knappen nedenfor.
");
define("LANINS_070", "e107 klarte ikke å lagre hovedkonfigurasjonsfilen på server.

Sjekk at <b>e107_config.php</b> filen har riktige tilganger (chmod 777)");
define("LANINS_071", "Fullfører installasjonen");

define("LANINS_072", "Adminbrukernavn");
define("LANINS_073", "Dette er navnet som du vil logge inn med på webområdet. Dersom du ønsker å bruke dette som ditt visningsnavn også");
define("LANINS_074", "Admin visningsnavn");
define("LANINS_075", "Dette er navnet som dine brukere vil se i din profil, forum og på andre deler av websidene dine. Dersom du ønsker å benytte loginnavnet ditt så la dette feltet være blankt.");
define("LANINS_076", "Adminpassord");
define("LANINS_077", "Skriv inn adminpassordet du ønsker å bruke her");
define("LANINS_078", "Bekreft adminpassordet");
define("LANINS_079", "Skriv inn passordet på nytt for å bekrefte det.");
define("LANINS_080", "Admin-email");
define("LANINS_081", "Skriv inn din e-mail adresse");

define("LANINS_082", "bruker@dinside.com");

// Better table creation error reporting
define("LANINS_083", "MySQL rapportert feil:");
define("LANINS_084", "Installasjonsprogrammet kunne ikke etablere tilkobling til databasen");
define("LANINS_085", "Installasjonsprogrammet kunne ikke velge databasen:");

?>