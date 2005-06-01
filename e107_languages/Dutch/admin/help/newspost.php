<?php
$caption = "Nieuwsberichten hulp";
$text = "<b>Algemeen</b><br />
De tekst wordt getoond op de hoofdpagina, uitgebreide tekst wordt getoond door op de 'Lees verder' link te klikken. Herkomst en URL verwijst naar de bron waar het bericht vandaan komt.
<br />
<br />
<b>Shortcuts</b><br />
Je kunt deze shortcuts gebruiken in plaats van het invoeren van de volledige tags, bij het verwerken van de berichten worden de shortcuts geconverteerd naar xhtml compliant code.
<br /><br />
<b>Links</b>
<br />
Gebruik het volledige pad naar links, zelfs al wordt verwezen naar lokale bestanden, omdat ze anders niet goed verwerkt worden.
<br /><br />
<b>Toon alleen de titel</b>
<br />
Activeer deze optie om alleen de titel weer te geven. Klikken op de titel leidt vervolgens naar de tekst.
<br /><br />
<b>Status</b>
<br />
Als de op de Deactiveren knop drukt, wordt het bericht niet getoond.
<br /><br />
<b>Activering</b>
<br />
Als je de start en/of einddatum invoert, wordt het bericht alleen tussen deze data weergegeven.
";
$ns -> tablerender($caption, $text);
?>