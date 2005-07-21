<?php
$text = "Notify sends email notifications when e107 events occur.<br /><br />
Pvyzdžiui, pasirinkus 'IP banned for flooding site'  vartotojų klasei'Admin' bei visiems administratoriams bus išsiųstas pranešimas
 kai tinklapis yra apkraunamas.<br /><br />
Arba, pasirinkus 'News item posted by admin' vartotojų klasei 'Members' ir visiems kitiems nariams e. paštu bus išsiųsta naujiena,
kurią js parašėte.<br /><br />
Jei norite, kad pranešimasbūtų pasiųstas kitu e. pašto adresu, pasirinkite 'Email' parinktį
ir įrašykite pageidaujamą e. pašto adresą.";

$ns -> tablerender("Notify Help", $text);
?>