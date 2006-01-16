<?php

if (!defined('e107_INIT')) { exit; }

$text = "Als uw MySql server versie dit ondersteunt, kunt u gebruik maken van de MySql sorteer methode die aanzienlijk sneller is dan de PHP methode. Zie voorkeuren.<br /><br />
Als uw site karaktergeoriënteerde talen zoals Chinees en Japans gebruikt, moet u de PHP sorteermethode gebruiken en de Zoek hele woord functie uitschakelen.";
$ns -> tablerender("Search Help", $text);
?>