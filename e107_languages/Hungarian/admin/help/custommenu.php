<?php
$text = "Itt saját menüket és/vagy oldalakat tudsz létrehozni.<br /><br /><b>Fontos</b> - e lehetőség használatához a(z) ".$PLUGINS_DIRECTORY.", a(z) ".$PLUGINS_DIRECTORY."custom/ illetve a(z) ".$PLUGINS_DIRECTORY."custompages/ könyvtár jogosultságait 777-re kell állítanod (CHMOD).
<br /><br />
<i>Fájlnév</i>: Az egyéni menüd/oldalad neve, a menü/oldal 'custom_ezaneve.php' néven lesz elmentve a /custom, vagy /custompages könyvtárba<br />
<i>Fejléc (címsor)</i>: Ez a szöveg fog megjelenni a menü/oldal címsorában.<br />
<i>Szöveg</i>: Az aktuális adat, ami a menü/oldal törzsében lesz megjelenítve, ez lehet szöveg, kép, stb.";

$ns -> tablerender(CUSLAN_18, $text);
?>
