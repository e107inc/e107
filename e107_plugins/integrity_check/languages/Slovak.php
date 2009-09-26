<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/integrity_check/languages/Slovak.php,v $
|     $Revision: 1.4 $
|     $Date: 2009-09-26 15:20:56 $
|     $Author: yarodin $
+----------------------------------------------------------------------------+
*/
	
define("Integ_01", "Vporiadku uložené");
define("Integ_02", "Chyba pri ukladaní");
define("Integ_03", "Chýbajúce súbory:");
define("Integ_04", "CRC-chyby:");
define("Integ_05", "Nie je môžené otvoriť súbor...");
define("Integ_06", "Skontrolovať integritu súborov");
define("Integ_07", "Žiadne prístupné súbory");
define("Integ_08", "Skontrolovať integritu");
define("Integ_09", "Vytvoriť sfv-súbor");
define("Integ_10", "Do zvoleného adresára <u>nemôže</u> byť uložený crc-súbor.");
define("Integ_11", "Meno súboru:");
define("Integ_12", "Vytvoriť sfv súbor");
define("Integ_13", "Kontrola integrity");
define("Integ_14", "Vytvorenie SFV nie je možné, pretože adresár ".e_PLUGIN."integrity_check/<b>{output}</b> nemá práva zápisu. Prosím, zmente prístupové práva na CHMOD 777!");
define("Integ_15", "Všetky súbory skontrolované a v poriadku!");
define("Integ_16", "Žiadny crc súbor jadra nedostupný");
define("Integ_17", "Žiadny crc súbor pluginu nedostupný");
define("Integ_18", "Vytvoriť crc súbor pluginu");
define("Integ_19", "Súbory kontrolného súčtu jadra");
define("Integ_20", "Súbory kontrolného súčtu pluginu");
define("Integ_21", "Zvoľte plugin, pre ktorý chcete vytvoriť crc súbor.");
define("Integ_22", "Použitie gzip");
define("Integ_23", "Len skontrolovať inštalované témy");
define("Integ_24", "Úvodná stránka administrátora");
define("Integ_25", "Odísť z administrátora");
define("Integ_26", "Nahrať stránku s normálnou hlavičkou");
	
// define("Integ_29", "<br /><br /><b>*<u>CRC-ERRORS:</u></b><br />These are checksum errors and there are two possible reasons for this:<br />-You changed something within the mentioned file, so it isn't longer the same as the original.<br />-The mentioned file is corrupt, you should reupload it!");
// language file should contain NO html. 

define("Integ_30", "Pre menšie zaťaženie procesora , použite kontrolu v jednom až desiatich krokoch.");
define("Integ_31", "Krokov: ");
define("Integ_32", "V adresáry s crc súbormy je súbor s názvom <b>log_crc.txt</b>. Prosím, zmažte ho! (Alebo skúste refresh)");
define("Integ_33", "V adresáry s crc súbormy je súbor s názvom <b>log_miss.txt</b>. Prosím, zmažte ho! (Alebo skúste refresh)");
define("Integ_34", "Váš adresár s crc súbormi nie je zapisovateľný!");
define("Integ_35", "Kvôli nasledujúcim dôvodom je vám povolené použiť len <b>jeden</b> krok:");
define("Integ_36", "Kliknite sem, ak nechcete čakať 5 sekúnd do ďaľšieho kroku:");
define("Integ_37", "Kliknite");
define("Integ_38", "Ďaľších <u><i>{counts}</i></u> riadkov do...");
define("Integ_39", "Prosím, zmažte súbor:<br />".e_PLUGIN."integrity_check/<u><i>do_core_file.php</i></u>!<br />Je starého dátumu a nemal by byť použitý na zverejnenie...");
	
?>
