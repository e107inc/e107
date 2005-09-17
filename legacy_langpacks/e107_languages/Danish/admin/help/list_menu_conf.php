<?php
$text = "I denne section kan du konfigurere 3 menuer<br>
<b> Ny Artikel Menu</b> <br>
Skriv et nummer for eksempel '5' i det f&oslash;rste felt for at vise de sidste 5 artikler, lad v&aelig;re tom for at vise alle, Du kan konfigurere hvad titlen p&aring; linket skal v&aelig;re for resten af artiklen i det andet felt, n&aring;r du lader denne sidste mulighed v&aelig;re tom vil der ikke blive skabt et link, for eksempel: 'Alle artikler'<br>
<b> Kommentarer/Forum Menu</b> <br>
Antallet af kommentarer er som standard 5, antallet af tegn er som standard 10000. Det sat s&aring;dan at hvis en linje er for lang vil den besk&aelig;re det til det angivne og vedh&aelig;fte det, til sidst, et godt valg for dette er '...', tjek originale emner hvis du vil vil se dem i oversigten.<br>

";
$ns -> tablerender("Menu Konfiguration Hj&aelig;lp", $text);
?>