<?php
$text = "În această secţiune puteţi configura 3 meniuri<br>
<b> Meniu Articole noi</b> <br>
Introduceţi un număr, de exemplu '5' în câmp, pentru a afişa prmele 5 articole, lăsaţi necompletat pentru a le vedea pe toate. Configuraţi titlul linkului pentru restul articolelor în cel de-al doilea câmp, dacă lăsaţi această opţiune necompletată, nu se va crea un link, de exemplu: 'Toate articolele'<br>
<b>Meniu Comentarii/Forum</b> <br>
Numărul de comentarii este implicit 5, numarul implicit de caractere este 10000. Postfixul foloseşte atunci când, dacă o linie este prea lungă, o va tăia şi o va anexa la sfârşit, de exemplu '...', verificaţi secţiunile originale dacă vreţi să le vedeţi în ansamblu.<br>

";
$ns -> tablerender("Asistenţă configurare meniu", $text);
?>