<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/e107_plugins/integrity_check/languages/Danish.php,v $
|        $Revision: 1.1 $
|        $Date: 2005-07-06 22:12:03 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/
	
define("Integ_01", "Gemt ");
define("Integ_02", "Gem slog fejl");
define("Integ_03", "Manglende filer:");
define("Integ_04", "CRC-fejl:");
define("Integ_05", "Ikke istand til at åbne Fil...");
define("Integ_06", "Tjek fil-integritet");
define("Integ_07", "Ingen filer tilgængelige");
define("Integ_08", "Tjek integritet");
define("Integ_09", "Lav sfv-fil");
define("Integ_10", "Den valgte mappe vil <u>ikke</u> blive gemt inden i crc-filen.");
define("Integ_11", "Filnavn:");
define("Integ_12", "Lav sfv fil");
define("Integ_13", "Integritet-tjek");
define("Integ_14", "SFV-Skabelse ikke mulig, fordi mappen ".e_PLUGIN."integrity_check/<b>{output}</b> er skrivebeskyttet. Chmod denne mappe 777!");
define("Integ_15", "Alle filer er kontrolleret og er o.k.!");
define("Integ_16", "Ingen core-crc-fil tilgængelig");
define("Integ_17", "Ingen plugin-crc-filer tilgængelig");
define("Integ_18", "Lav Plugin-CRC-Fil");
define("Integ_19", "Core-Checksum-Filer");
define("Integ_20", "Plugin-Checksum-Filer");
define("Integ_21", "Vælg det plugin du vil lave en crc-fil til.");
define("Integ_22", "Brug gzip");
define("Integ_23", "Tjek kun temaer der faktisk er på din side");
define("Integ_24", "Admin forside");
define("Integ_25", "Forside");
define("Integ_26", "Indlæs websted med normal header");

//define("Integ_29", "<br /><br /><b>*<u>CRC-FEJL:</u></b><br />Disse er checksum fejl og der er to mulige grunde for dette:<br />-Du har ændret noget inden i den nævnte fil, så det er ikke længere den samme som originalen.<br />-Den nævnte fil er korrupt, Du bør genuploade den!");
define("Integ_30", "For mindre cpu-brug, kan du lave kontrollen i 1 - 10 trin.");
define("Integ_31", "Trin: ");
define("Integ_32", "Der er en fil kaldet <b>log_crc.txt</b> i din crc-mappe. Slet! (eller prøv at genopfriske)");
define("Integ_33", "Der er en fil kaldet <b>log_miss.txt</b> i din crc-mappe. Slet! (eller prøv at genopfriske)");
define("Integ_34", "Din Crc-mappe er skrive beskyttet!");
define("Integ_35", "Af følgende grund(e) kan du kun vælge <b>et</b> trin:");
define("Integ_36", "Klik her, hvis du ikke vil vente 5 sekunder til det næste trin:");
define("Integ_37", "Klik mig");
define("Integ_38", "En anden <u><i>{tæller}</i></u> linjer der skal laves...");
define("Integ_39", "Slet filen:<br />".e_PLUGIN."integrity_check/<u><i>do_core_file.php</i></u>!<br />Er forældet og aldrig ment offentliggørelse...");

?>