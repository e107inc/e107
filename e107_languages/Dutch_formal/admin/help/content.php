<?php

if (!defined('e107_INIT')) { exit; }


$text = "U kunt hier een normale pagina toevoegen aan uw site. Er wordt automatisch een link in het hoofdmenu opgenomen. Als u bijvoorbeeld een pagina met de Link Naam 'Test' opgeeft, wordt een link met de naam 'Test' aan de Links sectie toegevoegd.<br />
Als u wilt dat er een titel wordt getoond, geef die dan op in het koptekst veld.";
$ns -> tablerender("Inhoud Hulp", $text);
?>