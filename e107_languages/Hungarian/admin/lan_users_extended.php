<?php
# --------------------------------------------------------------------------
# e107 hungarian language file - ver: 1.14 - author: e107hungary.org team - 2006
# --------------------------------------------------------------------------

define("EXTLAN_1", "Név");
define("EXTLAN_2", "Előnézet");
define("EXTLAN_3", "Értékek");
define("EXTLAN_4", "Kötelező");
define("EXTLAN_5", "Alkalmazható");
define("EXTLAN_6", "Olvasható");
define("EXTLAN_7", "Írható");
define("EXTLAN_8", "Művelet");
define("EXTLAN_9", "Bővített felhasználó mezők");

define("EXTLAN_10", "Mező neve");
define("EXTLAN_11", "A mező e névvel lesz tárolva az adatbázis táblában, nem egyezhet más mező nevével");
define("EXTLAN_12", "Mező szövege");
define("EXTLAN_13", "A mező e névvel fog megjelenni az oldalakon");
define("EXTLAN_14", "Mező típusa");
define("EXTLAN_15", "Mező típusának paraméterei");
define("EXTLAN_16", "Alapértelmezett érték");
define("EXTLAN_17", "Adj meg minden lehetséges értékeket minden sorban. <br> Az adatbázis táblához olvasd el a súgót.");
define("EXTLAN_18", "Kötelező");
define("EXTLAN_19", "A felhasználóknak meg kell adniuk egy értéket e mezőben, amikor frissítik a beállításaikat, adataikat.");
define("EXTLAN_20", "Meghatározza, hogy mely felhasználók fogják alkalmazni e mezőt.");
define("EXTLAN_21", "Meghatározza, hogy kik fogják látni ezt a mezőt a beállításaiknál.");
define("EXTLAN_22", "Meghatározza, hogy kik láthatják az értékét a felhasználói oldalon.");
define("EXTLAN_23", "Bővített mező hozzáadása");
define("EXTLAN_24", "Bővített mező  frissítése");
define("EXTLAN_25", "mozgatás lefelé");
define("EXTLAN_26", "mozgatás felfelé");
define("EXTLAN_27", "Törlés megerősítése");
define("EXTLAN_28", "Nincs definiálva bővített mező");
define("EXTLAN_29", "Bővített felhasználói mező elmentve.");
define("EXTLAN_30", "Bővített felhasználói mező törölve");
//define("EXTLAN_31", "Bővített mező menü");
//define("EXTLAN_32", "Bővített mező kezdőoldal");
define("EXTLAN_33", "Mégsem módosít");
define("EXTLAN_34", "Bővített mezők");
define("EXTLAN_35", "Kategóriák");
define("EXTLAN_36", "Nincs hozzárendelve kategória");
define("EXTLAN_37", "Nincsenek kategóriák");
define("EXTLAN_38", "Kategória neve");
define("EXTLAN_39", "Kategória hozzáadása");
define("EXTLAN_40", "Kategória létrehozva");
define("EXTLAN_41", "Kategória törölve");
define("EXTLAN_42", "Kategória frissítése");
define("EXTLAN_43", "Kategória frissítve");
define("EXTLAN_44", "Kategória");
define("EXTLAN_45", "Új mező hozzáadása");
define("EXTLAN_46", "Súgó");
define("EXTLAN_47", "Új paraméter hozzáadása");
define("EXTLAN_48", "Új érték hozzáadása");
define("EXTLAN_49", "A felhasználó elrejtheti");
define("EXTLAN_50", "Ha igen-re állítod, az adatot csak adminok fogják látni");
define("EXTLAN_51", "Bármilyen érvényes w3c paramétert be lehet írni ide<br />pl.: <i><b>class='tbox' size='40' maxlength='80'</i></b>");
define("EXTLAN_52", "regex validációs kód");
define("EXTLAN_53", "Add meg azt a regex kódot, amelynek egyeznie kell az érvényes bejegyzés létrehozásához <br />**a regex határolójelek kötelezőek**");
define("EXTLAN_54", "regex hiba szöveg");
define("EXTLAN_55", "Add meg azt a hibaüzenetet, amely a regex hiba esetén lesz látható.");
define("EXTLAN_56", "Előre definiált mezők");
define("EXTLAN_57", "Aktiválva");
define("EXTLAN_58", "Nincs aktiválva");
define("EXTLAN_59", "Aktiválás");
define("EXTLAN_60", "Kikapcsolás");
define("EXTLAN_61", "Nincs");

define("EXTLAN_62", "Tábla választás");
define("EXTLAN_63", "Mező id választás");
define("EXTLAN_64", "Megjelenítési érték választás");

define("EXTLAN_65", "Nem - Nem fog megjelenni a regisztrációs oldalon");
define("EXTLAN_66", "Igen - Meg fog jelenni a regisztrációs oldalon");
define("EXTLAN_67", "Nem - Megjelenik a regisztrációs oldalon");

define("EXTLAN_68", "Mező:");
define("EXTLAN_69", "Aktiválható");
define("EXTLAN_70", "HIBA!! Mező:");
define("EXTLAN_71", "Nem aktiválható!");
define("EXTLAN_72", "Kikapcsolható");
define("EXTLAN_73", "Nem lehet kikapcsolni!");
define("EXTLAN_74", "ez egy védett mező és nem lehet használni.");
define("EXTLAN_75", "Hiba, a mezőt nem sikerült hozzáadni az adatbázishoz.");
define("EXTLAN_76", "Érvénytelen karakter a mező nevében - csak A-Z, a-z, 0-9, '_' engedélyezett.");
define("EXTLAN_77", "Kategória nincs törölve - először töröld a benne lévő mezőket: ");


 //textbox
define("EXTLAN_HELP_1", "<b><i>Paraméterek:</i></b><br />size - a mező mérete<br />maxlength - a mező maximális hossza<br />class - a mező css class definíciója<br />");
//radio buttons
define("EXTLAN_HELP_2", "Írd be a szöveget a beállítások részére az 'Értékek' szövegdobozba - egy szövegdoboz/beállítás. Adj hozzá új dobozt, ha szükséges");
//dropdown
define("EXTLAN_HELP_3", "Írd be a szöveget a beállítások részére az 'Értékek' szövegdobozba - egy szövegdoboz/beállítás. Adj hozzá új dobozt, ha szükséges");
//db field
define("EXTLAN_HELP_4", "<b><i>Értékek:</i></b><br />Itt három értéket kellene megadni MINDIG:<br /><ol><li>adatbázis tábla</li><li>azonosító mező </li><li>érték mező</li></ol><br />");
//textarea
define("EXTLAN_HELP_5", "Határozz meg egy területet a szabad-formájú szöveghez. (Válaszd ki a méretet a 'szöveget tartalmazó mező' szövegdobozban, ha szükséges)");
//integer
define("EXTLAN_HELP_6", "Numerikus érték bevitelének engedélyezése");
//date
define("EXTLAN_HELP_7", "Kötelezi a felhasználót egy dátum megadására");
// Language
define("EXTLAN_HELP_8", "Telepített nyelv kiválasztásának engedélyezése");


?>
