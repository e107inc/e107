<?php
$text = "Recenziile sunt asemănătoare cu articolele dar vor fi listate în propria secţiune de meniu.<br />
 Pentru o recenzie multi-pagină, separaţi fiecare pagină cu textul [newpage], exemplu <br /><code>Test1 [newpage] Test2</code><br /> va crea o recenzie de două pagini cu 'Test1' pe pagina 1 şi 'Test2' pe pagina 2.";
$ns -> tablerender("Asistenţă recenzii", $text);
?>