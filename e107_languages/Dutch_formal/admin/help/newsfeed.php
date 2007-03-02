<?php

if (!defined('e107_INIT')) { exit; }

$text = "U kunt nieuws ophalen van andere sites en weergeven op uw site, door gebruik te maken van de backend RSS nieuwsbronnen van die andere sites.<br />Geef de volledige URL op waar het backend bestand staat (bijv. http://e107.org/news.xml). Als de RSS bron die u ophaalt een url naar een link knop heeft die u wilt tonen, laat dan het plaatjes veld leeg, geef anders het pad naar een plaatje op, of voer in 'none' om geen plaatje te tonen. Kruis vervolgens aan wat u van de nieuwsbron wilt tonen. U kunt de nieuwsbron activeren en de-activeren, als die site uit de lucht is bijvoorbeeld.<br /><br />Om de kopteksten te zien, moet u ervoor zorgen dat het headlines_menu is geactiveerd in de menufunctie.";

$ns -> tablerender("Nieuwsbronnen hulp", $text);
?>