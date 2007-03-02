<?php

if (!defined('e107_INIT')) { exit; }

$text = "Als u e107 upgrade, of als uw site even uit de lucht moet, kruis dan de onderhoudsvlag aan en uw bezoekers worden geleid naar een pagina waarin wordt uitgelegd dat de site even uit de lucht is voor onderhoudswerkzaamheden. Als u klaar bent, kruis het vakje uit om de site weer beschikbaar te stellen.";

$ns -> tablerender("Onderhoud hulp", $text);
?>