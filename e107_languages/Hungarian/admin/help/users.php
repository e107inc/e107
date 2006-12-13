<?php
$caption = "Felhasználók súgó";
$text = "Itt a regisztrált felhasználókra vonatkozó beállításokat tudod módosítani. Módosíthatod a beállításaikat, adminisztrátori jogot adhatsz nekik, új csoportba sorolhatod őket, stb...";
$text .= "<br /><br />Ezen kívül e-mailt is küldhetsz nekik, az E-mail küldése opcióval - itt egy adott tagnak nem tudsz e-mailt küldeni, csak egy adott csoportnak, vagy mindenkinek. (A kitiltott, illetve a regisztrációjukat nem megerősített felhasználók kivételével.)";
$ns -> tablerender($caption, $text);
unset($text);
?>
