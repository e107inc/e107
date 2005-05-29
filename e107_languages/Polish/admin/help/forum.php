<?php
$caption = "Pomoc: Dyskusje";
$text = "<b>Ogólne</b><br />
Na tej stronie możesz tworzyć i redagować dyskusje<br />
<br />
<b>Działy i Dyskusje</b><br />
Dział jest to nagłówek, pod którym występują inne dyskusje, dzięki czemu układ jest bardziej przejrzysty, a nawigacja prostsza.
<br /><br />
<b>Dostępność</b>
<br />
Możesz nakazać, aby dyskusje były dostępne tylko dla niektórych użytkowników. Kiedy określisz 'klasę' użytkowników, możesz kliknąć 
na tę klasę, aby tylko użytkownicy z tej klasy mieli dostęp do dyskusji. Ten sposób działa zarówno dla działów, jak i dyskusji.
<br /><br />
<b>Moderatorzy</b>
<br />
Zaznacz nazwy na liście administratorów, aby nadać im uprawnienia moderatorów tej dyskusji. Tylko administratorzy z uprawnieniami moderatorów znajdują się na tej liście.
<br /><br />
<b>Rangi</b>
<br />
Tutaj możesz ustawić rangi dla swoich użytkowników. Jeśli pola od obrazu są wypełnione, zostaną użyte obrazy. Jeżeli chcesz użyć rangi w formie nazwy musisz pozostawić puste pole obrazu, nazwa zostanie tam umieszczona.<br />Progiem zmiany rangi na poziom wyższy jest uzyskanie maksymalnej ilości punktów dla rangi, którą obecnie posiada. Ilość punktów w danej randze określa poziom zmiany na rangę wyżej.";
$ns -> tablerender($caption, $text);
unset($text);
?>
