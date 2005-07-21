<?php
$text = "Jūs galite surinkti ir pateikti kitų tinklapių RSS naujienas savama tinklapyje.<br />
Įrašykite visą URL adresą (pvz., http://e107.org/news.xml). Taip pat galite įrašyti kelią iki vaizdinio, jei jums nepatina
numatytasisarba jis iš viso nėranumatytas. Taip pat galite aktyvuoti arba deaktyvuoti naujienas jei tinklais neveikia.
<br /><br />Norėdami matyti naujienų antraštes,įsitikinkite, kad headlines_menu jūsų menių pslapyje yra aktyvuota.";

$ns -> tablerender("Headlines", $text);
?>