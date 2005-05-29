<?php
$caption = "Foren Hilfe";
$text = "<b>Allgemeines</b><br />
Nutzen Sie dieses Menü um Foren zu editieren oder zu erstellen.<br />
<br />
<b>Haupt- bzw. Ursprungskategorie/Foren</b><br />
EIne Ursprungskategorie ist eine Überschrift unter der die zum Thema gehörigen Foren gruppiert sind. Dadurch wird die Darstellung und die Navigation fü die Nutzer einfacher.
<br /><br />
<b>Erreichbarkeit</b>
<br /> 
Sie können Foren für bestimmte Benutzerklassen erstellen, die dann nur für diese erreichbar sind. Dazu müssen Sie die Klassen vergeben. Wenn Sie die Klassen Vegeben haben, sehen nur diese das für sie zungängliche Forum. Das Gleiche gilt auch für Haupt- bzw. Ursprungskategorien.
<br /><br />
<b>Moderatoren</b>
<br />
Wählen Sie die Namen der Administratoren, denen Sie den Forum Moderator Status erteilen wollen. Die Moderatoren müssen vorher den Status 'Forum Moderator' erhalten, um hier aufgelistet zu sein! Diese Einstellung nehemen Sie über das Menü Administratoren vor.
<br /><br />
<b>Benutzer Ranking</b>
<br />
Ebfalls können Sie über dieses Menü die Benutzer Rankings verwalten. Wenn die Bild Felder ausgewählt sind, werden die Bilder genutzt. Um die Raking Namen zu nutzen, lassen Sie das Feld Ranking Bild leer.<br /> Der Schwellenwert ist die Anzahl von Punkten, die ein User benötigt, um in den nästen Rang hochgestuft zu werden.";
$ns -> tablerender($caption, $text);
unset($text);
?>
