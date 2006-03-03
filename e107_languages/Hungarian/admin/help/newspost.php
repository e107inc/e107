<?php
$caption = "Új hír súgó";
$text = "<b>Általános</b><br />
A törzs a főoldalon fog megjelenni, míg a bővített a 'Tovább' linkre kattintva lesz olvasható.
<br />
A híreket kategóriákhoz rendelheted, így lehetővé téve a látogatók számára egy adott kategória összes hírének megjelenítését. <br />Töltsd fel a kategóriához tartozó képet a(z) ".$THEMES_DIRECTORY."-saját_téma-/images/ vagy a(z) ".$IMAGES_DIRECTORY."newsicons/ könyvtárba.
<br /><br />
<b>HTML gombok</b><br />
Írás közben ezeket a HTML gombokat használhatod, a küldéskor xhtml kompatibilis kóddá konvertálódik.
<br /><br />
<b>Linkek</b>
<br />
Mindenképpen abszolút módon add meg az elérési útvonalakat (azaz teljes webcímmel), még akkor is, ha helyi fájlokra hivatkozol. Enélkül esetleg hibásan jelennek meg. Ez alól kivétel: a Feltöltés opción keresztül feltöltött kép, vagy más fájl - ezek elérési útját ugyanis a rendszer automatikusan kezeli.
<br /><br />
<b>Csak a cím mutatása</b>
<br />
Ennek engedélyezésekor a főoldalon csak a hír címe jelenik meg,egy kattintható linkkel a teljes szöveghez.
<br /><br />
<b>Státusz</b>
<br />
Ha a Kikapcsolt opciót választod, akkor a cikk nem fog megjelenni a főoldalon.
<br /><br />
<b>Aktiválás</b>
<br />
Ha megadsz egy kezdési és/vagy befejezési dátumot, akkor a cikk csak a két időpont közt jelenik meg.
";
$ns -> tablerender($caption, $text);
?>
