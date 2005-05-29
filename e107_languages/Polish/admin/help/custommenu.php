<?php
$text = "Na tej stronie możesz tworzyć własne menu, posiadające własną zawartość.<br /><br /><b>Uwaga</b> - aby korzystać z tej możliwości musisz ustawić uprawnienia (CHMOD) do katalogu /menus na wartość 777.
<br /><br />
<i>Nazwa pliku Menu</i>: Nazwa własnego Menu, które zostanie zapisane jako plik 'custom_ta nazwa.php' w katalogu /menus<br />
<i>Tytuł Menu</i>: Tekst pokazywany w nagłówku okienka tego Menu<br />
<i>Tekst Menu</i>: Sama zawartość pokazywana w okienku Menu, może to być tekst, obrazy itd.";

$ns -> tablerender(CUSLAN_18, $text);
?>
