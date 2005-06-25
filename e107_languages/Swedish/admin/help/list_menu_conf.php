<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Swedish/admin/help/list_menu_conf.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-06-25 11:07:35 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/
$text = "I denna sektion kan du konfigurera 3 menyer<br>
<b>Nya artiklar meny</b> <br>
Skriv in ett nummer, t.ex. '5' i det första fältet för att visa de fem första artiklarna, lämna tomt för att visa alla. Du konfigurerar vad rubriken till länken skall vara för resten av artiklarna i det andra fältet, om du lämnar det sista valet tomt skapa ingen länk, t.ex.: 'Alla artiklar'<br>
<b>Kommentarer/Forum meny</b> <br>
Antalet kommentarer är 5 som standard, antalet tecken är 10000 som standard. Postfixet är till för om en rad är för lång så kommer den att kapas och så läggs postfixet till sist, ett bra val kan vara '...', markera originalämnena om du vill se dessa i översikten.<br>

";
$ns -> tablerender("Hjälp menykonfiguration", $text);

?>
