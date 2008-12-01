<?php
# ------------------------------------------------------------------------------------------------------------------
# e107 hungarian language file - $Revision: 1.9 $ - $author: e107hungary.org team $ - $Date: 2008-12-01 16:42:39 $
# ------------------------------------------------------------------------------------------------------------------

setlocale(LC_ALL, 'hu_HU.UTF-8', 'hu_HU@euro', 'hu_HU', 'hu', 'Hungarian');
define("CORE_LC", 'hu');
define("CORE_LC2", 'HU.UTF-8');
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
define("CORE_LAN10", "Hibás cookie észlelése - Kijelentkezett.");

// Footer
define("CORE_LAN11", "Oldal létrehozási idő: ");
define("CORE_LAN12", " másodperc, ");
define("CORE_LAN13", " lekérdezési idő. ");
define("CORE_LAN14", "");			// Used in 0.8
define("CORE_LAN15", "Adatbázis lekérdezés: ");
define("CORE_LAN16", "Memória használat: ");

// img.bb
define('CORE_LAN17', '[ Kép letiltva ]');
define('CORE_LAN18', 'Kép: ');

define("CORE_LAN_B", "b");
define("CORE_LAN_KB", "kb");
define("CORE_LAN_MB", "Mb");
define("CORE_LAN_GB", "Gb");
define("CORE_LAN_TB", "Tb");


define("LAN_WARNING", "Figyelmeztetés!");
define("LAN_ERROR", "Hiba");
define("LAN_ANONYMOUS", "Anonymous");
define("LAN_EMAIL_SUBS", "-Email-");

?>
