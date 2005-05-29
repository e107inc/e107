<?php
$text = "Z poziomu tej strony możesz dodawać pojedyncze strony artykułów lub kilkustronicowe artykuły.<br />W kilkustronicowych artykułach, strony dzielisz tekstem [newpage], np. <br /><code>Test1 [newpage] Test2</code><br /> tworzy dwie strony artykułu o zawartości &quot;Test1&quot; na stronie 1 i &quot;Test2&quot' na stronie drugiej.<br /><br />
Jeżeli w twoim artykule znajdują się znaki HTML i chcesz je zachować, wpisz kod pomiędzy [preserve] [/preserve]. Przykład: chcesz wstawić tekst &quot;&lt;table>&lt;tr>&lt;td>Cześć &lt;/td>&lt;/tr>&lt;/table>&quot; do twojego artykułu, kod tabeli zostanie ukryty, a zostanie wyświetlone tylko słowo &quot;Cześć&quot;. Jeżeli napiszesz &quot;[preserve]&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>[/preserve]&quot; kod całej tabeli zostanie wyświetlony jako zwykły tekst. ";

$ns -> tablerender("Pomoc: Artykuły", $text);
?>
