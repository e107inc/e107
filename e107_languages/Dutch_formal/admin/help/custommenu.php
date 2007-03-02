<?php

if (!defined('e107_INIT')) { exit; }

$text = "In dit scherm kunt u maatwerk menu's met uw eigen inhoud maken. <br /><br /><b>Let op</b> - om deze functie te kunnen gebruiken moet u de rechten op de map /menus instellen op 777.
<br /><br />
<i>Menu Bestandsnaam</i>: De naam van uw Maatwerk menu bestand, het menu wordt opgeslagen als 'custom_dezenaam.php' in de map /menus<br />
<i>Menu Titel</i>: De tekst die in de titelbalk van het menu wordt getoond<br />
<i>Menu Tekst</i>: De gegevens die feitelijk worden getoond in het menublok. Dat kan tekst zijn, of plaatjes enz.";

$ns -> tablerender("Maatwerk menu's", $text);
?>