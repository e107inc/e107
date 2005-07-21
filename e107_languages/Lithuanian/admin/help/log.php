<?php
$text = "Galima aktyvuoti statistiką - bus įrašomi visi prisijungimai prie tinklapio.
Jei neturite serveryje vietos į valias, geriau pažymėkite the domain only box as referer logging,
tai įrašys tik domeinus, bet ne visą url, pvz., 'jalist.com' vietoje 'http://jalist.com/links.php' ";
$ns -> tablerender("Logging Help", $text);
?>