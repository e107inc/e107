<?php

if (!defined('e107_INIT')) { exit; }

$text = "Als je MySql server versie dit ondersteunt, kun je gebruik maken van de MySql sorteer methode die aanzienlijk sneller is dan de PHP methode. Zie voorkeuren.<br /><br />
Als je site karaktergeoriënteerde talen zoals Chinees en Japans gebruikt, moet je de PHP sorteermethode gebruiken en de Zoek hele woord functie uitschakelen.";
$ns -> tablerender("Search Help", $text);
?>