<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Swedish/lan_installer.php,v $
|     $Revision: 1.7 $
|     $Date: 2007-01-18 06:38:27 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/
define("LANINS_001", "e107 Installation");
define("LANINS_002", "Steg ");
define("LANINS_003", "1");
define("LANINS_004", "Språkval");
define("LANINS_005", "Välj språk att använda under installationsprocessen");
define("LANINS_006", "Sätt språk");
define("LANINS_007", "4");
define("LANINS_008", "PHP & MySQL versionskontroll / Kontroll av filrättigheter");
define("LANINS_009", "Testa filrättigheterna igen");
define("LANINS_010", "Kan inte skriva till fil: ");
define("LANINS_010a", "Katalog är skrivskyddad: ");
define("LANINS_011", "Fel");
define("LANINS_012", "MySQL funktioner verkar inte finnas. Detta betyder antagligen antingen på att MySQL PHP modulen inte är installerad eller inte är korrekt konfigurerad.");
define("LANINS_013", "Kunde inte utröna din MySQL version. Detta är inte ett blockerande fel, så fortsätt att installera, men var medveten om att e107 kräver MySQL >= 3.23 för att fungera korrekt.");
define("LANINS_014", "Filrättigheter");
define("LANINS_015", "PHP version");
define("LANINS_016", "MySQL");
define("LANINS_017", "KLART");
define("LANINS_018", "Försäkra dig om att alla listade filer existerar och att de kan skrivas till av servern. Detta innebär vanligen att sätta dem till CHMOD 777, men miljöer kan se olika ut - kontakta din webbvärd om du får problem.");
define("LANINS_019", "PHP versionen installerad på din server kan inte köra e107. e107 kräver en PHP version på lägst 4.3.0 för att köras korrekt. Antingen uppgradera din egen PHP, eller kontakta din webbvärd för en uppgradering.");
define("LANINS_020", "Fortsätt installation");
define("LANINS_021", "2");
define("LANINS_022", "MySQL server detaljer");
define("LANINS_023", "Ange dina MySQL uppgifter här.<br /><br />Om du har root-åtkomst kan du skapa en ny databas genom att klicka i rutan 'Skapa databas', om inte måste du skapa en databas manuellt eller använda en befintlig.<br /><br />Om du bara har en databas, använd ett prefix så att andra skript kan dela på samma databas.<br />Om du inte känner till dina MySQL uppgifter, kontakta din webbvärd.");
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
define("LANINS_036", "3");
define("LANINS_037", "Verifiering av MySQL anslutning");
define("LANINS_038", " och skapande av databas");
define("LANINS_039", "Se till att du fyller i alla fält, viktigast är MySQL Server, MySQL användarnamn och MySQL databas (Dessa krävs alltid av MySQL servern)");
define("LANINS_040", "Fel");
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
define("LANINS_057", "e107 har nu all information som behövs för att avsluta installationen.<br /><br />Klicka på knappen för att skapa databastabellerna och spara alla dina inställningar.");
define("LANINS_058", "7");
define("LANINS_060", "Kan inte läsa SQL datafilen.<br /><br />Försäkra dig om att filen <b>core_sql.php</b> existerar i <b>/e107_admin/sql</b> katalogen.");
define("LANINS_061", "e107 kunde inte skapa alla nödvändiga databastabeller.<br />Vänligen rensa databasen och korrigera eventuella problem innan du provar igen.");
define("LANINS_062", "[b]Välkommen till din nya webbsajt![/b]<br />
e107 har installerats korrekt och är nu redo att ta emot innehåll.<br />Din administrationssektion är [link=e107_admin/admin.php]placerad här[/link], klicka för att gå dit nu. Du kommer att behöva logga in med det namn och lösenord du angav under installationsprocessen.[b]Support[/b] e107 hemsida: [link=http://e107.org]http://e107.org[/link], du kommer att hitta FAQ och dokumentation här. Forum: [link=http://e107.org/e107_plugins/forum/forum.php]
http://e107.org/e107_plugins/forum/forum.php[/link]
[b]Nerladdningar[/b] Plugins: [link=http://e107coders.org]http://e107coders.org
[/link]Teman: [link=http://e107styles.org]http://e107styles.org[/link] | [link=http://e107themes.org]http://e107themes.org
[/link] Tack för att du provar e107, vi hoppas att den uppfyller dina sajtbehov. (Du kan ta bort detta meddelande från din adminsektion.)");
define("LANINS_063", "Välkommen till e107");
define("LANINS_069", "e107 har installerats korrekt!<br /><br />Av säkerhetsskäl skall du nu sätta filrättigheterna på <b>e107_config.php</b> filen tillbaka till 644.<br /><br />Radera också install.php och e107_install katalogen från din server efter att du klickat på knappen nedan.");
define("LANINS_070", "e107 kunde inte spara huvudkonfigurationsfilen på din server.<br /><br />Försäkra dig om att filen <b>e107_config.php</b> har korrekta rättigheter");
define("LANINS_071", "Avslutar installation");
define("LANINS_072", "Admin användarnamn");
define("LANINS_073", "Detta är namnet du använder för att logga in på sajten. Du kan även använda detta som ditt visade namn.");
define("LANINS_074", "Visat Admin namn");
define("LANINS_075", "Detta är namnet som du vill att användarna skall se i din profil, i forum och i andra areor. Om du vill visa samma namn som inloggningsnamnet, lämna tomt här.");
define("LANINS_076", "Admin lösenord");
define("LANINS_077", "Ange det lösenord du vill ha för admin.");
define("LANINS_078", "Admin lösenordsbekräftelse");
define("LANINS_079", "Ange admins lösenord igen för verifiering.");
define("LANINS_080", "Admin e-post");
define("LANINS_081", "Ange din e-postadress här.");
define("LANINS_082", "namn@dinsajt.se");
define("LANINS_083", "MySQL rapporterade följande fel:");
define("LANINS_084", "Installationsprogrammet kunde inte etablera någon förbindelse med databasen");
define("LANINS_085", "Installationsprogrammet kunde inte välja databas:");
define("LANINS_086", "Användarnamn, lösenord och e-postadress för admin är fält som <b>måste</b> ifyllas. Vänligen backa till föregående sida och kontrollera att angiven information är korrekt.");


?>