<?php

if (!defined('e107_INIT')) { exit; }

$text = "Upload uw bestanden naar de ".e_FILE."downloads map, de afbeeldingen naar de ".e_FILE."downloadimages map en de thumbnail afbeeldingen naar de ".e_FILE."downloadthumbs map.
<br /><br />
Om een download aan te melden moet u eerst een ouder creëren en daarna een categorie onder deze ouder. Daarna kunt u de download beschikbaar stellen.";
$ns -> tablerender("Download Help", $text);

?>