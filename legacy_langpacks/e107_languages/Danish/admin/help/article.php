<?php
$text = "Fra denne side kan du tilf&oslash;je enkelt eller multi-side artikler.<br />
 For en a multi-side artikel indel hver side med teksten [newpage], eks. <br /><code>Test1 [newpage] Test2</code><br /> vil lave en to sidet artikel med 'Test1' p&aring; side 1 og 'Test2' p&aring; side 2.
<br /><br />
Hvis din artikel indeholder HTML tags du &oslash;nsker at beholde, omkrans da koden med [html] [/html]. For eksempel, hvis du skrev teksten '&lt;table>&lt;tr>&lt;td>Hejsa &lt;/td>&lt;/tr>&lt;/table>' i din artikel, en tabel indeholdende ordet Hejsa vil blive vist. Hvis du skrev '[html]&lt;table>&lt;tr>&lt;td>Hejsa &lt;/td>&lt;/tr>&lt;/table>[/html]' vil kode du skrev blive vist og ikke tabellen koden gennererede.";
$ns -> tablerender("Artikel Hj&aelig;lp", $text);
?>