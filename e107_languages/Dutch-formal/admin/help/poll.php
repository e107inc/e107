<?php

if (!defined('e107_INIT')) { exit; }

$text = "U kunt peilingen/enquetes in dit scherm instellen. Voer gewoon de titel en opties van de peiling in, bekijk het resultaat en als het goed lijkt, kruis dan een Actief veldje aan.<br /><br />
Om de peiling te zien, moet u het poll_menu in het menu scherm activeren.";

$ns -> tablerender("Peilingen", $text);
?>