<?php
$caption = "User von Ihrer Seite verbannen";
$text = "Über dieses Menü können Sie User von Ihrer Seite verbannen.<br />
Entweder geben Sie die vollständige IP-Adresse an oder nutzen eine sogenannte Wildcard, um eine Reihe von IP-Adressen zu verbannen. Oder Sie können die E-Mail Adresse angeben, um User davon abzuhalten, sie auf Ihrer Seite zu registrieren.<br /><br />
<b>Verbannen über IP-Addresse:</b><br />
Wenn Sie z.B. die IP-Addresse 123.123.123.123 eingeben, werden die user mit der genannten IP gesperrt.<br />
Wenn Sie jetzt aber die IP-Adresse 123.123.123.* eingeben, dann werden alle User, mit der Endung 000 bis 999 der IP Adresse gesperrt. Also eine ganze Reihe verschiedener User.<br /><br />
<b>Verbanung via E-Mail Adresse</b><br />
Geben Sie die Adresse mane@email.de ein wird jeder, der die genannte E-Mail verwendet gesperrt und kann sich nicht registrieren. Das ist i.d.R. nur eine Person, bzw. ein Nutzerkreis, der Zugang zu den Registrierungsdaten hat.<br />
Geben Sie jetzt *@email.de ein, dann kann NIEMAND der seine E-Mail bei der genannten Domain hat sich registrieren! Bitte seien Sie deshalb umsichtig im Umgang mit den Sperrcodes.";
$ns -> tablerender($caption, $text);
?>
