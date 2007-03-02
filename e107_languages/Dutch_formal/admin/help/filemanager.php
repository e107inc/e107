<?php

if (!defined('e107_INIT')) { exit; }

$text = "In dit scherm kunt u de bestanden in de map /files beheren. Als u bij uploaden foutmeldingen over onvoldoende rechten ziet, CHMOD de map waarin u bestanden wilt plaatsen naar 777.";
$ns -> tablerender("Bestandsbeheer Hulp", $text);
?>