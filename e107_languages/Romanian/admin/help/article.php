<?php
$text = "De pe această pagină puteţi adăuga articole pe o singură pagină sau pe mai multe.<br />
 Pentru articole multi-pagină, separaţi fiecare pagină cu textul [newpage], exemplu <br /><code>Test1 [newpage] Test2</code><br /> va crea un articol pe 2 pagini cu  'Test1' pe pagina 1 şi 'Test2' pe pagina 2.
<br /><br />
dacă articolul dumneavoastră conţine taguri HTML pe care doriţi să le păstraţi , închideţi codul între [html] [/html]. De exemplu, dacă aţi introdus textul '&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>' în articolul dumneavoastră, va fi afişat un tabel conţinând cuvântul hello. Dacă aţi introdus '[html]&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>[/html]' codul pe care l-aţi introdus va fi afişat şi nu tabelul pe care îl generează codul.";
$ns -> tablerender("Asistenţă articole", $text);
?>