<?php
$text = "Możesz pobierać i przetwarzać wiadomości zdalne ze źródeł RSS w innych serwisach oraz udostępniać je na swoich stronach.<br />Wpisz pełną ścieżkę (URL) do źródła wiadomości (np. http://e107.org/news.xml). Jeżeli źródło RSS, z którego korzystasz podaje ścieżkę do guzika z linkiem, a Ty chcesz go pokazać, pozostaw pole obrazu puste, w przeciwnym razie wpisz ścieżkę do obrazu, lub też wpisz 'none', aby nie pokazywać żadnego obrazu. Następnie pozaznaczaj w kratkach co dokładnie chcesz, aby się pojawiło w Twoim menu z nagłówkami wiadomości. Możesz dołączać i odłączać źródło w przypadku gdy np. serwis źródłowy jest niedostępny.<br /><br />Aby zobaczyć nagłówki z własnego serwisu, upewnij się, że headlines_menu jest włączone w Twoim panelu Menu.";

$ns -> tablerender("Pomoc: Nagłówki Wiadomości", $text);
?>
