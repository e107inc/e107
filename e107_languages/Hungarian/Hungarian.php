<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/Hungarian.php,v $
|     $Revision: 1.4 $
|     $Date: 2006-12-22 21:27:05 $
|     $Author: e107hun-lac $
+----------------------------------------------------------------------------+
*/
setlocale(LC_ALL, 'hu_HU.UTF-8', 'hu_HU@euro', 'hu_HU', 'hu', 'Hungarian');
define("CORE_LC", 'hu');
define("CORE_LC2", 'HU.utf-8');
// define("TEXTDIRECTION","rtl");
define("CHARSET", "utf-8");  // for a true multi-language site. :)
define("CORE_LAN1","Hiba : A téma hiányzik.\\n\\nCseréld le a beállításoknál a használt témát (admin terület) vagy tölts fel az aktuális téma file-it a szerverre.");

//v.616
define("CORE_LAN2", " \\1 írta:");// "\\1" represents the username.
define("CORE_LAN3", "fájl csatolása nem engedélyezett");

//v0.7+
define("CORE_LAN4", "Töröld az install.php file-t a szerveredről");
define("CORE_LAN5", "ha nem hajtod végre, akkor az oldalad potenciális biztonsági kockázatnak van kitéve");

// v0.7.6
define("CORE_LAN6", "A flood védelem ezen az oldalon aktiválva van és figyelmeztet téged, ha folytatod az oldal folyamatos lekérdezését, akkor ki leszel tiltva.");
define("CORE_LAN7", "A mag (Core) megkisérli a helyreállítást az autómatikus biztonsági mentésből.");
define("CORE_LAN8", "Mag (Core) beállítások Hiba");
define("CORE_LAN9", "A mag (Core) nem tudja a visszaállítást végrehajtani az autómatikus mentésből. Végrehajtás megszakítva.");
define("CORE_LAN10", "Korrupt cookie észlelése - Kijelentkezett.");


define("LAN_WARNING", "Figyelmeztetés!");
define("LAN_ERROR", "Hiba");
define("LAN_ANONYMOUS", "Anonymous");
?>
