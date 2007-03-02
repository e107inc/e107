<?php

if (!defined('e107_INIT')) { exit; }

$caption = "Beheerder menu Hulp";
$text = "Gebruik dit scherm om nieuwe beheerders toe te voegen, of om beheerdersrechten in te trekken. Een beheerder heeft uitsluitend toegang tot de aangekruiste functies.<br /><br />
Om een nieuwe beheerder aan te stellen, moet u naar de gebruikerbeheer pagina gaan en een bestaande gebruiker de beheerdersstatus toekennen.";
$ns -> tablerender($caption, $text);
?>