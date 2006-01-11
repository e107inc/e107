<?php

if (!defined('e107_INIT')) { exit; }

$caption = "Caching";
$text = "Als u caching aanzet, zal de snelheid van uw site aanzienlijk toenemen en de hoeveelheid database aanroepen sterk verminderen.<br /><br /><b>LET OP! Als u uw eigen Thema maakt, zet dan caching uit, omdat anders de wijzigingen niet worden getoond.</b>";
$ns -> tablerender($caption, $text);
?>