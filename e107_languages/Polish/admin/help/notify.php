<?php
$text = "Na tej stronie ,ustawiasz wszystkie parametry oraz zdarzenia, po których zostanie wysłany email do osób lub na podany adres, o zmianach w serwisie.<br /><br />
Na przykład, ustaw w 'Zablokowany adres IP' do klasy 'Tylko Administatorzy' a wszyscy admini otrzymają email gdy strona zostanie zablokowana.<br /><br />
Możesz również, dla kolejnego przykładu, ustawić w 'Pozycja nowości nadesłana przez admina' dla klasy 'Tylko Zarejestrowani' , wtedy wszyscy użytkownicy serwisu otrzymają stosowny o tym fakcie list.<br /><br />
Jeżeli chcesz aby email został wysłany na inny adres - zaznacz 'Email' i wpisz jego adres w pole.";

$ns -> tablerender("Pomoc do Powiadomienia", $text);
?>
