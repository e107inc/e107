<?php
$text = "In dit scherm kun je 3 menus instellen<br>
<b> Nieuwe artikelen Menu</b> <br>
Geef een waarde op, bijv. '5' in het eerste veld, om de eerste 5 artikelen te tonen, vul niets in om alles te zien. Stel in het tweede veld de titel van de link in die naar de rest leidt. Als je dit veld niet invult, wordt geen link gecreëerd, bijv.: 'Alla artikelen'<br>
<b> Commentaar/Forum Menu</b> <br>
De standaard waarde staat op 5 commentaren, het aantal tekens op 10000. De opvultekst geeft aan wat wordt getoond wanneer een tekst wordt afgebroken als de tekst te lang is, een goede keuze is '...', controleer de originele onderwerpen als je die in het overzicht wilt zien.<br>

";
$ns -> tablerender("Menu Configuratie Hulp", $text);
?>
