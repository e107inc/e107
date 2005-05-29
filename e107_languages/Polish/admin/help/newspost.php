<?php
$caption = "Pomoc: Wiadomości";
$text = "<b>Informacje ogólne</b><br />
Treść zostanie pokazana na stronie podstawowej, rozszerzenie będzie można przeczytać klikając na link 'Czytaj więcej'. ¬ródło i adres URL oznaczają miejsce pochodzenia wiadomości.
<br />
<br />
<b>Skróty</b><br />
Zamiast wypisywać cały znacznik, możesz używać tych skrótów. Podczas wysyłania wiadomości skróty zostaną zamienione na kod zgodny z XHTML.
<br /><br />
<b>Linki</b>
<br />
Używaj pełnych ścieżek dla wszystkich linków, nawet jeśli są one lokalne, w przeciwnym razie nie będą one poprawnie przetworzone.
<br /><br />
<b>Pokaż tylko tytuł</b>
<br />
Zaznacz tę opcję, aby pokazywać na stronie głównej tylko tytuły wiadomości oraz link prowadzący do pełnej treści.
<br /><br />
<b>Stan</b>
<br />
Jeżeli klikniesz na guzik Wyłączone (Disabled), wiadomość nie będzie pokazana w ogóle na stronie startowej.
<br /><br />
<b>Automatyczna aktywacja</b>
<br />
Jeżeli określisz datę początkową i/lub końcową, ta wiadomość będzie pokazywana tylko w czasie pomiędzy tymi datami.";
$ns -> tablerender($caption, $text);
?>
