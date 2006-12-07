<?php
if (!defined('e107_INIT')) { exit; }

$caption = "Admin súgó";
$text = "Itt hozhatsz létre új adminisztrátorokat, törölheted a meglévőket. Az adminisztrátorok csak azokkal a jogokkal fognak rendelkezni, amiket itt beállítasz.<br /><br />
Új admin létrehozásához lépj a Felhasználók oldalra és írd felül a meglévő Tag admin státuszát.";
$ns -> tablerender($caption, $text);
?>