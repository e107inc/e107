<?php
$text = "W tej sekcji możesz konfigurować 3 menu<br>
<b> Menu nowych Artykułów</b> <br>
Wpisz jakąś liczbę, na przykład '5' w pierwszym polu, aby pokazać pierwszych 5 artykułów, pozostaw to pole puste, aby pokazać wszystkie artykuły. Możesz określić jaki ma być tytuł linku do pozostałych artykułów w drugim polu, jeśli pozostawisz to pole puste, ten link nie zostanie wygenerowany. Na przykład: 'Pozostałe artykuły'<br>
<b> Menu Komentarzy/Dyskusji</b> <br>
Domniemana wartość liczby komentarzy to 5, a liczby znaków to 10000. Przyrostek: jeśli linia jest zbyt długa, zostanie ucięta, a na końcu będzie dodany przyrostek, może to być np. '...'; zaznacz w kratce 'Dotyczy materiału', jeśli chcesz umieścić go w podsumowaniu.<br>";
$ns -> tablerender("Pomoc: Menu Konfiguracji", $text);
?>
