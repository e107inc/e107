<?php

if (!defined('e107_INIT')) { exit; }

$text = "In dit scherm kunt u 3 menus instellen<br>
<b> Nieuwe artikelen Menu</b> <br>
Geef een waarde op, bijv. '5' in het eerste veld, om de eerste 5 artikelen te tonen, vul niets in om alles te zien. Stel in het tweede veld de titel van de link in die naar de rest leidt. Als u dit veld niet invult, wordt geen link gecreëerd, bijv.: 'Alle artikelen'<br>
<b> Commentaar/Forum Menu</b> <br>
De standaardwaarde staat op 5 commentaren, het aantal tekens op 10000. De opvultekst geeft aan wat wordt getoond wanneer een tekst wordt afgebroken als de tekst te lang is, een goede keuze is '...', controleer de originele onderwerpen als u die in het overzicht wilt zien.<br>

";
$ns -> tablerender("Menu Configuratie Hulp", $text);
?>
