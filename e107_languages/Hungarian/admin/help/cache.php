<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Hungarian Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/cache.php,v $
|     $Revision: 1.3 $
|     $Date: 2007-02-18 01:59:51 $
|     $Author: lisa_
+----------------------------------------------------------------------------+
*/
$caption = "Cache súgó";
$text = "A cachelés bekapcsolása nagymértékben gyorsítja az oldalak elérését, és minimalizálja az adatbázis lekérdezéseket.<br /><br />Ha a cache tartalmát fájlba akarod íratni (ezzel tovább csökken az adatbázis lekérdezések száma), a(z) ".$FILES_DIRECTORY."cache könyvtár jogosultsága 777 legyen!<br /><br /><b>FONTOS! Ha épp a saját témádat készíted, akkor kapcsold ki, mert a változtatások nem fognak látszani.</b>";
$ns -> tablerender($caption, $text);
?>
