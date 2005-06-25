<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/integrity_check/languages/Swedish.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-06-25 11:07:35 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

define("Integ_01", "Sparades");
define("Integ_02", "Spara misslyckades");
define("Integ_03", "Saknade filer:");
define("Integ_04", "CRC-fel:");
define("Integ_05", "Kan inte öppna fil...");
define("Integ_06", "Kontrollera fil-integritet");
define("Integ_07", "Inga filer tillgängliga");
define("Integ_08", "Kontrollera integritet");
define("Integ_09", "Skapa sfv-fil");
define("Integ_10", "Den valda foldern kommer <u>inte</u> att sparas i crc-filen.");
define("Integ_11", "Filnamn:");
define("Integ_12", "Skapa sfv-fil");
define("Integ_13", "Kontrollerar integritet");
define("Integ_14", "Kan ej skapa SFV eftersom katalogen ".e_PLUGIN."integrity_check/<b>{output}</b> är skrivskyddad. Sätt chmod på denna katalog till 777!");
define("Integ_15", "Alla filer har kontrollerats och är o.k.!");
define("Integ_16", "Ingen kärn-crc-fil tillgänglig");
define("Integ_17", "Inga plugin-crc-filer tillgängliga");
define("Integ_18", "Skapa plugin-CRC-fil");
define("Integ_19", "Kärn-checksumme-filer");
define("Integ_20", "Plugin-checksumme-filer");
define("Integ_21", "Välj den plugin du vill skapa en crc-fil för.");
define("Integ_22", "Använd gzip");
define("Integ_23", "Kontrollera bara installerade teman");
define("Integ_24", "Admin förstasida");
define("Integ_25", "Lämna adminarea");
define("Integ_26", "Ladda sajt med normal header");

// define("Integ_29", "<br /><br /><b>*<u>CRC-FEL:</u></b><br />Dessa är checksummefel och det finns två möjliga orsaker till dessa:<br />-Du har ändrat något i den nämnda filen så att den inte längre stämmer mot originalet.<br />-Den nämnda filen är skadad, då skall du ladda upp den igen!");
// Srpåkfil skall INTE innehålla någon html.

define("Integ_30", "För lägre cpu-belastning kan du utföra kontrollerna i 1 - 10 steg.");
define("Integ_31", "Steg: ");
define("Integ_32", "Det finns en fil med namnet <b>log_crc.txt</b> i din crc-folder. Radera den! (Eller prova uppdatera)");
define("Integ_33", "Det finns en fil med namnet <b>log_miss.txt</b> i din crc-folder. Radera den! (Eller prova uppdatera)");
define("Integ_34", "Din crc-folder är skrivskyddad!");
define("Integ_35", "På grund av följande orsak(er) kan du bara välja <b>ett</b> steg:");
define("Integ_36", "Klicka här om du inte vill vänta 5 sekunder till nästa steg:");
define("Integ_37", "Klicka här");
define("Integ_38", "Nu är det <u><i>{counts}</i></u> rader kvar att göra...");
define("Integ_39", "Vänligen radera filen:<br />".e_PLUGIN."integrity_check/<u><i>do_core_file.php</i></u>!<br />Den är gammal och aldrig menad för publik distribution...");

?>