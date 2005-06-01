<?php
$text = "
<b>&raquo;</b> <u>Plugin Titel:</u>
Titel van de plugin in de menubalk.  Instellen op PMLAN_PM om het taalbestand te gebruiken.
<br /><br />
<b>&raquo;</b> <u>Beperk PM tot:</u>
Hiermee kun je de gebruikersgroep van PM instellen.
<br />Als er nieuwe gebryikersklassen worden toegevoegd, wordt deze lijst automatisch aangevuld.  De 'Everyone' en 
'Members only' opties werken hetzelfde (alleen voor leden dus).
<br /><br />
<b>&raquo;</b> <u>versturen e-mail melding:</u>
Als deze instelling op Ja staat, krijgt de ontvanger een e-mailtje met de boodschap dat er een PM klaar staat.
";
$ns -> tablerender("PM Help", $text);
?>