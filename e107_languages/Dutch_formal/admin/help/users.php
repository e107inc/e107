<?php

if (!defined('e107_INIT')) { exit; }

$text = "Met dit scherm kunt u de geregistreerde leden van uw site beheren. U kunt o.a. hun instellingen wijzigen, hen beheerdersrechten geven en hun gebruikersklasse instellen.";
$ns -> tablerender("Gebruikers Hulp", $text);
unset($text);
?>