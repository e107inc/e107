<?php
$text = "Fra denne side kan du lave egne menuer med dit eget indhold.<br /><br /><b>Vigtigt</b> - for at benytte denne funktion skal du CHMOD din /menus mappe til 777.
<br /><br />
<i>Menu Filnavn</i>: Navnet på din menu, menuen vil blive gemt som 'custom_det navn.php' i /menus mappen<br />
<i>Menu Overskrift Titel</i>: Teksten der vises i titel feltet på menuen<br />
<i>Menu Tekst</i>: De data der vises i selve menuen, kan være tekst, billeder osv.";

$ns -> tablerender(CUSLAN_18, $text);
?>