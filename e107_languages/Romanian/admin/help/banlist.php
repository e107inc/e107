<?php
$caption = "Restricţionarea utilizatorilor de pe site";
$text = "Puteţi restricţiona utilizatorii pe site în acest ecran.<br />
Introduceţi ori întregul IP sau folosiţi un * pentru a restricţiona o arie de IP-uri. De asemenea, puteţi introduce o adresă de e-mail pentru a împiedica un utilizator să se înregistreze ca membru pe site-ul dumneavoastră.<br /><br />
<b>Restricţionare după adresa IP:</b><br />
Prin introducerea adresei IP 123.123.123.123 nu veţi permite utilizatorului cu această adresă să vă viziteze site-ul.<br />
Prin introducerea adresei IP 123.123.123.* nu veţi permite nimănui din această zonă de IP-uri să vă viziteze site-ul.<br /><br />
<b>Restricţionare după adresa de e-mail</b><br />
Prin introducerea adresei de e-mail foo@bar.com veţi opri pe orice utilizator al adrsei să se înregistreze ca membru pe site.<br />
Prin introducerea adresei de e-mail *@bar.com veţi opri pe orice utilizator al domeniului respectiv să se înregistreze ca membru pe site.";
$ns -> tablerender($caption, $text);
?>