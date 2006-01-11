<?php

if (!defined('e107_INIT')) { exit; }

$caption = "Gebruikersklasse Hulp";
$text = "U kunt op deze pagina gebruikersgroepen (klassen) aanmaken, bewerken of verwijderen.<br />Dit is nuttig om bepaalde onderdelen van uw site af te schermen van de reguliere gebruikers. U kunt bijvoorbeeld een TEST-klasse maken en daarna een forum maken waarvoor alleen gebruikers die lid zijn van de TEST-klasse toegang krijgen.<br /> Door het gebruik van Klassen kunt u een Alleen-voor-leden onderdeel van uw site maken.";
$ns -> tablerender($caption, $text);
?>