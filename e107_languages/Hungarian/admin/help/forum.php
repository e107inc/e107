<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/forum.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-12-13 20:44:36 $
|     $Author: e107hun-lac $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$caption = "Fórum Súgó";
$text = "<b>Általános</b><br />
Fórumok létrehozása, módosítása<br />
<br />
<b>Főkategóriák/Fórumok</b><br />
A főkategóriák tényleges fórumokat tartalmazzák, ezzel egyszerűbb az elrendezés és a fórumok közötti navigáció.
<br /><br />
<b>Elérhetőség</b>
<br />
Beállíthatod, hogy csak bizonyos látogatók érhessék el a fórumokat. Ha beállítod a látogatók 'csoportját', a kijelöléssel csak ezen csoport tagjai érhetik el a fórumot. Főkategóriát és egyéni fórumot is korlátozhatsz így.
<br /><br />
<b>Moderátorok</b>
<br />
Jelöld be a felsorolt adminisztrátorok nevénél, hogy fórum moderátor legyen. Az adminisztrátornak fórum moderálási jogosultságokkal kell rendelkeznie, hogy megjelenjen a listában.
<br /><br />
<b>Rangok</b>
<br />
Itt állíthatod be a rangokat. Ha a kép mezők ki vannak töltve, képek lesznek használva, a rang nevek használatához írd be azokat, és győződj meg róla, hogy a hozzá tartozó rang kép mező üres.<br />A határ az a pontszám, amelynek elérésével a tag az adott rangot megkapja.";
$ns -> tablerender($caption, $text);
unset($text);
?>
