<?php
$text = "Apžvalgos panašios į straipsnus, tik jos pateikiamos savo atskirame meniu.<br />
 Norėdami turėti apžvalgą iš daugelio puslapių, kiekvieną puslapį atskirkite tekstu [newpage], pvz., <br /><code>Testas1 [newpage] 
 Testas2</code><br />. Taip bus sukurti du puslapiai su tekstu 'Testas1' I-me puslapyje bei 'Testas2' II-me puslapyje.";
$ns -> tablerender("Review Help", $text);
?>