<?php

if (!defined('e107_INIT')) { exit; }

$caption = "Forum Hulp";
$text = "<b>Algemeen</b><br />
Gebruik dit scherm om forums te maken of bewerken<br />
<br />
<b>Hoofdonderwerpen/Forums</b><br />
Een hoofdonderwerp is de onderverdeling in onderwerpen waaronder de forums worden gegroepeerd. Dit verbetert het overzicht binnen uw forums, waardoor bezoekers eerder de weg vinden.
<br /><br />
<b>Toegankelijkheid</b>
<br />
U kunt forums openstellen voor bepaalde groepen gebruikers. Als u eenmaal de 'Klasse' van de bezoekers hebt ingesteld, kun u de gebruikersklasse die toegang mag hebben selecteren. U kunt hoofdonderwerpen en forums op deze wijze afschermen.
<br /><br />
<b>Moderators</b>
<br />
Kruis de namen van de getoonde beheerder aan om ze moderator van een forum te maken. De beheerder moet wel eerst moderator rechten hebben gekregen in het Beheerdersscherm.
";
$ns -> tablerender($caption, $text);
unset($text);
?>