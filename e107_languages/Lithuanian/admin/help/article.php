<?php
$text = "Šiame lape jus galite sukurti straipsnius iš vieno ar daugiau puslapių.<br />
 Kuriant straipsnį iš daugiau nei vieno puslapio, kiekvienas puslapis atskiriamas tekstu [newpage], pvz., <br /><code>Testas1 [newpage] Testas2</code><br /> sukrs du straipsnio puslapius su 'Testas1' pirmame puslapyje ir 'Testas2' - antrame.
<br /><br />
Jei jūsų straipsnyje yra HTML žymos (tags), kurias jūs norite išsaugoti, įkelkite šį kodą į [html] [/html]. Pavyzdžiui, jei parašėte tekstą tekstą '&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>' straipsnyje, bus sukurta lentelė su žodžiu hello. O jei įrašysite '[html]&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>[/html]' pamatysite tokį kodą, kokį įrašėte, o ne lentelę.";
$ns -> tablerender("Article Help", $text);
?>