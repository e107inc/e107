<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Swedish/lan_installer.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-06-26 11:12:22 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/
define("LANINS_001", "e107 Installation");


define("LANINS_002", "Steg ");
define("LANINS_003", "1");
define("LANINS_004", "Språkval");
define("LANINS_005", "Välj språk att använda under installationsprocessen");
define("LANINS_006", "Sätt språk");
define("LANINS_007", "2");
define("LANINS_008", "PHP &amp; MySQL versionskontroll / Kontroll av filrättigheter");
define("LANINS_009", "Testa filrättigheterna igen");
define("LANINS_010", "Kan inte skriva till fil: ");
define("LANINS_011", "Fel");
define("LANINS_012", "MySQL funktioner verkar inte finnas. Detta betyder antagligen antingen på att MySQL PHP modulen inte är installerad eller inte är korrekt konfigurerad."); // help for 012
define("LANINS_013", "Kunde inte utröna MySQL versionsnummer. Detta kan bero på att din MySQL server inte är igång, eller att den inte accepterar anslutningar.");
define("LANINS_014", "Filrättigheter");
define("LANINS_015", "PHP version");
define("LANINS_016", "MySQL");
define("LANINS_017", "KLART");
define("LANINS_018", "Försäkra dig om att alla listade filer existerar och att de kan skrivas till av servern. Detta innebär vanligen att sätta dem till CHMOD 777, men miljöer kan se olika ut - kontakta din webbvärd om du får problem.");
define("LANINS_019", "PHP versionen installerad på din server kan inte köra e107. e107 kräver en PHP version på lägst 4.3.0 för att köras korrekt. Antingen uppgradera din egen PHP, eller kontakta din webbvärd för en uppgradering.");
define("LANINS_020", "Fortsätt installation");
define("LANINS_021", "3");
define("LANINS_022", "MySQL server detaljer");
define("LANINS_023", "Ange dina MySQL uppgifter här.

Om du har root-åtkomst kan du skapa en ny databas genom att klicka i rutan, om inte måste du skapa en databas manuellt eller använda en befintlig.

Om du bara har en databas, använd ett prefix så att andra skript kan dela på samma databas.
Om du inte känner till dina MySQL uppgifter, kontakta din webbvärd.");
define("LANINS_024", "MySQL Server:");
define("LANINS_025", "MySQL användarnamn:");
define("LANINS_026", "MySQL lösenord:");
define("LANINS_027", "MySQL databas:");
define("LANINS_028", "Skapa databas?");
define("LANINS_029", "Tabellprefix:");
define("LANINS_030", "MySQL servern du vill att e107 skall använda. Kan också innehålla portnummer, t.ex. \"värdnamn:port\" eller sökvägen till en lokal socket t.ex. \":/sökväg/till/socket\" för den lokala värden.");
define("LANINS_031", "Användarnamnet e107 skall använda för att ansluta till din MySQL server");
define("LANINS_032", "Lösenordet för användaren du just angav");
define("LANINS_033", "MySQL databasen du vill att e107 skall befinna sig i, i vissa fall kallat schema. Om användaren har rättigheter att skapa en databas kan du begära att en ny databas skapas om den inte redan finns.");
define("LANINS_034", "Prefixet du vill att e107 skall använda när tabellerna skall skapas. Användbart för att köra flera olika e107 eller andra applikationer i samma databas/schema.");
define("LANINS_035", "Fortsätt");
define("LANINS_036", "4");
define("LANINS_037", "MySQL verifiering av anslutning");
define("LANINS_038", " och databas skapande");
define("LANINS_039", "Se till att du fyller i alla fält, viktigast är MySQL Server, MySQL användarnamn och MySQL databas (Dessa krävs alltid av MySQL servern)");
define("LANINS_040", "Fel"); // Pluralis
define("LANINS_041", "e107 kunde inte ansluta till MySQL med den information du angett. Återgå till förra sidan och kontrollera att informationen är korrekt.");
define("LANINS_042", "Anslutning till MySQL servern upprättad och verifierad.");
define("LANINS_043", "Kunde inte skapa en databas, kontrollera att du har tillräcklig behörighet för att skapa nya databaser på din server.");
define("LANINS_044", "Skapade databas felfritt.");
define("LANINS_045", "Klicka på knappen för att gå vidare till nästa steg.");
define("LANINS_046", "5");
define("LANINS_047", "Administratörsinformation");
define("LANINS_048", "Gå tillbaka till förra steget");
define("LANINS_049", "De båda lösenorden var inte lika, ange igen.");
define("LANINS_050", "XML modul");
define("LANINS_051", "Installerad");
define("LANINS_052", "Inte Installerad");
define("LANINS_053", "e107 .700 kräver att PHP XML modulen är installerad. Kontakta din webbvärd eller lär in informationen på ");
define("LANINS_054", " innan du fortsätter");
define("LANINS_055", "Installationsbekräftelse");
define("LANINS_056", "6");
define("LANINS_057", " e107 har nu all information som behövs för att avsluta installationen.

Klicka på knappen för att skapa databastabellerna och spara alla dina inställningar.

");
define("LANINS_058", "7");
define("LANINS_060", "Kan inte läsa SQL datafilen.

Försäkra dig om att filen <b>core_sql.php</b> existerar i <b>/e107_admin/sql</b> katalogen.");
define("LANINS_061", "e107 kunde inte skapa alla nödvändiga databastebeller.
Vänligen rensa databasen och korrigera eventuella problem innan du provar igen.");
define("LANINS_062", "Välkommen till din nya webbsajt!");
define("LANINS_063", "e107 är korrekt installerad och är nu redo att ta emot innehåll.");
define("LANINS_064", "Din administrationssektion ");
define("LANINS_065", "finns här");
define("LANINS_066", "klicka för att gå dit nu. Du kommer att få logga in med det namn och lösenord du angav under installationsprocessen.");
define("LANINS_067", "du kommer att hitta FAQ och dokumentation här.");
define("LANINS_068", "Tack för att du provar e107, vi hoppas att den uppfyller dina krav.\n(Du kan radera detta meddelande från din adminsektion.)\n\n<b>Observera att denna version av e107 är en beta version och inte avsedd att användas i en produktionsmiljö.</b>");
define("LANINS_069", "e107 har installerats korrekt!

Av säkerhetsskäl skall du nu sätta filrättigheterna på <b>e107_config.php</b> filen tillbaka till 644.

Radera också install.php och e107_install katalogen från din server efter att du klickat på knappen nedan.
");
define("LANINS_070", "e107 kunde inte spara huvudkonfigurationsfilen på din server.

Försäkra dig om att filen <b>e107_config.php</b> har korrekta rättigheter");
define("LANINS_071", "Avslutar installation");

?>
