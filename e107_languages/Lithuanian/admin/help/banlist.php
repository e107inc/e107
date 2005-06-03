<?php
$caption = "Patekimo į svetainę ribojimas";
$text = "Jūs galite apriboti nepageidaujamų lankytojų patekimą į jūsų svetainę.<br />
Galite uždrausti patekimą lankytojų su konkrečiu IP adresu arba, naudojant pakaitos simbolį (*), uždaryti savo svetainės duris visai eilei lankytojų. You can also enter an email address to stop a user registering as a member on your site.<br /><br />
<b>IP adresų uždraudimas:</b><br />
Įvedus IP adresą 123.123.123.123, lankytojas su šiuo IP adresu nebegalės patekti į jūsų svetainę.<br />
Įvedus IP adresą 123.123.*.*, bus uždraustas patekimas visiems lankytojams, kurių IP adresas prasideda skaičiais 123.123 .<br /><br />
<b>E-pašto adresų uždraudimas:</b><br />
Įvedus e-pašto adresą foo@bar.com, bus neleista prisiregistruoti lankytojams, kurie naudos šį e-pašto adresą registracijos metu.<br />
Įvedus *@bar.com, bus neleista prisiregistruoti visiems lankytojams, registracijos metu naudojantiems e-pašto adresus besibaigiančius @bar.com.";
$ns -> tablerender($caption, $text);
?>