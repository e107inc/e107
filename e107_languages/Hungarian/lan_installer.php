<?php

define("LANINS_001", "e107 Telepítés");


define("LANINS_002", "Szakasz ");
define("LANINS_003", "1");
define("LANINS_004", "Nyelv kiválasztása");
define("LANINS_005", "Válaszd ki a telepítés folyamán használt nyelvet");
define("LANINS_006", "Nyelv kiválasztása");
define("LANINS_007", "4");
define("LANINS_008", "PHP &amp; MySQL Verzió Ellenörzés / File Jogosultság Ellenörzés");
define("LANINS_009", "File Jogosultság Újraellenörzése");
define("LANINS_010", "File nem írható: ");
define("LANINS_010a", "Mappa nem írható: ");
define("LANINS_011", "Hiba");
define("LANINS_012", "MySQL Funkciók nem léteznek. Talán valamelyik MySQL PHP kiterjesztés nincs telepítve vagy nincs beállítva megfelelően."); // help for 012
define("LANINS_013", "Nem lehet megállapítani a MySQL verziószámát. Lehetséges, hogy a MySQL szerver nem elérhető vagy megszakadt a kapcsolat.");
define("LANINS_014", "File Jogosultságok");
define("LANINS_015", "PHP Verzió");
define("LANINS_016", "MySQL");
define("LANINS_017", "Ellenőrizve");
define("LANINS_018", "Gondoskodj róla, hogy a felsorolt file-k létezzenek és a szerver által írhatóak legyenek. Ez normális esetben CHMOD 777, de előfordulhat más beállítás is - probléma esetén lépj kapcsolatba a kiszolgálóval.");
define("LANINS_019", "A szerveren telepített PHP verzió nem alkalmas az e107 futtatására. Az e107-hez szükséges minimum a PHP 4.3.0 verzió a megfelelő működéshez. Vagy frissítsd a PHP verziót, vagy lépj kapcsolatba a kiszolgálóval.");
define("LANINS_020", "Telepítés folytatása");
define("LANINS_021", "2");
define("LANINS_022", "MySQL Szerver Adatok");
define("LANINS_023", "Írd be a MySQL beállításokat.

Ha root jogosultsággal rendelkezel, akkor létre tudsz hozni új adatbázist a jelölődoboz bejelölésével. Ha nem szükséges vagy már létezik, ne jelöld be.

Ha csak egy adatbázissal rendelkezel, használd a prefix-et (előtag), hogy más script-ek is használhassák az adatbázist.
Ha nem ismered a MySQL adatait, lépj kapcsolatba a kiszolgálóval.");
define("LANINS_024", "MySQL Szerver:");
define("LANINS_025", "MySQL Felhasználónév:");
define("LANINS_026", "MySQL Jelszó:");
define("LANINS_027", "MySQL Adatbázis:");
define("LANINS_028", "Létrehozol adatbázist?");
define("LANINS_029", "Tábla prefix (előtag):");
define("LANINS_030", "A MySQL szerver, amit használni szeretnél az e107-hez. Belefoglalhatsz még egy port számot is. Pl.: \"hostname:port\" vagy egy útvonalat a helyi kapcsolathoz, pl.: \":/path/to/socket\" a localhost-hoz.");
define("LANINS_031", "A felhasználónév, mellyel kapcsolódik az e107 a MySQL szerverhez");
define("LANINS_032", "A felhasználó jelszava, amit éppen beírsz");
define("LANINS_033", "Az e107 MySQL adatbázisa, néha hivatkozik egy sémára. Ha a felhasználó rendelkezik adatbázis létrehozása jogosultsággal, akkor választható az adatbázis automatikus létrehozása, ha még nem létezik.");
define("LANINS_034", "A prefix (előtag) az, amit az e107 használni fog, mikor létrehozza az adatbázis táblákat. Hasznos, ha összetett e107 telepítést kell végezni azonos adatbázisban.");
define("LANINS_035", "Folytatás");
define("LANINS_036", "3");
define("LANINS_037", "MySQL Kapcsolat Ellenörzése");
define("LANINS_038", " és Adatbázis Létrehozása");
define("LANINS_039", "Természetesen, ki kell töltened az összes mezőt, nagyon fontos: MySQL Szerver, MySQL Felhasználónév és MySQL Adatbázis (Ezekre mindig szüksége van a MySQL Szervernek)");
define("LANINS_040", "Hiba");
define("LANINS_041", "e107 nem tud kapcsolódni a MySQL szerverhez a rendelkezésre álló adatokkal. Lépj az előző oldalra és ellenőrizd le az adatokat.");
define("LANINS_042", "A MySQL szerverhez történő kapcsolódás létrejött, ellenőzizve.");
define("LANINS_043", "Az adatbázis létrehozása sikertelen, ellenőzizd az adatbázis létrehozásához szükséges jogosultságokat a szerveren.");
define("LANINS_044", "Adatbázis sikeresen létrejött.");
define("LANINS_045", "Katt a gombra a folyamat folytatásához.");
define("LANINS_046", "5");
define("LANINS_047", "Adminisztrátor Adatai");
define("LANINS_048", "Vissza az előző oldalra");
define("LANINS_049", "A két, általad beírt jelszó nem azonos. Lépj vissza és próbáld újra.");
define("LANINS_050", "XML Kiterjesztés");
define("LANINS_051", "Telepítve");
define("LANINS_052", "Nincs telepítve");
define("LANINS_053", "e107 .700-hoz szükséges a PHP XML Kiterjesztés telepítése. Lép kapcsolatba a kiszolgálóval vagy olvasd el az erről szóló információkat ");
define("LANINS_054", " mielőtt folytatnád");
define("LANINS_055", "Telepítés visszaigazolása");
define("LANINS_056", "6");
define("LANINS_057", " e107 rendelkezik a telepítéshez szükséges összes információval.

Katt a gombra az adatbázis táblák létrehozásához és a beállítások mentéséhez.

");
define("LANINS_058", "7");
define("LANINS_060", "A sql adat file olvasása sikertelen

Ellenőrizd a <b>core_sql.php</b> file létezését az <b>/e107_admin/sql</b> könyvtárban.");
define("LANINS_061", "Az e107 nem tudta létrehozni az összes, szükséges adatbázis táblát.
Töröld az adatbázist, az ismételt telepítés előtt javítani kell a problémát.");

define("LANINS_062", "[b]Köszöntelek az új weboldaladon![/b]
e107 telepítése sikeres, megkezdheted a tartalom feltöltését.<br />Az adminisztrációs felületed [link=e107_admin/admin.php]itt található[/link], katt a belépéshez. A bejelentkezéshez használd a telepítés folyamán megadott felhasználónevet és jelszót.

[b]Támogatás[/b]
e107 főoldala: [link=http://e107.org]http://e107.org[/link], itt megtalálhatod a FAQ-t és a dokumentációt.
e107 Magyarország főoldala: [link=http://e107hungary.org]http://e107hungary.org[/link], a magyar e107 TEAM oldala.
Source: [link=http://source.e107hungary.org]http://source.e107hungary.org[/link], Plugin-ok, theme-k, módosítások, ...
Fórumok: [link=http://e107hungary.org/e107_plugins/forum/forum.php]http://e107hungary.org/e107_plugins/forum/forum.php[/link]

[b]Letöltések[/b]
Plugin-ok: [link=http://e107coders.org]http://e107coders.org[/link]
Theme-ek: [link=http://e107themes.org]http://e107themes.org[/link]

Köszönjük, hogy kipróbálod az e107-t, bízunk benne, hogy mindent megtalálsz, amire szükséged van.
(Törölheted ezt az üzenetet az admin felületen.)");

define("LANINS_063", "Köszöntelek az e107 rendszerében!");

define("LANINS_069", "e107 sikeresen telepítve!

A biztonság érdekében írd át a <b>e107_config.php</b> file jogosultságát 644-re.

Ezenkívül, töröld az install.php file-t a szerverről a lenti gomb megnyomása után
");
define("LANINS_070", "e107 nem tudta elmenteni a fő beállításokat a szerverre.

Ellenőrizd az <b>e107_config.php</b> file megfelelő jogosultságát");
define("LANINS_071", "Telepítés befejezése");

define("LANINS_072", "Adminisztrátor Felhasználónév");
define("LANINS_073", "Ezzel a névvel tudsz belépni az oldalra. Ha akarod megadhatod ugyanezt a nevet a megjelenítéshez is");
define("LANINS_074", "Adminisztrátor Megjelenő név");
define("LANINS_075", "Az a név, amit a felhasználók látnak a profilodban, fórumokban vagy egyéb helyeken. Használhatod a bejelentkező nevedet is, ebben az esetben hagys üresen.");
define("LANINS_076", "Adminisztrátor Jelszó");
define("LANINS_077", "Írd be az általad használni kívánt jelszót");
define("LANINS_078", "Adminisztrátor Jelszó Megerősítés");
define("LANINS_079", "Írd be újra a jelszót");
define("LANINS_080", "Adminisztrátor Email");
define("LANINS_081", "Írd be az email címedet");

define("LANINS_082", "user@yoursite.com");

// Better table creation error reporting
define("LANINS_083", "MySQL Jelentett hiba:");
define("LANINS_084", "A telepítő nem tud kapcsolódni az adatbázishoz");
define("LANINS_085", "A telepítő nem tudja kiválasztani az adatbázist:");

define("LANINS_086", "Admin Felhasználónév, Admin Jelszó és Admin Email <b>kötelező</b> mezők. Lépj vissza az előző oldalra és írd be a megfelelő információkat.");

define("LANINS_087", "Egyéb");
define("LANINS_088", "Kezdőlap");
define("LANINS_089", "Letöltések");
define("LANINS_090", "Tagok");
define("LANINS_091", "Hír beküldése");
define("LANINS_092", "Kapcsolat");
define("LANINS_093", "Hozzáférés a Privát Üzenetküldés menühöz");
define("LANINS_094", "Privát fórumcsoport példa");
define("LANINS_095", "Integritás ellenörzés");
define("LANINS_096", "");
define("LANINS_097", "");
define("LANINS_098", "");
define("LANINS_099", "");
define("LANINS_100", "");

?>
