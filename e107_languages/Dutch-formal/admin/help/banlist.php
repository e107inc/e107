<?php

if (!defined('e107_INIT')) { exit; }

$caption = "Blokkeer toegang tot uw site";
$text = "U kunt in dit scherm gebruikers de toegang tot uw site ontzeggen.<br />
Dat kan ofwel met volledige IP-adres of met jokertekens om een hele serie IP-adressen te blokkeren. U kunt ook een e-mailadres opgeven om te verhinderen dat iemand zich aanmeldt als lid.<br /><br />
<b>Blokkeren op IP adres:</b><br />
Het invoeren van het IP adres 123.123.123.123 blokkeert toegang tot uw site vanaf dat ip-adres.<br />
Het invoeren van het ip adres 123.123.123.* verhindert toegang tot uw site vanaf deze hele serie adressen.<br /><br />
<b>Blokkeren van e-mailadressen</b><br />
Het invoeren van het e-mailadres foo@bar.com verhindert dat iemand zich met dat e-mailadres aanmeldt op uw site.<br />
Het invoeren van het e-mailadres *@bar.com voorkomt dat iemand met een e-mailadres van dat domein aanmeldt.";
$ns -> tablerender($caption, $text);
?>