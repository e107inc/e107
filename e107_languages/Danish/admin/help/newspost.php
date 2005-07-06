<?php
$caption = "Nyheds indlæg Hjælp";
$text = "<b>Generelt</b><br />
Tekst vil blive vist på hoved siden, udvidet vil blive vist ved at klikke på et 'Læs mere' link. Kilde og URL er hvor du har historien fra.
<br />
<br />
<b>Genveje</b><br />
Du kan bruge disse genveje istedet for at skrive hele HTML tags, ved oprettelse af nyheder vil genvejene blive konverteret til xhtml gyldig kode.
<br /><br />
<b>Links</b>
<br />
Skriv hele stien til alle links selvom de er lokale eller til et andet site ellers bliver de muligvis ikke blive vist korrekt.
<br /><br />
<b>Vis kun titel</b>
<br />
gør det muligt at kun vise titlen fra en nyhed på forsiden, med et klikbart link til hele nyheden.
<br /><br />
<b>Status</b>
<br />
Hvis du klikker på slået fra knappen vil nyheden overhovedet ikke blive vist på din forside.
<br /><br />
<b>Aktivering</b>
<br />
Hvis du ønsker at sætte en start og/eller slut dato vil din nyhed kun blive vis i det angivne tidsrum.
";
$ns -> tablerender($caption, $text);
?>