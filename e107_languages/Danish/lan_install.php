<?php

/*
Danish Translation File
Author: Jan Ole laugesen
Email: e107@tommerup.net
*/

define("INSLAN1", "Installations Fase");
define("INSLAN2", "PHP / mySQL versions kontrol / fil tilladelses tjek");
define("INSLAN3", "PHP Version");
define("INSLAN4", "Fejl");
define("INSLAN5", "<b>Du køre en version af PHP som ikke er kompatibel med e107<br />(e107 kræver mindst version 4.1.0).</b><br /><br />Hvis du bruger en lokal server på din egen computer, bliver du nød til at at opgradere din<br />version of PHP for at fortsætte, Se venligst <a href='http://php.net'>php.net</a> for yderligere instruktioner. Hvis du<br />forsøger at installerer e107 på en hosted server skal du kontakte din udbyder<br />og bede dem om en opgradering af PHP for dig.<br />Først herefter kan du vende tilbage til denne installation, efter en opgradering af din PHP version.");
define("INSLAN6", "Script er stoppet.");
define("INSLAN7", "Godkendt");
define("INSLAN8", "mySQL Version");
define("INSLAN9", "<b>e107 er ikke i stand til at fastslå hvilket mySQL version nummer du bruger.</b><br /><br /> Dette kan betyde at mySQL ikke er installeret, er oppe, eller <br />at der bruges en version som ikke tilbagegiver en korrekt information<br />nummer (v5.x er kendt for at have dette problem). Hvis det næste trin i denne installation<br />fejler, skal du tjekke din mySQL status.");
define("INSLAN10", "Fil tilladelse");
define("INSLAN11", "er skrivebeskyttet");
define("INSLAN12", "mappen er skrivebeskyttet");
define("INSLAN13", "Vær sikker på at rettighederne, er sat korrekt på de filer som er vist herover.<br />Tilladelser, kaldet chmod, bør sættes til 777. For at sætte de korrekte<br />betingelser, højreklik på filen i dit FTP program og vælge Chmod eller<br />Sæt skriverettigheder, derpå skriv 777, hvis der kommer en dialog med bokse<br />da afmærk alle bokse.<br /><br />vær venlig igen at teste efter at indstillinger er ændret.");
define("INSLAN14", "e107 installering");
define("INSLAN15", "ALVORLIG FEJL");
define("INSLAN16", "I hele processen var det ikke muligt at fastslå status på din mySQL installation,<br />Fortsæt venligst til næste trin.");
define("INSLAN17", "Fortsæt");
define("INSLAN18", "Test fil tilladelserne igen");
define("INSLAN19", "Alle server test er gennemført uden problemer, Klik på knappen for at fortsætte til næste trin");
define("INSLAN20", "mySQL information");
define("INSLAN21", "Angiv dine mySQL indstillinger her.<br />Hvis du har rettigheder til at lave ny database, kan du gøre dette ved at afmærke boksen, hvis ikke skal du<br />oprette en database eller anvende en pre-instaleret database. <br />Hvis du kun har en database, brug da et foranstillet unikt navn, således at andre scripts kan bruge samme database.<br />Hvis du ikke kender dine mySQL oplysninger kontakt da din web udbyder.");
define("INSLAN22", "mySQL Server");
define("INSLAN23", "mySQL Brugernavn");
define("INSLAN24", "mySQL Password");
define("INSLAN25", "mySQL Database");
define("INSLAN26", "Tabel unikt navn");
define("INSLAN27", "Fejl");
define("INSLAN28", "Fejl opstod tilfældigt");
define("INSLAN29", "Du efterlod ønskede felter blanke, udfyld venligst den manglende mySQL information");
define("INSLAN30", "e107 var ikke i stand til at etablerer kontakt til mySQL, ved brug af de informationer du opgav.<br />Gå venligst tilbage til foregående side og kontrollerer at alle oplysninger er korrekt angivet.");
define("INSLAN31", "mySQL kontrol");
define("INSLAN32", "Forbindelse til mySQL er oprettet og godkendt.");
define("INSLAN33", "Forsøger at oprette database");
define("INSLAN34", "Umuligt at oprette database, kontrollerer venligst at du har ret til at oprette database.");
define("INSLAN35", "Databasen er oprettet.");
define("INSLAN36", "Klik på knappen for at fortsætte til næste trin.");


define("INSLAN37", "Tilbage til foregående side");
define("INSLAN38", "Administrator information");
define("INSLAN39", "Angiv din hoved administrator brugernavn, password og e-mail adresse.<br />disse oplysninger vil blive brugt til at opnå adgang til administration af systemet.<br />Husk at gemme oplysninger på et sikkert sted, går disse tabt<br />kan de IKKE hentes igen.");
define("INSLAN40", "Admin Navn");
define("INSLAN41", "Admin Password");
define("INSLAN42", "Bekræft Password");
define("INSLAN43", "Admin E-mail Adresse");
define("INSLAN44", "Du efterlod et ønsket felt blankt, udfyld alle admin informationer.");
define("INSLAN45", "De to passwords er ikke ens, prøv igen.");
define("INSLAN46", "det alder ikke til at være en gyldig e-mail adresse, prøv igen.");
define("INSLAN47", "Alt er klar!");
define("INSLAN48", "e107 har nu opsamlet alle informationer som er krævet for at færdiggøre installationen.<br />Klik på knappen for at oprette database tabeller og indsætte informationerne.");
define("INSLAN49", "e107 Vr ude af stand til at gemme hoved konfigurations filen på din server <br />Kontrollerer at <b>e107_config.php</b> filen har de korrekte tilladelser ");
define("INSLAN50", "Installation Udført!");
define("INSLAN51", "Alt er udført");
define("INSLAN52", "e107 er blevet oprettet og installeret!<br />Af sikkerheds hensyn bør du nu sætte fil rettigheder på<br /><b>e107_config.php</b> tilbage til 644.<br />Samt slette  /install.php fra din server, efter du har klikket på knappen herunder");
define("INSLAN53", "Klik her for at gå til din nye website!");
define("INSLAN54", "Ej muligt at læse sql datafilen<br /><br />Vær sikker på at  <b>core_sql.php</b> er tilstede i <b>/e107_admin/sql</b> mappen.");
define("INSLAN55", "e107 var ikke I stand til at oprette alle de nødvendige database tabeller.<br />Ryd op i databasen og ret alle problemer før du prøver igen.");
define("INSLAN56", "Velkommen til din nye webside!");

define("INSLAN57", "e107 er installeret og er nu klar til at blive brugt.<br />Din administration af siden er <a href='e107_admin/admin.php'>placeret her</a>, klik for at gå til siden nu. Du skal bruge navn og password som du angav under installationsprocessen.");
define("INSLAN58", "du finder FAQ og dokumentation her.");
define("INSLAN59", "Tak fordi du bruger e107, vi håber det vil tilfredsstille dine website ønsker og krav.\n(Du kan slette denne besked fra din administrationssektion.)");
define("INSLAN60", "Afmærk for at oprette");
define("INSLAN61", "mappe");
define("INSLAN62", "eller");
define("INSLAN63", "Fil tilladelses fejl");
define("INSLAN64", "Denne fil er oprettet af installationssystemet.");

?>