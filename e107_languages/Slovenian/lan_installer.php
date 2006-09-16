<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Slovenian/lan_installer.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-09-16 17:08:25 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
define("LANINS_001", "Namestitev e107");


define("LANINS_002", "korak");
define("LANINS_003", "1");
define("LANINS_004", "Izbira jezika");
define("LANINS_005", "Prosimo Vas, da izberete jezik, ki ga želite uporabiti med namestitvenim postopkom");
define("LANINS_006", "Nastavitev Jezika");
define("LANINS_007", "2");
define("LANINS_008", "PHP &amp; MySQL Preveritev verzij / Dovoljenja za dostop do datotek");
define("LANINS_009", "Ponovno preveri dovoljenja za dostop do datotek");
define("LANINS_010", "Datoteke se ne da spremeniti: ");
define("LANINS_010a", "Mape se ne da spremeniti: ");
define("LANINS_011", "Napaka");
define("LANINS_012", " Izgleda da MySQL funkcije ne obstajajo. Morda  podpora za MySQL PHP  ni nameščena ali pa ni pravilno nameščena."); // pomoč za 012
define("LANINS_013", "Vaše verzije MySQL ni bilo mogoče določiti . To morda pomeni da je vašr MySQL strežnik nedejaven ali pa zavrača povezave.");
define("LANINS_014", "Dovoljenja za dostop do datotek");
define("LANINS_015", "PHP Verzija");
define("LANINS_016", "MySQL");
define("LANINS_017", "PASS");
define("LANINS_018", "Prepričajte se da vse datoteke na listi obstajajo in da so čitljive za server. To ponavadi vplete CHMODing 777, zato je lahko drugače - kontaktirajte vašega gostitelja, če naletite na težave.");
define("LANINS_019", "Verzija PHP ki je nameščena na Vašem serverju ne more obdelovati e107. e107 za pravilno delovanje zahteva vsaj verzijo 4.3.0. Posodobite PHP verzijo ali pa za to zaprosite svojega gostitelja.");
define("LANINS_020", "Nadaljuj z namestitvijo");
define("LANINS_021", "3");
define("LANINS_022", "Podrobnosti MySQL Strežnika");
define("LANINS_023", "Prosimo Vas, da tu vnesete nastavitve za  MySQL. Če imate dovoljenje za to, lahko z obkljukanjem polja naredite novo bazo podatkov, če pa nimate dovoljenja, morate uporabiti že obstoječo bazo podatkov.
Če imate samo eno bazo podatkov uporabite to, tako da bodo lahko drugi skripti uporabljali isto bazo podatkov.
Če ne poznate podrobnosti o svojem MySQL kontaktirajte svojega spletnega gostitelja.t.");

define("LANINS_024", "MySQL Strežnik:");
define("LANINS_025", "MySQL Uporabniško ime:");
define("LANINS_026", "MySQL Geslo:");
define("LANINS_027", "MySQL Baza podatkov:");
define("LANINS_028", "Ali želite ustvariti bazo podatkov?");
ddefine("LANINS_029", "Table prefix:");
define("LANINS_030", "MySQL strežnik želi uporabiti e107. Vsebuje lahko tudi številko priklopa ipd. \"hostname:port\" ali podnožje lokalnega gostitelja ipd. \":/pot/do/podnožja\" za lokalnega gostitelja.");
define("LANINS_031", "Uporabniško ime, ki ga naj e107 uporabi za povezavo na Vaš MySQL strežnik");
define("LANINS_032", "Geslo za uporabnika, ki ste ga pravkar  vnesli");
define("LANINS_033", "The MySQL database you wish e107 to reside in, sometimes refered to as a schema. If the user has database create permissions you can opt to create the database automatically if it doesn't already exsist.");
define("LANINS_034", "The prefix you wish for e107 to use when creating the e107 tables. Useful for multiple installs of e107 in one database schema.");
define("LANINS_035", "Nadaljuj");
define("LANINS_036", "4");
define("LANINS_037", "Preveritev povezave z MySQL");
define("LANINS_038", "  in formacija baze podatkov ");
define("LANINS_039", "Prosimo  Vas da se preprièate da ste izpolnili vsa polja; najpomembnejša so MySQL strežnik, MySQL uporabniško ime in MySQL bata podatkov (Te podatke MySQL Strežnik vedno zahteva)");
define("LANINS_040", "Napake");
define("LANINS_041", "e107 z informacijami ki ste jih vnesli ni mogel vzpostaviti povezave z MySQL strežnikom. Prosimo Vas, da se vrnete na prejšnjo stran in se preprièate da so informacije pravilne.");
define("LANINS_042", "Povezava z MySQL strežnikom je bila vzpostavljena in preverjena.");
define("LANINS_043", "Baze podatkov ni bilo mogoče ustvariti, prosimo Vas da se prepričate da imate dovoljenje za ustvatritev baze podatkov na svojem strežniku.");
define("LANINS_044", "Uspešno ste ustvarili bazo podatkov.");
define("LANINS_045", "Prosimo kliknite na gumb  za to da bi prišli na naslednji korak.");
define("LANINS_046", "5");
define("LANINS_047", "Podrobnosti o administratorju");
define("LANINS_048", "Pojdi nazaj na prejšnji korak");
define("LANINS_049", "Geslo ki ste ju vnesli nista isti. Prosimo Vas, da greste nazaj in poskusite znova.");
define("LANINS_050", "Podpora za XML ");
define("LANINS_051", "je naložena");
define("LANINS_052", "ni naložena");
define("LANINS_053", "e107 .700 zahteva da je nameščena podpora za PHP XML. Prosimo Vas da kontaktirate svojega gostitelja ali pa preberete informacije na ");
define("LANINS_054", " pred nadaljevanjem");
define("LANINS_055", "Potrditev namestitve");
define("LANINS_056", "6");
define("LANINS_057", " e107 ima sedaj vse informacije, ki jih potrebuje za dokončanje namestitve.

Prosimo kliknite na gumb če želite ustvariti bazo podatkov in shraniti vse svoje nastavitve.

");
define("LANINS_058", "7");
define("LANINS_060", "Podrobnosti o sql ni bilo mogoče prebrati

Prosimo Vas da se prepričate da datoteka <b>core_sql.php</b> obstaja v <b>/e107_admin/sql</b> mapi");
define("LANINS_061", "e107 was unable to create all of the required database tables.
Please clear the database and rectify any problems before trying again.");
define("LANINS_062", "Dobrodošli na naši novi spletni srani!");
define("LANINS_063", "e107 je bil uspešno nameščen in je pripravljen na sprejemanje vsebine.");
define("LANINS_064", "Your administration section is");
define("LANINS_065", "nahaja se tu");
define("LANINS_066", "Če želite odditi tja, kliknite. Vpisati boste morali uporabniško ime in geslo ki ste ju uporabili pri namestitvi.");
define("LANINS_067", "Tukaj boste našli Pogosto zastavljena vprašanja in dokumentacijo.");
define("LANINS_068", "Hvala Vam da ste preizkusili e107, upamo da zadovolji Vaše potrebe glede spletnih strani.\n(To sporočilo lahko odstranite iz svojega administratorskega podroèja.)\n\n<b>Prosimo vedite da je ta verzija e107 beta in ni mišljena za uporabo na aktivnih spletnih straneh.</b>");
define("LANINS_069", "e107 je bil uspešno namešèen!

Iz varnostnih razlogov, bi sedaj morali  nastaviti dovoljenja za datoteke <b>e_config.php</b> nazaj na  644.

Prosimo Vas tudi, da po tem ko ste kliknili na spodnji gumb, z svojega strežnika zbrišete install.php in mapo e107_install
");
define("LANINS_070", "e107 e107 na Vaš strežnik ni mogel shraniti glavne konfiguracijske datoteke.

Please ensure the <b>e107_config.php</b> datoteka ima pravilno dovoljenje");
define("LANINS_071", "Končujem namestiev");

define("LANINS_072", "Uporabniško ime administratorja");
define("LANINS_073", "To je ime ki ga boste uporabili za prijavo na stran. Če želite to ime uporabiti tudi kot ime za prikaz");
define("LANINS_074", "Administratorjevo ime za prikaz");
define("LANINS_075", "To je ime ki bo Vašim uporabnikom prikazano v Vašem profilu, forumih in drugod. Če želite da je to ime isto kot ime za Vpis, pustite to polje prazno.");
define("LANINS_076", "Administratorjevo geslo");
define("LANINS_077", "Prosimo Vas, da tu vpišete administratorjevo geslo, ki ga želite uporabiti");
define("LANINS_078", "Potrditev administratorjevega gesla");
define("LANINS_079", "Prosimo Vas, da za potrditev še enkrat vpišete administratorjevo geslo");
define("LANINS_080", "Administratorjev E-poštni naslov");
define("LANINS_081", "Vpišite svoj e-poštni naslov");

define("LANINS_082", "uporabnik@vašastran.com");

?>
