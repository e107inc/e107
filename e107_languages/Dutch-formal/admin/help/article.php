<?php

if (!defined('e107_INIT')) { exit; }

$text = "In dit scherm kunt u artikelen van één of meer pagina's opvoeren.<br />
 Voor een artikele van meerdere pagina's kunt u pagina's scheiden door het tussenplaatsen van [newpage], bijv. <br /><code>Test1 [newline] Test2</code><br /> zou resulteren in een artikel van twee pagina's met 'Test1' op pagina 1 en 'Test2' op pagina 2.
<br /><br />
Als het artikel HTML tags bevat die bewaard moeten blijven, plaats die code dan tussen [preserve] [/preserve]. Als u bijvoorbeeld de tekst '&lt;table>&lt;tr>&lt;td>Hallo &lt;/td>&lt;/tr>&lt;/table>' in uw artikel opneemt, zou een tabel met het woord 'hallo' worden getoond. <br />Als u '[preserve]&lt;table>&lt;tr>&lt;td>Hallo &lt;/td>&lt;/tr>&lt;/table>[/preserve]' had ingevoerd, dan zou de ingevoerde code worden getoond en niet de tabel die de code zou genereren.";
$ns -> tablerender("Artikel Hulp", $text);
?>