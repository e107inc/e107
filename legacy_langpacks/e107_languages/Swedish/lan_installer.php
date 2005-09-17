<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Swedish/lan_installer.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-09-17 14:44:06 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/
define("LANINS_001", "e107 Installation");

define("LANINS_002", "Steg ");
define("LANINS_003", "1");
define("LANINS_004", "Spr&aring;kval");
define("LANINS_005", "V&auml;lj spr&aring;k att anv&auml;nda under installationsprocessen");
define("LANINS_006", "S&auml;tt spr&aring;k");
define("LANINS_007", "2");
define("LANINS_008", "PHP & MySQL versionskontroll / Kontroll av filr&auml;ttigheter");
define("LANINS_009", "Testa filr&auml;ttigheterna igen");
define("LANINS_010", "Kan inte skriva till fil: ");
define("LANINS_010a", "Katalog &auml;r skrivskyddad: ");
define("LANINS_011", "Fel");
define("LANINS_012", "MySQL funktioner verkar inte finnas. Detta betyder antagligen antingen p&aring; att MySQL PHP modulen inte &auml;r installerad eller inte &auml;r korrekt konfigurerad."); // help for 012
define("LANINS_013", "Kunde inte utr&ouml;na MySQL versionsnummer. Detta kan bero p&aring; att din MySQL server inte &auml;r ig&aring;ng, eller att den inte accepterar anslutningar.");
define("LANINS_014", "Filr&auml;ttigheter");
define("LANINS_015", "PHP version");
define("LANINS_016", "MySQL");
define("LANINS_017", "KLART");
define("LANINS_018", "F&ouml;rs&auml;kra dig om att alla listade filer existerar och att de kan skrivas till av servern. Detta inneb&auml;r vanligen att s&auml;tta dem till CHMOD 777, men milj&ouml;er kan se olika ut - kontakta din webbv&auml;rd om du f&aring;r problem.");
define("LANINS_019", "PHP versionen installerad p&aring; din server kan inte k&ouml;ra e107. e107 kr&auml;ver en PHP version p&aring; l&auml;gst 4.3.0 f&ouml;r att k&ouml;ras korrekt. Antingen uppgradera din egen PHP, eller kontakta din webbv&auml;rd f&ouml;r en uppgradering.");
define("LANINS_020", "Forts&auml;tt installation");
define("LANINS_021", "3");
define("LANINS_022", "MySQL server detaljer");
define("LANINS_023", "Ange dina MySQL uppgifter h&auml;r.<br /><br />Om du har root-&aring;tkomst kan du skapa en ny databas genom att klicka i rutan 'Skapa databas', om inte m&aring;ste du skapa en databas manuellt eller anv&auml;nda en befintlig.<br /><br />Om du bara har en databas, anv&auml;nd ett prefix s&aring; att andra skript kan dela p&aring; samma databas.<br />Om du inte k&auml;nner till dina MySQL uppgifter, kontakta din webbv&auml;rd.");
define("LANINS_024", "MySQL Server:");
define("LANINS_025", "MySQL anv&auml;ndarnamn:");
define("LANINS_026", "MySQL l&ouml;senord:");
define("LANINS_027", "MySQL databas:");
define("LANINS_028", "Skapa databas?");
define("LANINS_029", "Tabellprefix:");
define("LANINS_030", "MySQL servern du vill att e107 skall anv&auml;nda. Kan ocks&aring; inneh&aring;lla portnummer, t.ex. \"v&auml;rdnamn:port\" eller s&ouml;kv&auml;gen till en lokal socket t.ex. \":/s&ouml;kv&auml;g/till/socket\" f&ouml;r den lokala v&auml;rden.");
define("LANINS_031", "Anv&auml;ndarnamnet e107 skall anv&auml;nda f&ouml;r att ansluta till din MySQL server");
define("LANINS_032", "L&ouml;senordet f&ouml;r anv&auml;ndaren du just angav");
define("LANINS_033", "MySQL databasen du vill att e107 skall befinna sig i, i vissa fall kallat schema. Om anv&auml;ndaren har r&auml;ttigheter att skapa en databas kan du beg&auml;ra att en ny databas skapas om den inte redan finns.");
define("LANINS_034", "Prefixet du vill att e107 skall anv&auml;nda n&auml;r tabellerna skall skapas. Anv&auml;ndbart f&ouml;r att k&ouml;ra flera olika e107 eller andra applikationer i samma databas/schema.");
define("LANINS_035", "Forts&auml;tt");
define("LANINS_036", "4");
define("LANINS_037", "Verifiering av MySQL anslutning");
define("LANINS_038", " och skapande av databas");
define("LANINS_039", "Se till att du fyller i alla f&auml;lt, viktigast &auml;r MySQL Server, MySQL anv&auml;ndarnamn och MySQL databas (Dessa kr&auml;vs alltid av MySQL servern)");
define("LANINS_040", "Fel"); // Pluralis
define("LANINS_041", "e107 kunde inte ansluta till MySQL med den information du angett. &Aring;terg&aring; till f&ouml;rra sidan och kontrollera att informationen &auml;r korrekt.");
define("LANINS_042", "Anslutning till MySQL servern uppr&auml;ttad och verifierad.");
define("LANINS_043", "Kunde inte skapa en databas, kontrollera att du har tillr&auml;cklig beh&ouml;righet f&ouml;r att skapa nya databaser p&aring; din server.");
define("LANINS_044", "Skapade databas felfritt.");
define("LANINS_045", "Klicka p&aring; knappen f&ouml;r att g&aring; vidare till n&auml;sta steg.");
define("LANINS_046", "5");
define("LANINS_047", "Administrat&ouml;rsinformation");
define("LANINS_048", "G&aring; tillbaka till f&ouml;rra steget");
define("LANINS_049", "De b&aring;da l&ouml;senorden var inte lika, ange igen.");
define("LANINS_050", "XML modul");
define("LANINS_051", "Installerad");
define("LANINS_052", "Inte Installerad");
define("LANINS_053", "e107 .700 kr&auml;ver att PHP XML modulen &auml;r installerad. Kontakta din webbv&auml;rd eller l&auml;r in informationen p&aring; ");
define("LANINS_054", " innan du forts&auml;tter");
define("LANINS_055", "Installationsbekr&auml;ftelse");
define("LANINS_056", "6");
define("LANINS_057", "e107 har nu all information som beh&ouml;vs f&ouml;r att avsluta installationen.<br /><br />Klicka p&aring; knappen f&ouml;r att skapa databastabellerna och spara alla dina inst&auml;llningar.");
define("LANINS_058", "7");
define("LANINS_060", "Kan inte l&auml;sa SQL datafilen.<br /><br />F&ouml;rs&auml;kra dig om att filen <b>core_sql.php</b> existerar i <b>/e107_admin/sql</b> katalogen.");
define("LANINS_061", "e107 kunde inte skapa alla n&ouml;dv&auml;ndiga databastabeller.<br />V&auml;nligen rensa databasen och korrigera eventuella problem innan du provar igen.");
define("LANINS_062", "V&auml;lkommen till din nya webbsajt!");
define("LANINS_063", "e107 &auml;r korrekt installerad och &auml;r nu redo att ta emot inneh&aring;ll.");
define("LANINS_064", "Din administrationssektion ");
define("LANINS_065", "finns h&auml;r");
define("LANINS_066", "klicka f&ouml;r att g&aring; dit nu. Du kommer att f&aring; logga in med det namn och l&ouml;senord du angav under installationsprocessen.");
define("LANINS_067", "du kommer att hitta FAQ och dokumentation h&auml;r.");
define("LANINS_068", "Tack f&ouml;r att du provar e107, vi hoppas att den uppfyller dina krav.\n(Du kan radera detta meddelande fr&aring;n din adminsektion.)\n\n<b>Observera att denna version av e107 &auml;r en beta version och inte avsedd att anv&auml;ndas i en produktionsmilj&ouml;.</b>");
define("LANINS_069", "e107 har installerats korrekt!<br /><br />Av s&auml;kerhetssk&auml;l skall du nu s&auml;tta filr&auml;ttigheterna p&aring; <b>e107_config.php</b> filen tillbaka till 644.<br /><br />Radera ocks&aring; install.php och e107_install katalogen fr&aring;n din server efter att du klickat p&aring; knappen nedan.");
define("LANINS_070", "e107 kunde inte spara huvudkonfigurationsfilen p&aring; din server.<br /><br />F&ouml;rs&auml;kra dig om att filen <b>e107_config.php</b> har korrekta r&auml;ttigheter");
define("LANINS_071", "Avslutar installation");
define("LANINS_072", "Admin anv&auml;ndarnamn");
define("LANINS_073", "Detta &auml;r namnet du anv&auml;nder f&ouml;r att logga in p&aring; sajten. Du kan &auml;ven anv&auml;nda detta som ditt visade namn.");
define("LANINS_074", "Visat Admin namn");
define("LANINS_075", "Detta &auml;r namnet som du vill att anv&auml;ndarna skall se i din profil, i forum och i andra areor. Om du vill visa samma namn som inloggningsnamnet, l&auml;mna tomt h&auml;r.");
define("LANINS_076", "Admin l&ouml;senord");
define("LANINS_077", "Ange det l&ouml;senord du vill ha f&ouml;r admin.");
define("LANINS_078", "Admin l&ouml;senordsbekr&auml;ftelse");
define("LANINS_079", "Ange admins l&ouml;senord igen f&ouml;r verifiering.");
define("LANINS_080", "Admin e-post");
define("LANINS_081", "Ange din e-postadress h&auml;r.");
define("LANINS_082", "namn@dinsajt.se");

?>
