<?php
$text = "I denne section kan du konfigurere 3 menuer<br>
<b> Ny Artikel Menu</b> <br>
Skriv et nummer for eksempel '5' i det første felt for at vise de sidste 5 artikler, lad være tom for at vise alle, Du kan konfigurere hvad titlen på linket skal være for resten af artiklen i det andet felt, når du lader denne sidste mulighed være tom vil der ikke blive skabt et link, for eksempel: 'Alle artikler'<br>
<b> Kommentarer/Forum Menu</b> <br>
Antallet af kommentarer er som standard 5, antallet af tegn er som standard 10000. Det sat sådan at hvis en linje er for lang vil den beskære det til det angivne og vedhæfte det, til sidst, et godt valg for dette er '...', tjek originale emner hvis du vil vil se dem i oversigten.<br>

";
$ns -> tablerender("Menu Konfiguration Hjælp", $text);
?>