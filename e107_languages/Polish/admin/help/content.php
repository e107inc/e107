<?php

$text = "Przy pomocy tej funkcji możesz dodawać do serwisu zwykłe strony. Link do takiej strony zostanie wygenerowany w głównym menu serwisu. Na przykład jeśli utworzysz nową stronę, której Nazwa linku będzie 'Test', po wysłaniu tej strony link o nazwie 'Test' pojawi się w głównym menu.<br />
Jeśli wprowadzana strona redakcyjna ma mieć nagłówek, wpisz go w pole 'Nagłówek strony'.";
$ns -> tablerender("Pomoc: Redakcyjne", $text);
?>