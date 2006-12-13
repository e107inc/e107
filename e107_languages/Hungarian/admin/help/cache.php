<?php
$caption = "Cache súgó";
$text = "A cachelés bekapcsolása nagymértékben gyorsítja az oldalak elérését, és minimalizálja az adatbázis lekérdezéseket.<br /><br />Ha a cache tartalmát fájlba akarod íratni (ezzel tovább csökken az adatbázis lekérdezések száma), a(z) ".$FILES_DIRECTORY."cache könyvtár jogosultsága 777 legyen!<br /><br /><b>FONTOS! Ha épp a saját témádat készíted, akkor kapcsold ki, mert a változtatások nem fognak látszani.</b>";
$ns -> tablerender($caption, $text);
?>
