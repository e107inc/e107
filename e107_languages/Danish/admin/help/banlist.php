<?php
$caption = "Banne brugere fra dit site";
$text = "Du kan Banne/Bortvise brugere fra dit site, på denne side.<br />
Skriv hele deres IP addresse eller brug et wildcard til at banne en række af IP addresser. Du kan også skrive en e-mail addresse til at forhindre en bruger i at registrere sig som medlem på dit site.<br /><br />
<b>Banne på IP addresse:</b><br />
Ved at skrive IP addressen 123.123.123.123 vil forhindre brugeren med den addresse i at besøge dit site.<br />
Ved at skrive IP addressen 123.123.123.* vil forhindre alle i den IP række/gruppe i at besøge dit site.<br /><br />
<b>Banne på e-mail addresse</b><br />
Ved at skrive e-mail addressen skod@nar.dk vil forhindre alle der bruger den e-mail addresse i at registrere sig som medlem på dit site.<br />
Ved at skrive e-mail addressen *@nar.dk vil forhindre alle der med e-mail fra det domæne i at registrere sig som medlem på dit site.";
$ns -> tablerender($caption, $text);
?>