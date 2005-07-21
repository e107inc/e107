<?php
$text = "In this section you can configure 3 menus<br>
<b> New Articles Menu</b> <br>
Įrašykite skeitmenį,  pvz.,'5' pirmame laukelyje jei norite, kad rodytų 5 straipsnius. Jei paliksite tuščią -matysite tik
 visus straipsnius.Antrame laukelyje įrašykite nuorodos, kuri ves į kitus straipsnius. Jei šio lauko neužpildysite,
 , nuoroda nebus sukurta<br>
<b> Comments/Forum Menu</b> <br>
Pagal numatymą komentarų rodoma 5, o ženklų kiekis - iki 10000. Postfix reikalingas tam, jei eilutė yra ilga
is for if a line is too long it will cut it off and append this postfix to the end, a good choice for this is '...', check original topics if you want to see those in the overview.<br>

";
$ns -> tablerender("Menu Configuration Help", $text);
?>
