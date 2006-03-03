<?php
$caption = "Fájlkezelő súgó";
$text = "Innét lehetőséged van a(z) ".$FILES_DIRECTORY.", a(z) ".$PLUGINS_DIRECTORY."custom és a(z) ".$PLUGINS_DIRECTORY."custompages könyvtárakban lévő állományok kezelésére. Ha feltöltés közben jogosultsággal kapcsolatos hibaüzeneteket kapsz, akkor adj 777 -es jogosultságot a célmappára.";
$ns -> tablerender($caption, $text);
?>
