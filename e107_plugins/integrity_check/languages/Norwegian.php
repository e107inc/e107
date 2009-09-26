?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/integrity_check/languages/Norwegian.php,v $
|     $Revision: 1.3 $
|     $Date: 2009-09-26 15:20:56 $
|     $Author: yarodin $
+----------------------------------------------------------------------------+
*/
	
define("Integ_01", "Lagret");
define("Integ_02", "Lagring feilet");
define("Integ_03", "Manglede filer:");
define("Integ_04", "CRC-feil:");
define("Integ_05", "Kunne ikk åpne fil...");
define("Integ_06", "Sjekk fil-integritet");
define("Integ_07", "Ingen filer tilgjengelige");
define("Integ_08", "Sjekk integritet");
define("Integ_09", "Lag svf-fil");
define("Integ_10", "Den valgte mappen vil <u>ikke</u> bli lagret inni i crc-filen.");
define("Integ_11", "Filnavn:");
define("Integ_12", "Lag svf-fil");
define("Integ_13", "Integritets-sjekking");
define("Integ_14", "SFV-fil kan ikke lages, P.G.A mappen ".e_PLUGIN."integrity_check/<b>{output}</b> som er skrivebeskyttet. Vennligst chmod denne mappen til 777!");
define("Integ_15", "Alle filene har blitt sjekket og er ok!");
define("Integ_16", "Ingen kjerne-crc-fil er tilgjengelig");
define("Integ_17", "Ingen plugin-crc-fil er tilgjengelig");
define("Integ_18", "Lag plugin-crc-fil");
define("Integ_19", "Kjerne-kontrollsum-filer");
define("Integ_20", "Plugin-kontrollsum-filer");
define("Integ_21", "Velg pluginen du vil lage en crc-fil for.");
define("Integ_22", "Bruk gzip");
define("Integ_23", "Kun sjekk installerte temaer");
define("Integ_24", "Admin forside");
define("Integ_25", "Forlat admin-område");
define("Integ_26", "Last side med normal overskrift");
define("Integ_27", "BRUK FILE INSPECTOR FOR KJERNE FILER");
	
// define("Integ_29", "<br /><br /><b>*<u>CRC-FEIL:</u></b><br />Det er kontrollsum feiler og det er 2 mulige hendelser for dette:<br />-Du endret noe inni denne filen, så den er ikke original lenger.<br />-Den filen som er nevnt er korrupt, du burde prøve å laste den opp på nytt!");
// Språk fil kan ikke inneholde HTML. 

define("Integ_30", "For mindre CPU-brul, kan du prøve å sjekke steg 1-10.");
define("Integ_31", "Steg: ");
define("Integ_32", "Det er en fil som heter <b>log_crc.txt</b> i din crc-mappe. Vennligst slett den! (eller prøv å refreshe)");
define("Integ_33", "Det er en fil som heter <b>log_miss.txt</b> i din crc-mappe. Vennligst slett den! (eller prøv å refreshe)");
define("Integ_34", "Din crc-mappe er skrivebeskyttet!");
define("Integ_35", "P.G.A følgende grunn(er) er det kun tillat å velge <b>ett</b> steg:");
define("Integ_36", "Trykk her, hvis du ikke vil vente 5 sekunder på neste steg:");
define("Integ_37", "Trykk på meg");
define("Integ_38", "En annen <u><i>{counts}</i></u> linje å gjøre...");
define("Integ_39", "Vennligst slett denne filen:<br />".e_PLUGIN."integrity_check/<u><i>do_core_file.php</i></u>!<br />Den er utdatert og aldri ment for publikk utgivelse...");
	
?>
?>